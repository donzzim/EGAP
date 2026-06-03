<?php

namespace App\Http\Controllers;

use App\Models\Patrimonio\BensMoveis\Termo;
use Illuminate\Support\Facades\Auth;

class TermosPrintController extends Controller
{
    public function imprimir($id)
    {
        $termo = Termo::query()
            ->with([
                'arquivoDigital',
                'ultimaTransferencia.setorAtualRel',
                'ultimaTransferencia.complementoAtualRel',
                'ultimaTransferencia.usuarioRef.infoUser',
                'transferencias' => fn ($query) => $query
                    ->select(['id', 'Termo', 'NumPatrimonio'])
                    ->orderBy('id'),
                'transferencias.bem' => fn ($query) => $query
                    ->select([
                        'id',
                        'NumPatrimonio',
                        'Descricao',
                        'Marca',
                        'Modelo',
                        'EstadodeConservacao',
                        'ValorAquisicao',
                        'ValordaReavaliacao',
                        'DatadeIncorporacao',
                    ]),
                'transferencias.bem.marcaRef:id,descricao',
                'transferencias.bem.modeloRef:id,descricao',
            ])
            ->findOrFail($id);

        $arquivoDigital = $termo->arquivoDigital;
        $ultimaTransferencia = $termo->ultimaTransferencia;
        $setorAtual = $ultimaTransferencia?->setorAtualRel;
        $complementoAtual = $ultimaTransferencia?->complementoAtualRel;
        $usuarioEmitente = $ultimaTransferencia?->usuarioRef;
        $infoEmitente = $usuarioEmitente?->infoUser;

        $bens = $termo->transferencias
            ->map(function ($transferencia) {
                $bem = $transferencia->bem;

                if (! $bem) {
                    return null;
                }

                $bem->setAttribute('marca_desc', $bem->marcaRef?->descricao ?? $bem->marcaRef?->Descricao);
                $bem->setAttribute('modelo_desc', $bem->modeloRef?->descricao);
                $bem->setAttribute(
                    'ValorCalculado',
                    optional($bem->DatadeIncorporacao)->lt('2015-01-01')
                        ? $bem->ValordaReavaliacao
                        : $bem->ValorAquisicao
                );

                return $bem;
            })
            ->filter()
            ->values();

        $usuarioAutenticado = Auth::user();
        $cpfRaw = $infoEmitente?->cpf ?? $usuarioAutenticado?->cpf;

        $cpfEmitente = '';
        if ($cpfRaw) {
            $nbr_cpf = str_pad(preg_replace('/[^0-9]/', '', $cpfRaw), 11, '0', STR_PAD_LEFT);
            $cpfEmitente = substr($nbr_cpf, 0, 3) . '.' . substr($nbr_cpf, 3, 3) . '.' . substr($nbr_cpf, 6, 3) . '-' . substr($nbr_cpf, 9, 2);
        }

        return view('patrimonio.termo_impresso', [
            'termo' => $termo,
            'arquivoDigital' => $arquivoDigital,
            'bens' => $bens,
            'unidade' => $setorAtual?->UnidadeOrganizacional ?? 'TRIBUNAL DE JUSTIÇA DO ESPÍRITO SANTO',
            'setor' => $setorAtual?->Setor ?? 'NÃO INFORMADO',
            'complemento' => $complementoAtual?->descricao ?? 'NÃO INFORMADO',
            'usuarioEmitente' => $usuarioEmitente?->name ?? $usuarioAutenticado?->name ?? 'NÃO INFORMADO',
            'cargoEmitente' => $infoEmitente?->cargo ?? $usuarioAutenticado?->cargo ?? 'SERVIDOR',
            'cpfEmitente' => $cpfEmitente,
        ]);
    }
}
