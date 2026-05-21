@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Ajustes após Reavaliação dos Imóveis- Bens Patrimoniais')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-top: 10px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 5px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; }
        
        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 14px; padding: 8px; text-transform: uppercase; font-family: Verdana, sans-serif; }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório de Ajustes após Reavaliação dos Imóveis- Bens Patrimoniais</td>
            <td width="50%" align="right">Seção de Patrimônio</td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <tr><td class="caixa-titulo">RELATÓRIO DE AJUSTES APÓS REAVALIAÇÃO DOS IMÓVEIS</td></tr>
    </table>

    @isset($filtros['data_inicio'], $filtros['data_termino'])
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr>
                <td width="8%" style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 11px; font-weight: bold; padding: 4px 6px;">PERÍODO</td>
                <td width="92%" style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 11px; padding: 4px 6px;">
                    {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    @endisset

    <table class="tabela-grid">
        <tr class="linha-cabecalho">
            <th colspan="3" style="border-top: none !important; border-left: none !important;">&nbsp;</th>
            <th colspan="3">VALOR ANTERIOR</th>
            <th colspan="3">VALOR ATUAL</th>
        </tr>
        <tr class="linha-cabecalho">
            <th width="10%">CONTA CONTÁBIL</th>
            <th width="20%">DESCRIÇÃO P/ SUBITEM CONTÁBIL</th>
            <th width="25%">IMÓVEL</th>
            <th width="9%">VALOR BRUTO</th>
            <th width="9%">DEPRECIAÇÃO ACUMUL.</th>
            <th width="9%">VALOR LIQ.</th>
            <th width="6%">DATA REAVALIAÇÃO</th>
            <th width="6%">VALOR ATUAL</th>
            <th width="6%">AJUSTE CONTÁBIL</th>
        </tr>

        @php 
            $tValBruto = 0; 
            $tDepAcumulada = 0; 
            $tValLiq = 0; 
            $tValAtual = 0; 
            $tAjusteContabil = 0;
        @endphp

        @forelse ($dados as $linha)
            @php 
                $tValBruto += $linha->valor_bruto;
                $tDepAcumulada += $linha->depreciacao_acumulada;
                $tValLiq += $linha->valor_liquido_contabil;
                $tValAtual += $linha->valor_atual;
                $tAjusteContabil += $linha->ajuste_contabil;
            @endphp
            <tr>
                <td style="text-align: center;">{{ $linha->conta_contabil }}</td>
                <td style="text-align: left;">{{ $linha->descricao_subitem }}</td>
                <td style="text-align: left;">
                    @if($linha->inscricao_generica)
                        {{ $linha->inscricao_generica }}<br>
                    @endif
                    @if($linha->num_registro)
                        {{ $linha->num_registro }}<br>
                    @endif
                    {{ $linha->descricao_imovel }}
                </td>
                <td style="text-align: right;">{{ number_format($linha->valor_bruto, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->depreciacao_acumulada, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_liquido_contabil, 2, ',', '.') }}</td>
                <td style="text-align: center;">{{ $linha->data_reavaliacao ? \Carbon\Carbon::parse($linha->data_reavaliacao)->format('d/m/Y') : '' }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_atual, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->ajuste_contabil, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse

        @if($dados->count() > 0)
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tValBruto, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tDepAcumulada, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tValLiq, 2, ',', '.') }}</td>
                <td>&nbsp;</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tValAtual, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tAjusteContabil, 2, ',', '.') }}</td>
            </tr>
        @endif
    </table>
@endsection