@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Depreciação Mensal - Bens Patrimoniais')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-top: 10px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 5px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; }

        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 14px; padding: 8px; text-transform: uppercase; font-family: Verdana, sans-serif; }
        .caixa-info { border: 1px solid #000 !important; padding: 4px 6px; font-family: Verdana, sans-serif; font-size: 11px; }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório de Depreciação Mensal - Bens Patrimoniais</td>
            <td width="50%" align="right">Setor de Patrimônio</td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <tr><td class="caixa-titulo">RELATÓRIO DE DEPRECIAÇÃO MENSAL</td></tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <td width="6%" class="caixa-info" style="font-weight: bold;">PERÍODO</td>
            <td width="44%" class="caixa-info">
                {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
            </td>
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
            <th colspan="6">RESUMO</th>
        </tr>
        <tr class="linha-cabecalho">
            <th colspan="3" style="border-top: none !important; border-left: none !important;">&nbsp;</th>
            <th colspan="6">VALORES DA DEPRECIAÇÃO</th>
        </tr>
        <tr class="linha-cabecalho">
            <th width="10%">CONTA CONTÁBIL</th>
            <th width="10%">CÓD. NAT. DESPESA</th>
            <th width="28%">DESCRIÇÃO P/ SUBITEM CONTÁBIL</th>
            <th width="8%">VALOR</th>
            <th width="8%">VALOR RESIDUAL</th>
            <th width="9%">DEPRECIAÇÃO MENSAL</th>
            <th width="9%">DEPRECIAÇÃO ACUMULADA</th>
            <th width="9%">VALOR LÍQUIDO CONTÁBIL</th>
            <th width="9%">DEPRECIAÇÃO ACUMULADA DAS SAÍDAS</th>
        </tr>

        @php
            $tVal = 0; $tRes = 0; $tDepM = 0; $tDepA = 0; $tLiq = 0; $tDepS = 0;
        @endphp

        @forelse ($dados as $linha)
            @php
                $tVal += $linha->valor_base;
                $tRes += $linha->valor_residual;
                $tDepM += $linha->dep_mensal;
                $tDepA += $linha->dep_acumulada;
                $tLiq += $linha->valor_liquido;
                $tDepS += $linha->dep_saidas;
            @endphp
            <tr>
                <td style="text-align: center;">{{ $linha->conta_contabil }}</td>
                <td style="text-align: left;">{{ $linha->cod_nat_despesa }}</td>
                <td style="text-align: left;">{{ $linha->descricao }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_base, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_residual, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->dep_mensal, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->dep_acumulada, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_liquido, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->dep_saidas, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse

        @if($dados->count() > 0)
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tVal, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tRes, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tDepM, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tDepA, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tLiq, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tDepS, 2, ',', '.') }}</td>
            </tr>
        @endif
    </table>
@endsection
