@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Relatório Analítico Contábil')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-top: 10px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 4px; text-align: center; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; }
        
        .caixa-info { border: 1px solid #000 !important; padding: 4px 6px; font-family: Verdana, sans-serif; font-size: 11px; }

        .tabela-footer { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-top: 0px; }
        .tabela-footer td { border: 1px solid #000 !important; border-top: none !important; padding: 6px; text-align: center; font-weight: bold; }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório Analítico Contábil</td>
            <td width="50%" align="right">Setor de Patrimônio</td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
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
            <th>CONTA CONTÁBIL</th>
            <th>PATRIMÔNIO</th>
            <th>DATA DE AQUISIÇÃO</th>
            <th>VALOR DE ENTRADA</th>
            <th>VIDA ÚTIL REMANESCENTE</th>
            <th>DATA DISPONIBILIDADE</th>
            <th>VALOR LÍQUIDO CONTÁBIL</th>
            <th>VALOR RESIDUAL</th>
            <th>VALOR REAVALIADO</th>
        </tr>

        @php 
            $totEntrada = 0; 
            $totLiq = 0; 
            $totRes = 0;
            $totReav = 0;
        @endphp

        @forelse ($dados as $linha)
            @php 
                $totEntrada += $linha->valor_entrada;
                $totLiq += $linha->valor_liquido_contabil;
                $totRes += $linha->valor_residual;
                $totReav += $linha->valor_reavaliado;

                // Formatação das datas para evitar exibir zeros inválidos
                $dataAq = ($linha->data_aquisicao && $linha->data_aquisicao != '0000-00-00 00:00:00') ? \Carbon\Carbon::parse($linha->data_aquisicao)->format('d/m/Y') : '00/00/0000';
                $dataDisp = ($linha->data_disponibilidade && $linha->data_disponibilidade != '0000-00-00 00:00:00') ? \Carbon\Carbon::parse($linha->data_disponibilidade)->format('d/m/Y') : '00/00/0000';
            @endphp
            <tr>
                <td>{{ $linha->conta_contabil }}</td>
                <td>{{ $linha->patrimonio }}</td>
                <td>{{ $dataAq }}</td>
                <td>{{ number_format($linha->valor_entrada, 2, '.', '') }}</td>
                <td>{{ $linha->vida_util_remanescente }}</td>
                <td>{{ $dataDisp }}</td>
                <td>{{ number_format($linha->valor_liquido_contabil, 2, '.', '') }}</td>
                <td>{{ number_format($linha->valor_residual, 2, '.', '') }}</td>
                <td>{{ number_format($linha->valor_reavaliado, 2, '.', '') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="padding: 20px;">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse
    </table>

    <table class="tabela-footer">
        <tr>
            <td width="15%" style="text-align: left;">Total de itens: {{ $dados->count() }}</td>
            <td width="20%" style="text-align: left;">Total Valor de Aquisicao: {{ number_format($totEntrada, 2, '.', ',') }}</td>
            <td width="25%" style="text-align: left;">Total Valor Líquido Contábil: {{ number_format($totLiq, 2, '.', ',') }}</td>
            <td width="20%" style="text-align: left;">Total Valor Residual: {{ number_format($totRes, 2, '.', ',') }}</td>
            <td width="20%" style="text-align: left;">Total Valor Reavaliado: {{ number_format($totReav, 2, '.', ',') }}</td>
        </tr>
    </table>
@endsection