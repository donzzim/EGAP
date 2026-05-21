@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'TCE IN 34 - Tabela 13')

@section('tabela')
    <table>
        <tr>
            <th colspan="2" class="no-border-bottom">TABELA 13</th>
            <th colspan="11" class="text-center">DEMONSTRATIVO ANALÍTICO DAS ENTRADAS E SAÍDAS DE BENS IMÓVEIS</th>
        </tr>
        <tr>
            <th colspan="2" class="no-border-top"></th>
            <th colspan="6" class="text-center">ENTRADAS</th>
            <th colspan="5" class="text-center">SAÍDAS</th>
        </tr>
        <tr>
            <th width="10%">CONTA CONTÁBIL</th>
            <th width="20%">DESCRIÇÃO P/ SUBITEM CONTÁBIL</th>
            <th width="6%" class="text-center">COMPRAS</th>
            <th width="6%" class="text-center">DOAÇÃO</th>
            <th width="6%" class="text-center">CONSTRUÇÃO/REFORMA</th>
            <th width="6%" class="text-center">DESAPROPRIAÇÃO</th>
            <th width="6%" class="text-center">OUTRAS</th>
            <th width="7%" class="text-center">TOTAL</th>
            <th width="6%" class="text-center">ALIENAÇÃO</th>
            <th width="6%" class="text-center">DOAÇÃO</th>
            <th width="6%" class="text-center">PERDAS</th>
            <th width="6%" class="text-center">OUTRAS</th>
            <th width="7%" class="text-center">TOTAL</th>
        </tr>

        @php
            $t = ['e_com' => 0, 'e_doa' => 0, 'e_con' => 0, 'e_des' => 0, 'e_out' => 0, 'e_tot' => 0,
                  's_ali' => 0, 's_doa' => 0, 's_per' => 0, 's_out' => 0, 's_tot' => 0];
        @endphp

        @forelse ($dados as $linha)
            <tr>
                <td>{{ $linha->conta_contabil }}</td>
                <td>{{ $linha->descricao }}</td>
                <td class="text-right">{{ number_format($linha->ent_compras, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->ent_doacao, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->ent_construcao, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->ent_desapropriacao, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->ent_outras, 2, ',', '.') }}</td>
                <td class="text-right"><b>{{ number_format($linha->total_entradas, 2, ',', '.') }}</b></td>
                <td class="text-right">{{ number_format($linha->sai_alienacao, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->sai_doacao, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->sai_perdas, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->sai_outras, 2, ',', '.') }}</td>
                <td class="text-right"><b>{{ number_format($linha->total_saidas, 2, ',', '.') }}</b></td>
            </tr>
            @php
                $t['e_com'] += $linha->ent_compras; $t['e_doa'] += $linha->ent_doacao; 
                $t['e_con'] += $linha->ent_construcao; $t['e_des'] += $linha->ent_desapropriacao; 
                $t['e_out'] += $linha->ent_outras; $t['e_tot'] += $linha->total_entradas;
                $t['s_ali'] += $linha->sai_alienacao; $t['s_doa'] += $linha->sai_doacao; 
                $t['s_per'] += $linha->sai_perdas; $t['s_out'] += $linha->sai_outras; 
                $t['s_tot'] += $linha->total_saidas;
            @endphp
        @empty
            <tr><td colspan="13" class="text-center">Nenhum registro encontrado.</td></tr>
        @endforelse

        <tr>
            <th colspan="2" class="text-right">TOTAL</th>
            <th class="text-right">{{ number_format($t['e_com'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['e_doa'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['e_con'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['e_des'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['e_out'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['e_tot'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['s_ali'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['s_doa'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['s_per'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['s_out'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['s_tot'], 2, ',', '.') }}</th>
        </tr>
    </table>
@endsection