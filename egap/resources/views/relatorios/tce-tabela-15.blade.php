@extends('relatorios.layout-tce')

@section('titulo_pagina', 'TCE IN 34 - Tabela 15')

@section('tabela')
    <table>
        <tr>
            <th colspan="3" class="no-border-bottom">TABELA 15</th>
            <th colspan="9" class="text-center">DEMONSTRATIVO ANALÍTICO DAS ENTRADAS E SAÍDAS DO ALMOXARIFADO DE MATERIAL DE CONSUMO</th>
        </tr>
        <tr>
            <th colspan="3" class="no-border-top"></th>
            <th colspan="9" class="text-center">VALORES DO INVENTÁRIO FÍSICO</th>
        </tr>
        <tr>
            <th colspan="3"></th>
            <th colspan="4" class="text-center">ENTRADAS</th>
            <th colspan="5" class="text-center">SAÍDAS</th>
        </tr>
        <tr>
            <th width="8%">CONTA CONTÁBIL</th>
            <th width="8%">CÓD. NAT. DESPESA</th>
            <th width="20%">DESCRIÇÃO P/ SUBITEM CONTÁBIL</th>

            <th width="7%" class="text-center">COMPRAS</th>
            <th width="7%" class="text-center">DOAÇÃO<br>TRANSFERÊNCIA</th>
            <th width="7%" class="text-center">REENTRADAS<br>OUTRAS</th>
            <th width="8%" class="text-center">TOTAL</th>

            <th width="7%" class="text-center">CONSUMO</th>
            <th width="7%" class="text-center">DOAÇÃO<br>TRANSFERÊNCIA</th>
            <th width="7%" class="text-center">PERDAS</th>
            <th width="7%" class="text-center">OUTRAS</th>
            <th width="8%" class="text-center">TOTAL</th>
        </tr>

        @php
            $t = [
                'ec' => 0, 'ed' => 0, 'eo' => 0, 'et' => 0,
                'sc' => 0, 'sd' => 0, 'sp' => 0, 'so' => 0, 'st' => 0
            ];
        @endphp

        @forelse ($dados as $linha)
            <tr>
                <td class="text-center">{{ $linha->conta_contabil }}</td>
                <td>{{ $linha->cod_nat_despesa }}</td>
                <td>{{ $linha->descricao }}</td>

                <td class="text-right">{{ number_format($linha->ent_compras, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->ent_doacao, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->ent_outras, 2, ',', '.') }}</td>
                <td class="text-right"><b>{{ number_format($linha->total_entradas, 2, ',', '.') }}</b></td>

                <td class="text-right">{{ number_format($linha->sai_consumo, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->sai_doacao, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->sai_perdas, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->sai_outras, 2, ',', '.') }}</td>
                <td class="text-right"><b>{{ number_format($linha->total_saidas, 2, ',', '.') }}</b></td>
            </tr>
            @php
                $t['ec'] += $linha->ent_compras; $t['ed'] += $linha->ent_doacao;
                $t['eo'] += ($linha->ent_compras + $linha->ent_outras);
                $t['et'] += $linha->ent_compras;
                $t['sc'] += $linha->sai_consumo; $t['sd'] += $linha->sai_doacao;
                $t['sp'] += $linha->sai_perdas;  $t['so'] += $linha->sai_outras;
                $t['st'] += $linha->total_saidas;
            @endphp
        @empty
            <tr>
                <td colspan="12" class="text-center">Nenhum registro encontrado no período.</td>
            </tr>
        @endforelse

        <tr>
            <th colspan="3" class="text-right">TOTAL</th>
            <th class="text-right">{{ number_format($t['ec'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['ed'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['eo'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['et'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['sc'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['sd'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['sp'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['so'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['st'], 2, ',', '.') }}</th>
        </tr>
    </table>
@endsection
