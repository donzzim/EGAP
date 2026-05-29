@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Baixa por Processo - Bens Patrimoniais')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 12px; margin-top: 5px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 6px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; }

        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 14px; padding: 8px; text-transform: uppercase; font-family: Verdana, sans-serif; }
    </style>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr><td class="caixa-titulo">RELATÓRIO DE BAIXA DOS BENS PATRIMONIAIS</td></tr>
    </table>

    <table class="tabela-grid">
        <tr class="linha-cabecalho">
            <th width="25%">PROCESSO BAIXA</th>
            <th width="25%">QUANTIDADE</th>
            <th width="25%">VALOR AQUISIÇÃO</th>
            <th width="25%">VALOR REAVALIADO</th>
        </tr>

        @php
            $totQtd = 0;
            $totAq = 0;
            $totReav = 0;
        @endphp

        @forelse ($dados as $linha)
            @php
                $totQtd += $linha->quantidade;
                $totAq += $linha->valor_aquisicao;
                $totReav += $linha->valor_reavaliado;
            @endphp
            <tr>
                <td style="text-align: center;">{{ $linha->processo }}</td>
                <td style="text-align: center;">{{ $linha->quantidade }}</td>
                <td style="text-align: center;">{{ number_format($linha->valor_aquisicao, 2, ',', '.') }}</td>
                <td style="text-align: center;">{{ number_format($linha->valor_reavaliado, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px;">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse

        @if($dados->count() > 0)
            <tr>
                <td style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: center; font-weight: bold;">{{ $totQtd }}</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($totAq, 2, ',', '.') }}</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($totReav, 2, ',', '.') }}</td>
            </tr>
        @endif
    </table>
@endsection
