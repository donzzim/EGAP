<?php

namespace App\Http\Controllers;

use App\Models\Patrimonio\BensMoveis\Termo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TermosPrintController extends Controller
{
    public function imprimir($id)
    {
        $termo = Termo::query()
            ->with([
                'arquivoDigital.validadoPor.infoUser',
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
        $usuarioDestinatario = (int) ($arquivoDigital?->situacao ?? 0) === 1
            ? $arquivoDigital?->validadoPor
            : $usuarioEmitente;
        $infoDestinatario = $usuarioDestinatario?->infoUser;

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
        $dataEmissao = Carbon::parse($termo->date_time ?? now())->format('d/m/Y');
        $dataAssinatura = Carbon::parse(
            $arquivoDigital?->data_validacao ?? $arquivoDigital?->date_time ?? $termo->date_time ?? now(),
        )->format('d/m/Y');

        return view('patrimonio.termo_impresso', [
            'termo' => $termo,
            'arquivoDigital' => $arquivoDigital,
            'bens' => $bens,
            'unidade' => $setorAtual?->UnidadeOrganizacional ?? 'TRIBUNAL DE JUSTIÇA DO ESPÍRITO SANTO',
            'setor' => $setorAtual?->Setor ?? 'NÃO INFORMADO',
            'complemento' => $complementoAtual?->descricao ?? 'NÃO INFORMADO',
            'usuarioEmitente' => $usuarioEmitente?->name ?? $usuarioAutenticado?->name ?? 'NÃO INFORMADO',
            'cargoEmitente' => $infoEmitente?->cargo ?? $usuarioAutenticado?->cargo ?? 'SERVIDOR',
            'cpfEmitente' => $this->formatCpf($infoEmitente?->cpf ?? $usuarioAutenticado?->cpf),
            'usuarioDestinatario' => $usuarioDestinatario?->name,
            'cargoDestinatario' => $infoDestinatario?->cargo,
            'cpfDestinatario' => $this->formatCpf($infoDestinatario?->cpf),
            'dataEmissao' => $dataEmissao,
            'dataAssinatura' => $dataAssinatura,
        ]);
    }

    private function formatCpf(?string $cpf): string
    {
        if (blank($cpf)) {
            return '';
        }

        $digits = str_pad(preg_replace('/\D/', '', $cpf), 11, '0', STR_PAD_LEFT);

        return substr($digits, 0, 3).'.'.substr($digits, 3, 3).'.'.substr($digits, 6, 3).'-'.substr($digits, 9, 2);
    }
}
