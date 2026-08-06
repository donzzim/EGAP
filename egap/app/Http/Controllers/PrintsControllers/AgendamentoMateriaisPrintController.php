<?php

namespace App\Http\Controllers\PrintsControllers;

use App\Http\Controllers\Controller;
use App\Models\Agendamento\Solicitacao;
use Illuminate\Support\Carbon;

class AgendamentoMateriaisPrintController extends Controller
{
    public function print(int $id)
    {
        $solicitacao = Solicitacao::query()
            ->with([
                'idSolicitanteRef',
                'unidadeSolicitanteRef',
                'setorSolicitanteRef',
                'materiaisRef.idPedidoRef',
                'materiaisRef.idTermoRef.transferencias' => fn ($query) => $query
                    ->select(['id', 'Termo', 'NumPatrimonio', 'AndarAtual'])
                    ->orderBy('id'),
                'materiaisRef.idTermoRef.transferencias.bem' => fn ($query) => $query
                    ->select([
                        'id',
                        'NumPatrimonio',
                        'Descricao',
                        'Marca',
                        'Modelo',
                        'NumerodeSerie',
                        'EstadodeConservacao',
                    ]),
                'materiaisRef.idTermoRef.transferencias.bem.marcaRef:id,descricao',
                'materiaisRef.idTermoRef.transferencias.bem.modeloRef:id,descricao',
            ])
            ->findOrFail($id);

        $itens = collect();

        foreach ($solicitacao->materiaisRef as $material) {
            $termo = $material->idTermoRef;

            foreach ($termo?->transferencias ?? [] as $transferencia) {
                $bem = $transferencia->bem;

                if (! $bem) {
                    continue;
                }

                $bem->setAttribute('marca_desc', $bem->marcaRef?->descricao);
                $bem->setAttribute('modelo_desc', $bem->modeloRef?->descricao);
                $bem->setAttribute('andar_atual', $transferencia->AndarAtual);
                $bem->setAttribute('termo_completo', "{$termo->num_termo}/{$termo->ano_termo}");

                $itens->push($bem);
            }
        }

        $pedido = $solicitacao->materiaisRef->first()?->idPedidoRef;

        return view('agendamento.materiais_impressao', [
            'solicitacao' => $solicitacao,
            'itens' => $itens,
            'unidade' => $solicitacao->unidadeSolicitanteRef?->UnidadeOrganizacional ?? 'NÃO INFORMADO',
            'setor' => $solicitacao->setorSolicitanteRef?->Setor ?? 'NÃO INFORMADO',
            'pedidoProtocolo' => $pedido?->num_protocolo,
            'dataEmissao' => Carbon::parse($solicitacao->date_time ?? now())->format('d/m/Y'),
        ]);
    }
}
