<?php

namespace App\Http\Controllers;

use App\Helper\CpfHelper;
use App\Models\Patrimonio\BensMoveis\ItemInventario;
use App\Models\Patrimonio\BensMoveis\Termo;
use Illuminate\Support\Carbon;

class TermoInventarioPrintController extends Controller
{
    public function print($id)
    {
        $termo = Termo::query()
            ->with(['arquivoDigital.validadoPor.infoUser', 'responsavelRef.infoUser'])
            ->findOrFail($id);

        $itens = ItemInventario::query()
            ->where('termo', $termo->id)
            ->with('complementoSetorRef')
            ->orderBy('num_patrimonio')
            ->get();

        $primeiroItem = $itens->first();

        $arquivoDigital = $termo->arquivoDigital;
        $responsavel = $arquivoDigital?->validadoPor ?? $termo->responsavelRef;
        $infoResponsavel = $responsavel?->infoUser;

        return view('patrimonio.termo_inventario_impresso', [
            'termo' => $termo,
            'itens' => $itens,
            'unidade' => $primeiroItem?->unidade_localizado ?: 'NÃO INFORMADO',
            'setor' => $primeiroItem?->setor_localizado ?: 'NÃO INFORMADO',
            'complemento' => $primeiroItem?->complementoSetorRef?->descricao ?: ($primeiroItem?->complemento_localizado ?: 'NÃO INFORMADO'),
            'responsavel' => $responsavel?->name ?: 'NÃO INFORMADO',
            'cargo' => $infoResponsavel?->cargo ?: 'SERVIDOR',
            'cpf' => CpfHelper::format($infoResponsavel?->cpf),
            'dataAssinatura' => Carbon::parse($arquivoDigital?->data_validacao ?? $termo->date_time ?? now())->format('d/m/Y H:i:s'),
        ]);
    }
}
