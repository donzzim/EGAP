@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'TCE IN 34 - Tabela 11')

@section('tabela')
    <table>
        <tr>
            <th colspan="3" class="no-border-bottom">TABELA 11</th>
            <th colspan="9" class="text-center">DEMONSTRATIVO ANALÍTICO DAS ENTRADAS E SAÍDAS DE BENS MÓVEIS</th>
        </tr>
        <tr>
            <th colspan="3" class="no-border-top"></th>
            <th colspan="4" class="text-center">ENTRADAS</th>
            <th colspan="5" class="text-center">SAÍDAS</th>
        </tr>
        <tr>
            <th width="8%">CONTA CONTÁBIL</th>
            <th width="8%">CÓD. NAT. DESPESA</th>
            <th width="20%">DESCRIÇÃO P/ SUBITEM CONTÁBIL</th>
            
            <th width="7%" class="text-center">INCORPORADAS<br>AO PATRIMÔNIO</th>
            <th width="7%" class="text-center">DOAÇÃO</th>
            <th width="7%" class="text-center">OUTRAS</th>
            <th width="8%" class="text-center">TOTAL</th>

            <th width="7%" class="text-center">ALIENAÇÃO</th>
            <th width="7%" class="text-center">DOAÇÃO</th>
            <th width="7%" class="text-center">PERDAS</th>
            <th width="7%" class="text-center">OUTRAS</th>
            <th width="8%" class="text-center">TOTAL</th>
        </tr>

        @php
            $t = [
                'ent_inc' => 0, 'ent_doa' => 0, 'ent_out' => 0, 'ent_tot' => 0,
                'sai_ali' => 0, 'sai_doa' => 0, 'sai_per' => 0, 'sai_out' => 0, 'sai_tot' => 0
            ];
        @endphp

        @forelse ($dados as $linha)
            <tr>
                <td>{{ $linha->conta_contabil }}</td>
                <td>{{ $linha->cod_nat_despesa }}</td>
                <td>{{ $linha->descricao }}</td>
                
                <td class="text-right">{{ number_format($linha->ent_incorporadas, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->ent_doacao, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->ent_outras, 2, ',', '.') }}</td>
                <td class="text-right"><b>{{ number_format($linha->total_entradas, 2, ',', '.') }}</b></td>
                
                <td class="text-right">{{ number_format($linha->sai_alienacao, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->sai_doacao, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->sai_perda, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($linha->sai_outras, 2, ',', '.') }}</td>
                <td class="text-right"><b>{{ number_format($linha->total_saidas, 2, ',', '.') }}</b></td>
            </tr>
            @php
                $t['ent_inc'] += $linha->ent_incorporadas; $t['ent_doa'] += $linha->ent_doacao;
                $t['ent_out'] += $linha->ent_outras;       $t['ent_tot'] += $linha->total_entradas;
                $t['sai_ali'] += $linha->sai_alienacao;    $t['sai_doa'] += $linha->sai_doacao;
                $t['sai_per'] += $linha->sai_perda;        $t['sai_out'] += $linha->sai_outras;
                $t['sai_tot'] += $linha->total_saidas;
            @endphp
        @empty
            <tr>
                <td colspan="12" class="text-center">Nenhum registro encontrado no período.</td>
            </tr>
        @endforelse

        <tr>
            <th colspan="3" class="text-right">TOTAL</th>
            <th class="text-right">{{ number_format($t['ent_inc'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['ent_doa'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['ent_out'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['ent_tot'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['sai_ali'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['sai_doa'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['sai_per'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['sai_out'], 2, ',', '.') }}</th>
            <th class="text-right">{{ number_format($t['sai_tot'], 2, ',', '.') }}</th>
        </tr>
    </table>
@endsection