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
            <th width="30%">DESCRIÇÃO DO BEM</th>
            <th width="5%">QTDE</th>
            <th width="10%">DATA AQUISIÇÃO</th>
            <th width="10%">VALOR AQUISIÇÃO</th>
            <th width="10%">VALOR AJUSTADO</th>
            <th width="10%">VALOR ATUAL</th>
            <th width="13%">LOCALIZAÇÃO ATUAL</th>
        </tr>

        @php 
            $seq = 1; 
            $tValAquisicao = 0; 
            $tValAjustado = 0; 
            $tValAtual = 0; 
        @endphp

        @forelse ($dados as $linha)
            @php 
                $tValAquisicao += $linha->valor_aquisicao;
                $tValAjustado += $linha->valor_ajustado;
                $tValAtual += $linha->valor_atual;
            @endphp
            <tr>
                <td style="text-align: center;">{{ $seq++ }}</td>
                <td style="text-align: center;">{{ number_format($linha->patrimonio, 0, ',', '.') }}</td>
                <td style="text-align: left;">{{ $linha->descricao }}</td>
                <td style="text-align: center;">1</td>
                <td style="text-align: center;">{{ $linha->data_aquisicao ? \Carbon\Carbon::parse($linha->data_aquisicao)->format('d/m/Y') : '' }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_aquisicao, 4, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_ajustado, 4, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_atual, 4, ',', '.') }}</td>
                <td style="text-align: left;">{{ $linha->setor }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">Nenhum registro encontrado.</td>
            </tr>
        @endforelse

        @if($dados->count() > 0)
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: center; font-weight: bold;">{{ $dados->count() }}</td>
                <td></td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tValAquisicao, 4, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tValAjustado, 4, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tValAtual, 4, ',', '.') }}</td>
                <td></td>
            </tr>
        @endif
    </table>

@endsection