@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Conciliação - Bens Patrimoniais')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 11px; margin-top: 10px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 6px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; }
        
        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 14px; padding: 8px; text-transform: uppercase; font-family: Verdana, sans-serif; }
        .caixa-info { border: 1px solid #000 !important; padding: 4px 6px; font-family: Verdana, sans-serif; font-size: 11px; }
    </style>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <tr><td class="caixa-titulo">RELATÓRIO DE CONCILIAÇÃO</td></tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <td width="8%" class="caixa-info" style="font-weight: bold;">PERÍODO</td>
            <td width="40%" class="caixa-info">
                {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
            </td>
            <td width="2%" style="border: none !important;"></td>
            <td width="50%" class="caixa-info">
                @if(($filtros['situacao_contabil'] ?? 'Todos') !== 'Todos')
                    <span style="font-weight: bold;">SITUAÇÃO DO INVENTÁRIO:</span> {{ mb_strtoupper($filtros['situacao_contabil']) }}
                @else
                    &nbsp;
                @endif
            </td>
        </tr>
    </table>

    <table class="tabela-grid">
        <tr class="linha-cabecalho">
            <th colspan="3" style="border-top: none !important; border-left: none !important;">&nbsp;</th>
            <th colspan="3">RESUMO</th>
        </tr>
        <tr class="linha-cabecalho">
            <th colspan="3" style="border-top: none !important; border-left: none !important;">&nbsp;</th>
            <th colspan="3">VALORES DA CONCILIAÇÃO</th>
        </tr>
        <tr class="linha-cabecalho">
            <th width="15%">CONTA CONTÁBIL</th>
            <th width="15%">CÓD. NAT. DESPESA</th>
            <th width="35%">DESCRIÇÃO P/ SUBITEM CONTÁBIL</th>
            <th width="12%">VALOR HISTÓRICO</th>
            <th width="12%">VALOR REAVALIADO</th>
            <th width="11%">PERDA/DEPRECIAÇÃO</th>
        </tr>

        @php 
            $totHist = 0; 
            $totReav = 0; 
            $totPerda = 0;
        @endphp

        @forelse ($dados as $linha)
            @php 
                $totHist += $linha->valor_historico;
                $totReav += $linha->valor_reavaliado;
                $totPerda += $linha->perda_depreciacao;
            @endphp
            <tr>
                <td style="text-align: center;">{{ $linha->conta_contabil }}</td>
                <td style="text-align: left;">{{ $linha->cod_nat_despesa }}</td>
                <td style="text-align: left;">{{ $linha->descricao }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_historico, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_reavaliado, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->perda_depreciacao, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse

        @if($dados->count() > 0)
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totHist, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totReav, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totPerda, 2, ',', '.') }}</td>
            </tr>
        @endif
    </table>
@endsection