@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'TCE IN 34 - Tabela 12')

@section('tabela')
    <table>
        <tr>
            <th colspan="2" class="no-border-bottom">TABELA 12</th>
            <th colspan="6" class="text-center">RESUMO DO INVENTÁRIO BENS IMÓVEIS</th>
        </tr>
        <tr>
            <th colspan="2" class="no-border-top"></th>
            <th colspan="6" class="text-center">VALORES DO INVENTÁRIO FÍSICO</th>
        </tr>
        <tr>
            <th width="15%">CONTA CONTÁBIL</th>
            <th width="37%">DESCRIÇÃO P/ SUBITEM CONTÁBIL</th>
            <th width="8%" class="text-center">SALDO ANTERIOR</th>
            <th width="8%" class="text-center">ENTRADAS</th>
            <th width="8%" class="text-center">SAÍDAS</th>
            <th width="8%" class="text-center">SALDO BRUTO</th>
            <th width="8%" class="text-center">DEPRECIAÇÃO ACUMULADA</th>
            <th width="8%" class="text-center">SALDO ATUAL</th>
        </tr>

        @php
            $totais = ['anterior' => 0, 'entradas' => 0, 'saidas' => 0, 'bruto' => 0, 'depreciacao' => 0, 'atual' => 0];
        @endphp

        @forelse ($dados as $linha)
            <tr>
                <td class="text-center">{{ $linha->conta_contabil }}</td>
                <td>{{ $linha->descricao }}</td>
                <td class="text-right">{{ number_format($linha->saldo_anterior, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->entradas, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->saidas, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->saldo_bruto, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->depreciacao_acumulada, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->saldo_atual, 2, ',', '.') }}</td>
            </tr>
            @php
                $totais['anterior'] += $linha->saldo_anterior;
                $totais['entradas'] += $linha->entradas;
                $totais['saidas'] += $linha->saidas;
                $totais['bruto'] += $linha->saldo_bruto;
                $totais['depreciacao'] += $linha->depreciacao_acumulada;
                $totais['atual'] += $linha->saldo_atual;
            @endphp
        @empty
            <tr>
                <td colspan="8" class="text-center">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse

        <tr>
            <th colspan="2" class="text-right">TOTAL</th>
            <th class="text-right">{{ number_format($totais['anterior'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($totais['entradas'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($totais['saidas'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($totais['bruto'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($totais['depreciacao'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($totais['atual'], 2, ',', '.') }}</th>
        </tr>
    </table>
@endsection