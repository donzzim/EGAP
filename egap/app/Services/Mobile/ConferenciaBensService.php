<?php

namespace App\Services\Mobile;

use App\Models\Patrimonio\BensMoveis\AtividadeInventario;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Patrimonio\BensMoveis\Inventario;
use App\Models\Patrimonio\BensMoveis\ItemInventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConferenciaBensService
{
    private const SITUACOES_ELEGIVEIS = [1, 7, 8];

    private const STATUS_LOCALIZADO = 'LOCALIZADO';
    private const STATUS_NAO_LOCALIZADO = 'NÃO LOCALIZADO';
    private const STATUS_DIVERGENTE = 'DIVERGENTE';
    private const STATUS_A_INVENTARIAR = 'A INVENTARIAR';

    public function atual(array $scope): array
    {
        $inventario = $this->inventarioAtual();
        $atividade = $this->atividadeDoSetor($inventario, $scope);
        $resumo = $this->resumo($inventario, $scope);

        return [
            'inventario' => $this->inventarioToArray($inventario),
            'atividade' => $this->atividadeToArray($atividade, $scope, $resumo),
            'resumo' => $resumo,
            'scope' => $scope,
        ];
    }

    public function bens(array $scope, ?string $status = null, int $perPage = 30): array
    {
        $inventario = $this->inventarioAtual();
        $atividade = $this->atividadeDoSetor($inventario, $scope);

        $paginator = $this->bensEsperadosQuery($scope)
            ->when($status !== null && $status !== 'todos', fn (Builder $query): Builder => $this->applyStatusFilter(
                $query,
                $inventario,
                $scope,
                $status,
            ))
            ->orderBy('Descricao')
            ->orderBy('Marca')
            ->orderBy('Modelo')
            ->orderBy('NumPatrimonio')
            ->paginate($perPage);

        $bensPage = collect($paginator->items());
        $items = $this->itensDosBens($inventario, $scope, $bensPage);
        $bens = $bensPage
            ->map(fn (BemMovel $bem): ?array => $this->bemToArray(
                $bem,
                $inventario,
                $this->itemDoBem($items, $bem),
            ))
            ->filter()
            ->values();

        $resumo = $this->resumo($inventario, $scope);

        return [
            'inventario' => $this->inventarioToArray($inventario),
            'atividade' => $this->atividadeToArray($atividade, $scope, $resumo),
            'total' => $paginator->total(),
            'bens' => $bens,
            'resumo' => $resumo,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ];
    }

    public function validarLeitura(array $scope, string $codigo): array
    {
        $inventario = $this->inventarioAtual();
        $bem = $this->buscarBemPorCodigo($codigo);

        if (! $bem) {
            $item = $this->itemSemCadastroExistente($inventario, $scope, $codigo);

            if ($item) {
                return $this->resultadoLeitura('ja_conferido', 'Bem sem cadastro já registrado como divergência neste inventário.', null, false);
            }

            return $this->resultadoLeitura('nao_cadastrado', 'Bem não encontrado no cadastro.', null, false);
        }

        $item = $this->itemExistente($inventario, $scope, $bem);

        if ($item) {
            return $this->resultadoLeitura('ja_conferido', 'Bem já conferido neste inventário.', $bem, false, $inventario, $item);
        }

        if ((int) $bem->Setor !== (int) $scope['setor']) {
            return $this->resultadoLeitura('outro_setor', 'Bem pertence a outro setor.', $bem, false, $inventario);
        }

        if (! in_array((int) $bem->SituacaoBem, self::SITUACOES_ELEGIVEIS, true)) {
            return $this->resultadoLeitura('situacao_nao_conferivel', 'Bem em situação não conferível.', $bem, false, $inventario);
        }

        if ($this->isEmTransferencia($bem)) {
            return $this->resultadoLeitura('em_transferencia', 'Bem em transferência.', $bem, false, $inventario);
        }

        if ((int) $bem->SituacaoBem === 8) {
            return $this->resultadoLeitura('cadastrado_manualmente', 'Bem cadastrado manualmente. Verifique o termo.', $bem, true, $inventario);
        }

        return $this->resultadoLeitura('localizavel', 'Bem localizado no setor.', $bem, true, $inventario);
    }

    public function localizar(array $scope, array $data): array
    {
        $inventario = $this->inventarioAtual();
        $this->atividadeEditavel($inventario, $scope);
        $bem = $this->resolverBem($data);

        $this->validarBemConferivelNoSetor($scope, $bem);

        $itemExistente = $this->itemExistente($inventario, $scope, $bem);

        if ($itemExistente) {
            return [
                'status' => 'ja_conferido',
                'message' => 'Bem já conferido neste inventário.',
                'bem' => $this->bemToArray($bem, $inventario, $itemExistente),
                'resumo' => $this->resumo($inventario, $scope),
            ];
        }

        $item = DB::connection('egap')->transaction(function () use ($inventario, $scope, $bem): ItemInventario {
            $bemTravado = BemMovel::query()
                ->whereKey($bem->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $itemExistente = $this->itemExistente($inventario, $scope, $bemTravado);

            if ($itemExistente) {
                return $itemExistente;
            }

            $item = ItemInventario::query()->create($this->dadosItemInventario(
                $inventario,
                $scope,
                $bemTravado,
                self::STATUS_LOCALIZADO,
            ));

            BemMovel::query()
                ->whereKey($bemTravado->getKey())
                ->update([
                    'sit_inventario' => self::STATUS_LOCALIZADO,
                    'id_inventario' => $inventario->id,
                    'Usuario' => $scope['id_egap'],
                    'date_time' => now(),
                ]);

            $this->atualizarQuantidadeInventariada($inventario, $scope);

            return $item;
        });

        return [
            'status' => 'localizado',
            'message' => 'Bem localizado no setor.',
            'bem' => $this->bemToArray($bem->fresh(), $inventario, $item),
            'resumo' => $this->resumo($inventario, $scope),
        ];
    }

    public function naoLocalizados(array $scope, array $bemIds, string $justificativa): array
    {
        $justificativa = trim($justificativa);

        if ($justificativa === '') {
            throw new HttpException(422, 'Justificativa obrigatória.');
        }

        $inventario = $this->inventarioAtual();
        $this->atividadeEditavel($inventario, $scope);

        $bens = $this->bensEsperadosQuery($scope)
            ->whereIn('id', $bemIds)
            ->get();

        if ($bens->isEmpty()) {
            throw new NotFoundHttpException('Nenhum bem pendente encontrado para registrar.');
        }

        DB::connection('egap')->transaction(function () use ($inventario, $scope, $bens, $justificativa): void {
            foreach ($bens as $bem) {
                $item = $this->itemExistente($inventario, $scope, $bem);
                $dados = $this->dadosItemInventario($inventario, $scope, $bem, self::STATUS_NAO_LOCALIZADO, $justificativa);

                if ($item) {
                    $item->fill([
                        'situacao' => self::STATUS_NAO_LOCALIZADO,
                        'observacao' => $justificativa,
                        'atualizado_por' => $scope['id_egap'],
                        'date_time' => now(),
                    ])->save();
                } else {
                    ItemInventario::query()->create($dados);
                }

                BemMovel::query()
                    ->whereKey($bem->getKey())
                    ->update([
                        'sit_inventario' => self::STATUS_NAO_LOCALIZADO,
                        'id_inventario' => $inventario->id,
                        'Usuario' => $scope['id_egap'],
                        'date_time' => now(),
                    ]);
            }

            $this->atualizarQuantidadeInventariada($inventario, $scope);
        });

        return [
            'message' => 'Bem não localizado registrado.',
            'bens' => $bens
                ->map(fn (BemMovel $bem): array => $this->bemToArray(
                    $bem->fresh(),
                    $inventario,
                    $this->itemExistente($inventario, $scope, $bem),
                ))
                ->values(),
            'resumo' => $this->resumo($inventario, $scope),
        ];
    }

    public function registrarDivergencia(array $scope, array $data): array
    {
        $observacao = trim((string) ($data['observacao'] ?? ''));

        if ($observacao === '') {
            throw new HttpException(422, 'Observação obrigatória.');
        }

        $inventario = $this->inventarioAtual();
        $this->atividadeEditavel($inventario, $scope);

        $campos = collect($data['campos'] ?? [])
            ->filter(fn ($value): bool => filled($value))
            ->values()
            ->implode(', ');

        $observacaoCompleta = $campos !== ''
            ? "Campos divergentes: {$campos}. {$observacao}"
            : $observacao;

        try {
            $bem = $this->resolverBem($data);
        } catch (NotFoundHttpException $exception) {
            if (empty($data['codigo'])) {
                throw $exception;
            }

            return $this->registrarDivergenciaSemCadastro(
                $inventario,
                $scope,
                (string) $data['codigo'],
                $observacaoCompleta,
            );
        }

        $atualizarBem = (int) $bem->Setor === (int) $scope['setor']
            && (int) $bem->UnidadeJudiciaria === (int) $scope['unidade_judiciaria'];

        $item = DB::connection('egap')->transaction(function () use ($inventario, $scope, $bem, $observacaoCompleta, $atualizarBem): ItemInventario {
            $item = $this->itemExistente($inventario, $scope, $bem);

            if ($item) {
                $item->fill([
                    'situacao' => self::STATUS_DIVERGENTE,
                    'observacao' => $observacaoCompleta,
                    'atualizado_por' => $scope['id_egap'],
                    'date_time' => now(),
                ])->save();
            } else {
                $item = ItemInventario::query()->create($this->dadosItemInventario(
                    $inventario,
                    $scope,
                    $bem,
                    self::STATUS_DIVERGENTE,
                    $observacaoCompleta,
                ));
            }

            if ($atualizarBem) {
                BemMovel::query()
                    ->whereKey($bem->getKey())
                    ->update([
                        'sit_inventario' => self::STATUS_DIVERGENTE,
                        'id_inventario' => $inventario->id,
                        'Usuario' => $scope['id_egap'],
                        'date_time' => now(),
                    ]);
            }

            $this->atualizarQuantidadeInventariada($inventario, $scope);

            return $item;
        });

        return [
            'status' => 'divergente',
            'message' => 'Divergência registrada.',
            'bem' => $this->bemToArray($bem->fresh(), $inventario, $item),
            'resumo' => $this->resumo($inventario, $scope),
        ];
    }

    private function registrarDivergenciaSemCadastro(
        Inventario $inventario,
        array $scope,
        string $codigo,
        string $observacao,
    ): array {
        $codigo = $this->normalizarCodigo($codigo);

        DB::connection('egap')->transaction(function () use ($inventario, $scope, $codigo, $observacao): void {
            $item = $this->itemSemCadastroExistente($inventario, $scope, $codigo);

            if ($item) {
                $item->fill([
                    'situacao' => self::STATUS_DIVERGENTE,
                    'observacao' => $observacao,
                    'atualizado_por' => $scope['id_egap'],
                    'date_time' => now(),
                ])->save();
            } else {
                ItemInventario::query()->create([
                    'date_time' => now(),
                    'id_bem' => null,
                    'unidades' => $scope['unidade_judiciaria'],
                    'num_patrimonio' => $codigo,
                    'descricao_detalhada' => 'Bem encontrado fisicamente sem cadastro patrimonial digital.',
                    'setor' => $scope['setor'],
                    'id_inventario' => $inventario->id,
                    'observacao' => $observacao,
                    'situacao' => self::STATUS_DIVERGENTE,
                    'atualizado_por' => $scope['id_egap'],
                ]);
            }

            $this->atualizarQuantidadeInventariada($inventario, $scope);
        });

        return [
            'status' => 'divergente',
            'message' => 'Divergência de bem sem cadastro registrada.',
            'bem' => null,
            'resumo' => $this->resumo($inventario, $scope),
        ];
    }

    public function finalizar(array $scope): array
    {
        $inventario = $this->inventarioAtual();
        $atividade = $this->atividadeEditavel($inventario, $scope);
        $resumo = $this->resumo($inventario, $scope);

        if (($resumo['pendentes'] ?? 0) > 0) {
            throw new HttpException(409, 'Ainda existem bens pendentes no setor.');
        }

        $atividade->fill([
            'situacao' => 'Finalizado',
            'termino' => now(),
            'qtde_inventariada' => $this->quantidadeInventariada($inventario, $scope),
        ])->save();

        return [
            'message' => 'Conferência do setor finalizada.',
            'inventario' => $this->inventarioToArray($inventario),
            'atividade' => $this->atividadeToArray($atividade->fresh(), $scope, $this->resumo($inventario, $scope)),
        ];
    }

    private function inventarioAtual(): Inventario
    {
        $inventario = Inventario::query()
            ->orderByRaw("
                CASE
                    WHEN situacao IN ('Em andamento', 'A inventariar', '0') THEN 0
                    WHEN situacao = 'Finalizado' THEN 2
                    ELSE 1
                END
            ")
            ->orderByDesc('id')
            ->first();

        if (! $inventario) {
            throw new NotFoundHttpException('Inventário atual não encontrado.');
        }

        return $inventario;
    }

    private function atividadeDoSetor(Inventario $inventario, array $scope): ?AtividadeInventario
    {
        return AtividadeInventario::query()
            ->where('id_inventario', $inventario->id)
            ->where('id_unidade', $scope['unidade_judiciaria'])
            ->where('setor', $scope['setor'])
            ->orderByDesc('id')
            ->first();
    }

    private function atividadeEditavel(Inventario $inventario, array $scope): AtividadeInventario
    {
        $atividade = $this->atividadeDoSetor($inventario, $scope);

        if ($atividade && ! $this->atividadePodeEditar($atividade)) {
            throw new HttpException(403, 'Inventário do setor finalizado.');
        }

        if ($atividade) {
            return $atividade;
        }

        return AtividadeInventario::query()->create([
            'date_time' => now(),
            'id_inventario' => $inventario->id,
            'id_unidade' => $scope['unidade_judiciaria'],
            'setor' => $scope['setor'],
            'inicio' => now(),
            'situacao' => 'Em andamento',
            'qtde_inventariada' => 0,
        ]);
    }

    private function atividadePodeEditar(?AtividadeInventario $atividade): bool
    {
        if (! $atividade) {
            return true;
        }

        $situacao = Str::lower(Str::ascii((string) $atividade->situacao));

        return ! in_array($situacao, ['finalizado', 'carga efetuada'], true);
    }

    private function bensEsperadosQuery(array $scope): Builder
    {
        return BemMovel::query()
            ->select([
                'id',
                'NumPatrimonio',
                'TomboSmarapd',
                'NumTomboSmarapd',
                'NumerodePatAnterior',
                'NumerodeSerie',
                'Descricao',
                'DescricaoResumidadoBem',
                'Marca',
                'Modelo',
                'EstadodeConservacao',
                'SituacaoBem',
                'UnidadeJudiciaria',
                'Setor',
                'ComplementoSetor',
                'AndarSetor',
                'ValorAquisicao',
                'Valor',
                'Observacao',
                'id_descricaodetalhada',
                'sit_inventario',
                'id_inventario',
            ])
            ->with([
                'descricaoResumidaBemRef:id,Descricao',
                'descricaoDetalhadaRef:id,descricao_detalhada',
                'marcaRef:id,descricao',
                'modeloRef:id,descricao',
                'situacaoBemRef:id,descricao,situacao',
                'unidadeJudiciariaRef:id,Setor',
                'setorRef:id,Setor,CodigoPai',
                'complementoSetorRef:id,descricao',
            ])
            ->where('UnidadeJudiciaria', $scope['unidade_judiciaria'])
            ->where('Setor', $scope['setor'])
            ->whereIn('SituacaoBem', self::SITUACOES_ELEGIVEIS);
    }

    private function applyStatusFilter(Builder $query, Inventario $inventario, array $scope, string $status): Builder
    {
        return match ($status) {
            'localizado' => $this->whereItemStatus($query, $inventario, $scope, self::STATUS_LOCALIZADO),
            'nao_localizado' => $this->whereItemStatus($query, $inventario, $scope, self::STATUS_NAO_LOCALIZADO),
            'divergente' => $this->whereItemStatus($query, $inventario, $scope, self::STATUS_DIVERGENTE),
            'em_transferencia' => $query->where(function (Builder $query): void {
                $query
                    ->where('SituacaoBem', 7)
                    ->orWhere('sit_inventario', 'like', 'EM TRANSF%');
            }),
            'cadastrado_manualmente' => $query->where('SituacaoBem', 8),
            'pendente' => $this->wherePendente($query, $inventario, $scope),
            default => $query,
        };
    }

    private function whereItemStatus(Builder $query, Inventario $inventario, array $scope, string $status): Builder
    {
        return $query->whereExists(function ($query) use ($inventario, $scope, $status): void {
            $query
                ->selectRaw('1')
                ->from('mat_itensinventario')
                ->where('mat_itensinventario.id_inventario', $inventario->id)
                ->where('mat_itensinventario.setor', $scope['setor'])
                ->where('mat_itensinventario.situacao', $status)
                ->where(function ($query): void {
                    $query
                        ->whereColumn('mat_itensinventario.id_bem', 'mat_patrimonio.id')
                        ->orWhereColumn('mat_itensinventario.num_patrimonio', 'mat_patrimonio.NumPatrimonio');
                });
        });
    }

    private function wherePendente(Builder $query, Inventario $inventario, array $scope): Builder
    {
        return $query
            ->where('SituacaoBem', '<>', 7)
            ->where(function (Builder $query): void {
                $query
                    ->where('SituacaoBem', '<>', 8)
                    ->orWhereExists(function ($query): void {
                        $query
                            ->selectRaw('1')
                            ->from('mat_transferencia')
                            ->join('mat_arquivodigital', 'mat_transferencia.Termo', '=', 'mat_arquivodigital.termo')
                            ->whereColumn('mat_transferencia.NumPatrimonio', 'mat_patrimonio.id')
                            ->where('mat_arquivodigital.situacao', 1);
                    });
            })
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('sit_inventario')
                    ->orWhere('sit_inventario', 'not like', 'EM TRANSF%');
            })
            ->whereNotExists(function ($query) use ($inventario, $scope): void {
                $query
                    ->selectRaw('1')
                    ->from('mat_itensinventario')
                    ->where('mat_itensinventario.id_inventario', $inventario->id)
                    ->where('mat_itensinventario.setor', $scope['setor'])
                    ->where(function ($query): void {
                        $query
                            ->whereColumn('mat_itensinventario.id_bem', 'mat_patrimonio.id')
                            ->orWhereColumn('mat_itensinventario.num_patrimonio', 'mat_patrimonio.NumPatrimonio');
                    });
            });
    }

    private function itensDosBens(Inventario $inventario, array $scope, Collection $bens): Collection
    {
        $ids = $bens->pluck('id')->filter()->values();
        $patrimonios = $bens->pluck('NumPatrimonio')->filter()->values();

        if ($ids->isEmpty() && $patrimonios->isEmpty()) {
            return collect();
        }

        return ItemInventario::query()
            ->where('id_inventario', $inventario->id)
            ->where('setor', $scope['setor'])
            ->where(function (Builder $query) use ($ids, $patrimonios): void {
                $query
                    ->when($ids->isNotEmpty(), fn (Builder $query): Builder => $query->whereIn('id_bem', $ids))
                    ->when($patrimonios->isNotEmpty(), fn (Builder $query): Builder => $query->orWhereIn('num_patrimonio', $patrimonios));
            })
            ->get();
    }

    private function itemDoBem(Collection $items, BemMovel $bem): ?ItemInventario
    {
        return $items->first(function (ItemInventario $item) use ($bem): bool {
            return ((string) $item->id_bem !== '' && (int) $item->id_bem === (int) $bem->id)
                || ((string) $item->num_patrimonio !== '' && (string) $item->num_patrimonio === (string) $bem->NumPatrimonio);
        });
    }

    private function itemExistente(Inventario $inventario, array $scope, BemMovel $bem): ?ItemInventario
    {
        return ItemInventario::query()
            ->where('id_inventario', $inventario->id)
            ->where('setor', $scope['setor'])
            ->where(function (Builder $query) use ($bem): void {
                $query
                    ->where('id_bem', $bem->id)
                    ->orWhere('num_patrimonio', $bem->NumPatrimonio);
            })
            ->first();
    }

    private function itemSemCadastroExistente(Inventario $inventario, array $scope, string $codigo): ?ItemInventario
    {
        $codigo = $this->normalizarCodigo($codigo);
        $semZeros = ltrim($codigo, '0') ?: '0';

        return ItemInventario::query()
            ->where('id_inventario', $inventario->id)
            ->where('setor', $scope['setor'])
            ->whereNull('id_bem')
            ->where(function (Builder $query) use ($codigo, $semZeros): void {
                $query
                    ->where('num_patrimonio', $codigo)
                    ->orWhere('num_patrimonio', $semZeros)
                    ->orWhereRaw("TRIM(LEADING '0' FROM num_patrimonio) = ?", [$semZeros]);
            })
            ->first();
    }

    private function buscarBemPorCodigo(string $codigo): ?BemMovel
    {
        $codigo = $this->normalizarCodigo($codigo);

        $semZeros = ltrim($codigo, '0') ?: '0';

        return BemMovel::query()
            ->with([
                'descricaoResumidaBemRef:id,Descricao',
                'descricaoDetalhadaRef:id,descricao_detalhada',
                'marcaRef:id,descricao',
                'modeloRef:id,descricao',
                'situacaoBemRef:id,descricao,situacao',
                'unidadeJudiciariaRef:id,Setor',
                'setorRef:id,Setor,CodigoPai',
                'complementoSetorRef:id,descricao',
            ])
            ->where(function (Builder $query) use ($codigo, $semZeros): void {
                $query
                    ->where('NumPatrimonio', $codigo)
                    ->orWhere('NumPatrimonio', $semZeros)
                    ->orWhereRaw("TRIM(LEADING '0' FROM NumPatrimonio) = ?", [$semZeros])
                    ->orWhere('TomboSmarapd', $codigo)
                    ->orWhere('NumTomboSmarapd', $codigo)
                    ->orWhere('NumerodePatAnterior', $codigo);
            })
            ->first();
    }

    private function normalizarCodigo(string $codigo): string
    {
        $codigo = preg_replace('/\s+/', '', trim($codigo)) ?? '';

        if ($codigo === '') {
            throw new HttpException(422, 'Informe o código patrimonial.');
        }

        return $codigo;
    }

    private function resolverBem(array $data): BemMovel
    {
        if (! empty($data['bem_id'])) {
            $bem = BemMovel::query()
                ->with([
                    'descricaoResumidaBemRef:id,Descricao',
                    'descricaoDetalhadaRef:id,descricao_detalhada',
                    'marcaRef:id,descricao',
                    'modeloRef:id,descricao',
                    'situacaoBemRef:id,descricao,situacao',
                    'unidadeJudiciariaRef:id,Setor',
                    'setorRef:id,Setor,CodigoPai',
                    'complementoSetorRef:id,descricao',
                ])
                ->find((int) $data['bem_id']);

            if ($bem) {
                return $bem;
            }
        }

        if (! empty($data['codigo'])) {
            $bem = $this->buscarBemPorCodigo((string) $data['codigo']);

            if ($bem) {
                return $bem;
            }
        }

        throw new NotFoundHttpException('Bem não encontrado.');
    }

    private function validarBemConferivelNoSetor(array $scope, BemMovel $bem): void
    {
        if ((int) $bem->Setor !== (int) $scope['setor']) {
            throw new HttpException(409, 'Bem pertence a outro setor.');
        }

        if ((int) $bem->UnidadeJudiciaria !== (int) $scope['unidade_judiciaria']) {
            throw new HttpException(409, 'Bem pertence a outra unidade.');
        }

        if (! in_array((int) $bem->SituacaoBem, self::SITUACOES_ELEGIVEIS, true)) {
            throw new HttpException(409, 'Bem em situação não conferível.');
        }

        if ($this->isEmTransferencia($bem)) {
            throw new HttpException(409, 'Bem em transferência.');
        }
    }

    private function dadosItemInventario(
        Inventario $inventario,
        array $scope,
        BemMovel $bem,
        string $situacao,
        ?string $observacao = null,
    ): array {
        return [
            'date_time' => now(),
            'id_bem' => $bem->id,
            'unidades' => $bem->UnidadeJudiciaria,
            'num_patrimonio' => $bem->NumPatrimonio,
            'num_patrimonioantigo' => $bem->NumerodePatAnterior,
            'num_serie' => $bem->NumerodeSerie,
            'descricao_resumida' => $bem->descricaoResumidaBemRef?->Descricao,
            'descricao_detalhada' => $bem->descricaoDetalhadaRef?->descricao_detalhada ?? $bem->Descricao,
            'marca' => $bem->marcaRef?->descricao,
            'modelo' => $bem->modeloRef?->descricao,
            'setor' => $scope['setor'],
            'id_inventario' => $inventario->id,
            'estado_conservacao' => $bem->EstadodeConservacao,
            'setor_localizado' => $bem->setorRef?->Setor,
            'unidade_localizado' => $bem->unidadeJudiciariaRef?->Setor,
            'complemento_localizado' => $bem->complementoSetorRef?->descricao,
            'observacao' => $observacao,
            'situacao' => $situacao,
            'atualizado_por' => $scope['id_egap'],
            'num_serie_egap' => $bem->NumerodeSerie,
            'descricao_detalhada_egap' => $bem->Descricao,
            'marca_egap' => $bem->marcaRef?->descricao,
            'modelo_egap' => $bem->modeloRef?->descricao,
            'id_complementosetor' => $bem->ComplementoSetor,
        ];
    }

    private function resumo(Inventario $inventario, array $scope): array
    {
        $total = (clone $this->bensEsperadosQuery($scope))->count();
        $localizados = $this->countItens($inventario, $scope, self::STATUS_LOCALIZADO);
        $naoLocalizados = $this->countItens($inventario, $scope, self::STATUS_NAO_LOCALIZADO);
        $divergentes = $this->countItens($inventario, $scope, self::STATUS_DIVERGENTE);

        $pendentes = (clone $this->bensEsperadosQuery($scope))
            ->where('SituacaoBem', '<>', 7)
            ->where(function (Builder $query): void {
                $query
                    ->where('SituacaoBem', '<>', 8)
                    ->orWhereExists(function ($query): void {
                        $query
                            ->selectRaw('1')
                            ->from('mat_transferencia')
                            ->join('mat_arquivodigital', 'mat_transferencia.Termo', '=', 'mat_arquivodigital.termo')
                            ->whereColumn('mat_transferencia.NumPatrimonio', 'mat_patrimonio.id')
                            ->where('mat_arquivodigital.situacao', 1);
                    });
            })
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('sit_inventario')
                    ->orWhere('sit_inventario', 'not like', 'EM TRANSF%');
            })
            ->whereNotExists(function ($query) use ($inventario, $scope): void {
                $query
                    ->selectRaw('1')
                    ->from('mat_itensinventario')
                    ->where('mat_itensinventario.id_inventario', $inventario->id)
                    ->where('mat_itensinventario.setor', $scope['setor'])
                    ->where(function ($query): void {
                        $query
                            ->whereColumn('mat_itensinventario.id_bem', 'mat_patrimonio.id')
                            ->orWhereColumn('mat_itensinventario.num_patrimonio', 'mat_patrimonio.NumPatrimonio');
                    });
            })
            ->count();

        $emTransferencia = (clone $this->bensEsperadosQuery($scope))
            ->where(function (Builder $query): void {
                $query
                    ->where('SituacaoBem', 7)
                    ->orWhere('sit_inventario', 'like', 'EM TRANSF%');
            })
            ->count();

        $cadastradosManualmente = (clone $this->bensEsperadosQuery($scope))
            ->where('SituacaoBem', 8)
            ->count();

        return [
            'total' => $total,
            'localizados' => $localizados,
            'pendentes' => $pendentes,
            'nao_localizados' => $naoLocalizados,
            'divergentes' => $divergentes,
            'outro_setor' => 0,
            'em_transferencia' => $emTransferencia,
            'cadastrados_manualmente' => $cadastradosManualmente,
            'pode_finalizar' => $total > 0 && $pendentes === 0,
        ];
    }

    private function countItens(Inventario $inventario, array $scope, string $situacao): int
    {
        return ItemInventario::query()
            ->where('id_inventario', $inventario->id)
            ->where('setor', $scope['setor'])
            ->where('situacao', $situacao)
            ->count();
    }

    private function atualizarQuantidadeInventariada(Inventario $inventario, array $scope): void
    {
        AtividadeInventario::query()
            ->where('id_inventario', $inventario->id)
            ->where('id_unidade', $scope['unidade_judiciaria'])
            ->where('setor', $scope['setor'])
            ->update([
                'qtde_inventariada' => $this->quantidadeInventariada($inventario, $scope),
            ]);
    }

    private function quantidadeInventariada(Inventario $inventario, array $scope): int
    {
        return ItemInventario::query()
            ->where('id_inventario', $inventario->id)
            ->where('setor', $scope['setor'])
            ->whereIn('situacao', [
                self::STATUS_LOCALIZADO,
                self::STATUS_NAO_LOCALIZADO,
                self::STATUS_DIVERGENTE,
            ])
            ->count();
    }

    private function resultadoLeitura(
        string $status,
        string $message,
        ?BemMovel $bem,
        bool $podeLocalizar,
        ?Inventario $inventario = null,
        ?ItemInventario $item = null,
    ): array {
        return [
            'status' => $status,
            'message' => $message,
            'pode_localizar' => $podeLocalizar,
            'bem' => $bem ? $this->bemToArray($bem, $inventario, $item) : null,
        ];
    }

    private function bemToArray(?BemMovel $bem, ?Inventario $inventario = null, ?ItemInventario $item = null): ?array
    {
        if (! $bem) {
            return null;
        }

        $status = $this->statusConferencia($bem, $item);

        return [
            'id' => $bem->id,
            'patrimonio' => $bem->NumPatrimonio,
            'patrimonio_anterior' => $bem->NumerodePatAnterior,
            'tombo_smarapd' => $bem->TomboSmarapd,
            'num_tombo_smarapd' => $bem->NumTomboSmarapd,
            'numero_serie' => $bem->NumerodeSerie,
            'descricao' => $bem->Descricao,
            'descricao_resumida' => $bem->descricaoResumidaBemRef?->Descricao,
            'descricao_detalhada' => $bem->descricaoDetalhadaRef?->descricao_detalhada,
            'marca' => $bem->marcaRef?->descricao,
            'modelo' => $bem->modeloRef?->descricao,
            'estado_conservacao' => $bem->EstadodeConservacao,
            'situacao_bem_id' => $bem->SituacaoBem,
            'situacao' => $bem->situacaoBemRef?->descricao_completa,
            'sit_inventario' => $bem->sit_inventario,
            'id_inventario' => $bem->id_inventario,
            'unidade_judiciaria' => [
                'id' => $bem->UnidadeJudiciaria,
                'nome' => $bem->unidadeJudiciariaRef?->Setor,
            ],
            'setor' => [
                'id' => $bem->Setor,
                'nome' => $bem->setorRef?->Setor,
            ],
            'complemento_setor' => [
                'id' => $bem->ComplementoSetor,
                'nome' => $bem->complementoSetorRef?->descricao,
            ],
            'andar_setor' => $bem->AndarSetor,
            'valor_aquisicao' => $bem->ValorAquisicao,
            'valor' => $bem->Valor,
            'observacao' => $bem->Observacao,
            'conferencia' => [
                'status' => $status,
                'status_label' => $this->statusLabel($status),
                'id_inventario' => $inventario?->id,
                'ja_registrado' => $item !== null,
                'item_id' => $item?->id,
                'situacao_item' => $item?->situacao,
                'observacao_item' => $item?->observacao,
            ],
        ];
    }

    private function statusConferencia(BemMovel $bem, ?ItemInventario $item): string
    {
        if ($item) {
            return match ($item->situacao) {
                self::STATUS_LOCALIZADO => 'localizado',
                self::STATUS_NAO_LOCALIZADO => 'nao_localizado',
                self::STATUS_DIVERGENTE => 'divergente',
                default => 'registrado',
            };
        }

        if ($this->isEmTransferencia($bem)) {
            return 'em_transferencia';
        }

        if ((int) $bem->SituacaoBem === 8) {
            return 'cadastrado_manualmente';
        }

        return 'pendente';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'localizado' => 'Localizado',
            'nao_localizado' => 'Não localizado',
            'divergente' => 'Divergente',
            'em_transferencia' => 'Em transferência',
            'cadastrado_manualmente' => 'Cadastrado manualmente',
            'registrado' => 'Registrado',
            default => 'Pendente',
        };
    }

    private function isEmTransferencia(BemMovel $bem): bool
    {
        return (int) $bem->SituacaoBem === 7
            || Str::startsWith(Str::upper(Str::ascii((string) $bem->sit_inventario)), 'EM TRANSF');
    }

    private function inventarioToArray(Inventario $inventario): array
    {
        return [
            'id' => $inventario->id,
            'numero' => $inventario->num_inventario,
            'ano' => $inventario->ano_inventario,
            'situacao' => $this->situacaoInventarioLabel($inventario->situacao),
            'situacao_raw' => $inventario->situacao,
            'inicio' => $this->dateValue($inventario->inicio_inventario),
            'termino' => $this->dateValue($inventario->termino_inventario),
        ];
    }

    private function atividadeToArray(?AtividadeInventario $atividade, array $scope, array $resumo): array
    {
        $podeEditar = $this->atividadePodeEditar($atividade);

        return [
            'id' => $atividade?->id,
            'unidade_judiciaria' => $atividade?->id_unidade ?? $scope['unidade_judiciaria'],
            'setor' => $atividade?->setor ?? $scope['setor'],
            'situacao' => $atividade?->situacao ?: 'Aberto',
            'pode_editar' => $podeEditar,
            'pode_finalizar' => $podeEditar && (bool) ($resumo['pode_finalizar'] ?? false),
            'qtde_inventariada' => $atividade?->qtde_inventariada,
            'inicio' => $this->dateValue($atividade?->inicio),
            'termino' => $this->dateValue($atividade?->termino),
        ];
    }

    private function situacaoInventarioLabel(null|int|string $situacao): string
    {
        return match ((string) $situacao) {
            '0' => 'Em andamento',
            '1' => 'A inventariar',
            '2' => 'Finalizado',
            default => (string) ($situacao ?: 'Em andamento'),
        };
    }

    private function dateValue(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->toDateTimeString();
    }
}
