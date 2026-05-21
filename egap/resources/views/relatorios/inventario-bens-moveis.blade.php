@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Bens Patrimoniais')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-top: 5px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 5px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; font-size: 10px; }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 2px solid #000;">
        <tr>
            <td width="50%" align="left">Relatório de Bens Patrimoniais</td>
            <td width="50%" align="right">Seção de Patrimônio</td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <td style="text-align: center; font-weight: bold; font-size: 18px;">
                INVENTÁRIO ANUAL {{ $ano_inventario }} - BENS MÓVEIS
            </td>
        </tr>
    </table>

    @if($dados->isEmpty())
        <div style="font-family: Verdana; font-size: 11px; font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #000; padding-bottom: 3px;">
            CONTA CONTÁBIL: NENHUMA CONTA ENCONTRADA / SEM DADOS
        </div>
    @else
        <div style="font-family: Verdana; font-size: 11px; font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #000; padding-bottom: 3px; text-transform: uppercase;">
            CONTA CONTÁBIL: {{ $dados->first()->conta_codigo }} - {{ $dados->first()->conta_titulo }}
        </div>
    @endif

    <table class="tabela-grid">
        <tr class="linha-cabecalho">
            <th width="4%">ITEM</th>
            <th width="8%">PATRIMÔNIO</th>
            <th width="28%">DESCRIÇÃO DO BEM</th>
            <th width="5%">QTDE</th>
            <th width="10%">DATA AQUISIÇÃO</th>
            <th width="12%">VALOR HISTÓRICO OU APÓS AJUSTE EXEC. ANTERIORES</th>
            <th width="11%">DEPRECIAÇÃO ACUMULADA</th>
            <th width="10%">VALOR CONTÁBIL</th>
            <th width="12%">LOCALIZAÇÃO ATUAL</th>
        </tr>

        @php 
            $seq = 1; 
            $tValReavaliado = 0; 
            $tDepAcumulada = 0; 
            // O legado calculava o total final de Valor Contábil subtraindo as outras duas somas
        @endphp

        @forelse ($dados as $linha)
            @php 
                $tValReavaliado += $linha->valor_reavaliado;
                $tDepAcumulada += $linha->depreciacao_acumulada;
            @endphp
            <tr>
                <td style="text-align: center;">{{ $seq++ }}</td>
                <td style="text-align: center;">{{ number_format($linha->patrimonio, 0, ',', '.') }}</td>
                <td style="text-align: left;">{{ $linha->descricao }}</td>
                <td style="text-align: center;">1</td>
                <td style="text-align: center;">{{ $linha->data_aquisicao ? \Carbon\Carbon::parse($linha->data_aquisicao)->format('d/m/Y') : '' }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_reavaliado, 4, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->depreciacao_acumulada, 4, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_liquido, 4, ',', '.') }}</td>
                <td style="text-align: left;">{{ $linha->setor }}</td>
            </tr>
        @empty
            @endforelse

        <tr>
            <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL</td>
            <td style="text-align: center; font-weight: bold;">{{ $dados->count() }}</td>
            <td></td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($tValReavaliado, 4, ',', '.') }}</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($tDepAcumulada, 4, ',', '.') }}</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($tValReavaliado - $tDepAcumulada, 4, ',', '.') }}</td>
            <td></td>
        </tr>
    </table>

@endsection