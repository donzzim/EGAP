@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Acurácia Documental dos Bens Patrimoniais Ativos')

@section('tabela')
    <style>
        .tabela-grid { width: 50%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 11px; margin-bottom: 20px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 6px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; font-size: 11px; }

        .texto-resumo { font-family: Verdana, sans-serif; font-size: 14px; font-weight: bold; margin-top: 15px; }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 30px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório de Acurácia Documental dos Bens Patrimoniais Ativos</td>
            <td width="50%" align="right">Seção de Patrimônio</td>
        </tr>
    </table>

    <table class="tabela-grid">
        <tr class="linha-cabecalho">
            <th width="50%">BEM PATRIMONIAL ATIVO</th>
            <th width="25%">QUANTIDADE</th>
            <th width="25%">PERC.</th>
        </tr>

        <tr>
            <td style="text-align: left;">Com 1 ou mais TR's Válidos</td>
            <td style="text-align: right;">{{ number_format($totalValidos, 0, ',', '.') }}</td>
            <td style="text-align: right;">{{ number_format($percValidos, 2, ',', '.') }}%</td>
        </tr>
        <tr>
            <td style="text-align: left;">Outras situações</td>
            <td style="text-align: right;">{{ number_format($totalOutros, 0, ',', '.') }}</td>
            <td style="text-align: right;">{{ number_format($percOutros, 2, ',', '.') }}%</td>
        </tr>
    </table>

    <div class="texto-resumo">
        Total Geral de Bens Móveis Ativos: {{ number_format($totalAtivo, 0, ',', '.') }}
    </div>

@endsection
