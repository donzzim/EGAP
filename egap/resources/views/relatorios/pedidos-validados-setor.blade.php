@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de pedido validado pelo setor')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-bottom: 30px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 6px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; font-size: 10px; }

        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 14px; padding: 6px; font-family: Verdana, sans-serif; margin-bottom: 15px;}
        .pedido-header td { font-weight: bold; font-size: 12px; text-align: left; }

        @media print {
            .nova-pagina { page-break-before: always; }
        }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório de pedido validado pelo setor</td>
            <td width="50%" align="right">Seção de Material de Consumo</td>
        </tr>
    </table>

    <div class="caixa-titulo">
        Pedidos Validados do Setor
    </div>

    @isset($filtros['data_inicio'], $filtros['data_termino'])
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; padding: 6px;">
                    Período: {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    @endisset

    @if($dadosAgrupados->isEmpty())
        <h2 style="text-align: center; font-family: Verdana; margin-top: 50px;">Nenhum registro encontrado para os filtros informados.</h2>
    @endif

    @foreach($dadosAgrupados as $pedidoId => $itens)
        @php
            $primeiroItem = $itens->first();
        @endphp

        <table class="tabela-grid">
            <tr class="pedido-header">
                <td colspan="7">
                    {{ $primeiroItem->numero_formatado }} -- {{ mb_strtoupper($primeiroItem->setor) }} -- {{ mb_strtoupper($primeiroItem->solicitante) }}
                </td>
            </tr>
            <tr class="linha-cabecalho">
                <th width="5%">ITEM</th>
                <th width="35%">DESCRIÇÃO</th>
                <th width="15%">UNIDADE DE MEDIDA</th>
                <th width="10%">QTDE. SOLICITADA</th>
                <th width="10%">QTDE. ATENDIDA</th>
                <th width="12%">VALOR MÉDIO</th>
                <th width="13%">VALOR TOTAL MÉDIO</th>
            </tr>

            @php
                $seq = 1;
                $somaQtdSolicitada = 0;
                $somaQtdAtendida = 0;
                $somaValorMedio = 0;
                $somaValorTotal = 0;
            @endphp

            @foreach($itens as $item)
                @php
                    $somaQtdSolicitada += $item->qtde_solicitada;
                    $somaQtdAtendida += $item->qtde_atendida;
                    $somaValorMedio += $item->valor_medio;
                    $somaValorTotal += $item->valor_total;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $seq++ }}</td>
                    <td style="text-align: left;">
                        @if($item->descricao_detalhada)
                            {{ $item->descricao_detalhada }}<br>
                        @endif
                        {{ $item->descricao_resumida }} - {{ $item->elemento }}
                    </td>
                    <td style="text-align: center;">{{ $item->sigla }} - {{ $item->unidade }}</td>
                    <td style="text-align: center;">{{ number_format($item->qtde_solicitada, 0, '', '.') }}</td>
                    <td style="text-align: center;">{{ number_format($item->qtde_atendida, 0, '', '.') }}</td>
                    <td style="text-align: center;">R$ {{ number_format($item->valor_medio, 4, ',', '.') }}</td>
                    <td style="text-align: center;">R$ {{ number_format($item->valor_total, 4, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="3" style="text-align: center; font-weight: bold; text-transform: uppercase;">Total:</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($somaQtdSolicitada, 0, '', '.') }}</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($somaQtdAtendida, 0, '', '.') }}</td>
                <td style="text-align: center; font-weight: bold;">
                    R$ {{ number_format(($seq > 1) ? ($somaValorMedio / ($seq - 1)) : 0, 4, ',', '.') }}
                </td>
                <td style="text-align: center; font-weight: bold;">R$ {{ number_format($somaValorTotal, 4, ',', '.') }}</td>
            </tr>
        </table>
    @endforeach

@endsection
