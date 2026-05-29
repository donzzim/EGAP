@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório Imóveis - Teste do Saldo Anterior')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-top: 10px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #ddd !important; padding: 6px; }
        .linha-cabecalho th { font-weight: bold; background-color: #f9f9f9; text-align: left; }

        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 14px; padding: 6px; text-transform: uppercase; font-family: Verdana, sans-serif; margin-bottom: 10px;}
        .texto-datas { font-family: Verdana, sans-serif; font-size: 10px; margin-bottom: 10px; }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório Imóveis - Teste do Saldo Anterior</td>
            <td width="50%" align="right">Seção de Patrimônio</td>
        </tr>
    </table>

    <div class="caixa-titulo">
        SALDO ANTERIOR DE IMÓVEIS
    </div>

    <div class="texto-datas">
        Data início: <b>{{ $inicioRaw }}</b> Data Término: <b>{{ $terminoRaw }}</b>
    </div>

    <table class="tabela-grid">
        <tr class="linha-cabecalho">
            <th width="15%">Imóvel</th>
            <th width="8%" style="text-align: right;">Saldo Anterior</th>
            <th width="8%" style="text-align: right;">Entrada Obras</th>
            <th width="8%" style="text-align: right;">Entrada Ajustes</th>
            <th width="8%" style="text-align: right;">Saída Ajustes</th>
            <th width="8%" style="text-align: center;">Data Aquisição</th>
            <th width="10%" style="text-align: right;">Valor Hist. 1a Avaliação</th>
            <th width="7%" style="text-align: center;">Data Baixa</th>
            <th width="8%" style="text-align: center;">Data Trans. Ativo</th>
            <th width="7%" style="text-align: center;">Data Reavaliação</th>
            <th width="8%" style="text-align: right;">Valor Últ. Reavaliação</th>
            <th width="5%" style="text-align: center;">Situação</th>
        </tr>

        @php $seq = 1; @endphp

        @forelse ($dados as $linha)
            <tr>
                <td>{{ $seq++ }} - {{ $linha->descricao }}</td>
                <td style="text-align: right; color: red; font-weight: bold;">{{ number_format($linha->saldo_anterior, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->valorobra_entrada, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->ajustecontabil_entrada, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($linha->ajustecontabil_saida, 2, ',', '.') }}</td>
                <td style="text-align: center;">{{ $linha->data_aquisicao ? \Carbon\Carbon::parse($linha->data_aquisicao)->format('d/m/Y') : '' }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_historico, 2, ',', '.') }}</td>
                <td style="text-align: center;">{{ $linha->data_baixa ? \Carbon\Carbon::parse($linha->data_baixa)->format('d/m/Y') : '' }}</td>
                <td style="text-align: center;">{{ $linha->data_situacao ? \Carbon\Carbon::parse($linha->data_situacao)->format('d/m/Y') : '' }}</td>
                <td style="text-align: center;">{{ $linha->data_reavaliacao ? \Carbon\Carbon::parse($linha->data_reavaliacao)->format('d/m/Y') : '' }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_reavaliacao, 2, ',', '.') }}</td>
                <td style="text-align: center;">{{ $linha->situacao_descricao }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="12" style="text-align: center; padding: 20px;">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse
    </table>

@endsection
