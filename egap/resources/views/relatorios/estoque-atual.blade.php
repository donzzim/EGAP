@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório - Estoque Atual')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 11px; margin-bottom: 20px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #ddd; padding: 8px; vertical-align: middle; }
        .linha-cabecalho th { font-weight: bold; text-align: left; border-bottom: 2px solid #ccc !important; }

        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 16px; padding: 6px; font-family: Verdana, sans-serif; margin-bottom: 15px;}

        /* Zebrado Bootstrap clássico */
        .tabela-grid tbody tr:nth-child(odd) { background-color: #f9f9f9; }

        /* Destaca as linhas zeradas */
        .linha-zerada, .linha-zerada td { background-color: #f2dede !important; color: #a94442; }

        .grupo-titulo td { font-weight: bold; font-size: 14px; padding-top: 15px !important; padding-bottom: 10px !important; background-color: #fff !important; border-left: none; border-right: none; }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório - Estoque Atual</td>
            <td width="50%" align="right">Seção de Material de Consumo</td>
        </tr>
    </table>

    <div class="caixa-titulo">
        ESTOQUE ATUAL
    </div>

    @if($dadosAgrupados->isEmpty())
        <h2 style="text-align: center; font-family: Verdana; margin-top: 50px;">Nenhum registro encontrado.</h2>
    @endif

    <table class="tabela-grid">
        <thead>
            <tr class="linha-cabecalho">
                <th width="50%">Material</th>
                <th width="15%" style="text-align: center;">Quantidade Estoque</th>
                <th width="10%" style="text-align: center;">Preço Médio (R$)</th>
                <th width="15%" style="text-align: center;">Valor Total (R$)</th>
                <th width="10%" style="text-align: center;">Atualizado em</th>
            </tr>
        </thead>
        <tbody>
            @php $seq = 1; @endphp

            @foreach ($dadosAgrupados as $grupoNome => $itens)
                <tr class="grupo-titulo">
                    <td colspan="5">{{ $grupoNome }}</td>
                </tr>

                @foreach ($itens as $linha)
                    <tr class="{{ $linha->quantidade_estoque == 0 ? 'linha-zerada' : '' }}">
                        <td>{{ $seq++ }} - {{ $linha->descricao_detalhada }}</td>
                        <td style="text-align: center;">
                            {{ number_format($linha->quantidade_estoque, 0, '', '.') }} - {{ $linha->sigla }}
                        </td>
                        <td style="text-align: center;">{{ number_format($linha->preco_medio_estoque, 2, ',', '.') }}</td>
                        <td style="text-align: center;">{{ number_format($linha->valor_total_estoque, 2, ',', '.') }}</td>
                        <td style="text-align: center;">{{ $linha->data_formatada }}</td>
                    </tr>
                @endforeach
            @endforeach

            @if($dadosAgrupados->count() > 0)
                <tr>
                    <td colspan="5" style="text-align: right; font-weight: bold; font-size: 16px; padding-top: 15px;">
                        Total: {{ number_format($totalGeral, 2, ',', '.') }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

@endsection
