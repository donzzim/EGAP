<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Services\UsersConnectionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BensController extends Controller
{
    public function index(Request $request, UsersConnectionService $usersConnectionService): JsonResponse
    {
        $mobileUser = $usersConnectionService->findByUser($request->user());

        if ($mobileUser === null) {
            return response()->json([
                'message' => 'Usuário sem vínculo válido para consulta de bens.',
            ], 403);
        }

        $setor = $this->filledInteger($mobileUser->setor());
        $unidadeJudiciaria = $this->filledInteger($mobileUser->unidadeJudiciaria());

        if ($setor === null || $unidadeJudiciaria === null) {
            return response()->json([
                'message' => 'Usuário sem lotação válida para consulta de bens.',
                'scope' => [
                    'user_id' => $mobileUser->id(),
                    'id_egap' => $mobileUser->idEgap(),
                    'setor' => $mobileUser->setor(),
                    'unidade_judiciaria' => $mobileUser->unidadeJudiciaria(),
                ],
            ], 422);
        }

        $bens = $this->bensQuery()
            ->where('UnidadeJudiciaria', $unidadeJudiciaria)
            ->where('Setor', $setor)
            ->orderBy('NumPatrimonio')
            ->get()
            ->map(fn (BemMovel $bem): array => $this->bemToArray($bem));

        return response()->json([
            'scope' => [
                'user_id' => $mobileUser->id(),
                'id_egap' => $mobileUser->idEgap(),
                'setor' => $setor,
                'unidade_judiciaria' => $unidadeJudiciaria,
            ],
            'total' => $bens->count(),
            'bens' => $bens,
        ]);
    }

    public function show(string $numPatrimonio): JsonResponse
    {
        $numPatrimonio = trim($numPatrimonio);

        if ($numPatrimonio === '') {
            return response()->json([
                'message' => 'Informe o NumPatrimonio do bem.',
            ], 422);
        }

        $bem = $this->bensQuery()
            ->where('NumPatrimonio', $numPatrimonio)
            ->first();

        if ($bem === null) {
            return response()->json([
                'message' => 'Bem não encontrado.',
                'patrimonio' => $numPatrimonio,
            ], 404);
        }

        return response()->json([
            'bem' => $this->bemToArray($bem),
        ]);
    }

    private function bensQuery(): Builder
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
                'TipodoBem',
                'EstadodeConservacao',
                'Voltagem',
                'SituacaoBem',
                'UnidadeJudiciaria',
                'Setor',
                'ComplementoSetor',
                'AndarSetor',
                'ValorAquisicao',
                'Valor',
                'DatadeIncorporacao',
                'DataCadastro',
                'DataBaixa',
                'ProcessoBaixa',
                'numero_processo',
                'nota_empenho',
                'nota_liquidacao',
                'data_liquidacao',
                'Observacao',
            ])
            ->with([
                'descricaoResumidaBemRef:id,Descricao',
                'marcaRef:id,descricao',
                'modeloRef:id,descricao',
                'situacaoBemRef:id,descricao,situacao',
                'unidadeJudiciariaRef:id,Setor',
                'setorRef:id,Setor,CodigoPai',
                'complementoSetorRef:id,descricao',
            ]);
    }

    private function bemToArray(BemMovel $bem): array
    {
        return [
            'id' => $bem->id,
            'patrimonio' => $bem->NumPatrimonio,
            'patrimonio_anterior' => $bem->NumerodePatAnterior,
            'tombo_smarapd' => $bem->TomboSmarapd,
            'num_tombo_smarapd' => $bem->NumTomboSmarapd,
            'numero_serie' => $bem->NumerodeSerie,
            'descricao' => $bem->Descricao,
            'descricao_resumida' => $bem->descricaoResumidaBemRef?->Descricao,
            'marca' => $bem->marcaRef?->descricao,
            'modelo' => $bem->modeloRef?->descricao,
            'tipo_bem' => $bem->TipodoBem,
            'estado_conservacao' => $bem->EstadodeConservacao,
            'voltagem' => $bem->Voltagem,
            'situacao' => $bem->situacaoBemRef?->descricao_completa,
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
            'data_incorporacao' => $bem->getRawOriginal('DatadeIncorporacao'),
            'data_cadastro' => $bem->getRawOriginal('DataCadastro'),
            'data_baixa' => $bem->getRawOriginal('DataBaixa'),
            'processo_baixa' => $bem->ProcessoBaixa,
            'numero_processo' => $bem->numero_processo,
            'nota_empenho' => $bem->nota_empenho,
            'nota_liquidacao' => $bem->nota_liquidacao,
            'data_liquidacao' => $bem->getRawOriginal('data_liquidacao'),
            'observacao' => $bem->Observacao,
        ];
    }

    private function filledInteger(int|string|null $value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $integerValue = (int) $value;

        return $integerValue > 0 ? $integerValue : null;
    }
}
