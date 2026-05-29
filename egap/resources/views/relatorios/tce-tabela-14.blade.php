@extends('relatorios.layout-tce')

@section('titulo_pagina', 'TCE IN 34 - Tabela 14')

@section('tabela')
    <table>
        <tr>
            <th colspan="3" class="no-border-bottom">TABELA 14</th>
            <th colspan="4" class="text-center">RESUMO DO INVENTÁRIO DO ALMOXARIFADO - MATERIAL DE CONSUMO</th>
        </tr>
        <tr>
            <th colspan="3" class="no-border-top"></th>
            <th colspan="4" class="text-center">VALORES DO INVENTÁRIO FÍSICO</th>
        </tr>
        <tr>
            <th width="12%">CONTA CONTÁBIL</th>
            <th width="12%">CÓD. NAT. DESPESA</th>
            <th width="36%">DESCRIÇÃO P/ SUBITEM CONTÁBIL</th>
            <th width="10%" class="text-center">SALDO ANTERIOR</th>
            <th width="10%" class="text-center">ENTRADAS</th>
            <th width="10%" class="text-center">SAÍDAS</th>
            <th width="10%" class="text-center">SALDO ATUAL</th>
        </tr>

        @php
            $totais = ['anterior' => 0, 'entradas' => 0, 'saidas' => 0, 'atual' => 0];
        @endphp

        @forelse ($dados as $linha)
            <tr>
                <td>{{ $linha->conta_contabil }}</td>
                <td>{{ $linha->cod_nat_despesa }}</td>
                <td>{{ $linha->descricao }}</td>
                <td class="text-right">{{ number_format($linha->saldo_anterior, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->entradas, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->saidas, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->saldo_atual, 2, ',', '.') }}</td>
            </tr>
            @php
                $totais['anterior'] += $linha->saldo_anterior;
                $totais['entradas'] += $linha->entradas;
                $totais['saidas'] += $linha->saidas;
                $totais['atual'] += $linha->saldo_atual;
            @endphp
        @empty
            <tr>
                <td colspan="7" class="text-center">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse

        <tr>
            <th colspan="3" class="text-right">TOTAL</th>
            <th class="text-right">{{ number_format($totais['anterior'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($totais['entradas'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($totais['saidas'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($totais['atual'], 2, ',', '.') }}</th>
        </tr>
    </table>
@endsection
