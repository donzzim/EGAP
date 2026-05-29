@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Gasto Anual com Itens de Estoque')

@section('tabela')
    <style>
        /* Esconder a tabela do layout-tce padrão, pois este relatório tem um cabeçalho único */
        table[width="100%"] { display: none; }
        .tabela-gasto { display: table !important; width: 100%; border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; font-size: 12px; margin-top: 20px;}

        .relatorio-title { font-family: Helvetica, Arial, sans-serif; font-size: 22px; font-weight: bold; margin-bottom: 10px; margin-top: 10px; }

        .tabela-gasto th { border-bottom: 2px solid #ddd; padding: 8px; text-align: left; font-weight: bold; }
        .tabela-gasto td { border-top: 1px solid #ddd; padding: 8px; text-align: left; }

        /* Simula o visual table-striped do bootstrap na tabela */
        .tabela-gasto tbody tr:nth-child(odd) { background-color: #f9f9f9; }

        /* Estilos dos totais */
        .linha-subtotal td { background-color: #d9d9d9 !important; font-weight: bold; }
        .linha-total td { background-color: #666 !important; color: white !important; font-weight: bold; }

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
    </style>

    <div class="relatorio-title">
        Gasto Anual com Itens de Estoque - {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
    </div>

    <table class="tabela-gasto">
        <thead>
            <tr>
                <th width="30%">Elemento Despesa</th>
                <th width="50%">Material</th>
                <th width="10%" class="text-right">Quantidade</th>
                <th width="10%" class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGeralValor = 0; @endphp

            @forelse($dadosAgrupados as $elemento => $materiais)
                @php $subtotalValor = 0; @endphp

                @foreach($materiais as $linha)
                    @php
                        $subtotalValor += $linha->valor;
                        $totalGeralValor += $linha->valor;
                    @endphp
                    <tr>
                        <td>{{ $elemento }}</td>
                        <td>{{ $linha->material }}</td>
                        <td class="text-right">{{ number_format($linha->qtde, 0, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($linha->valor, 2, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr class="linha-subtotal">
                    <td>{{ $elemento }}</td>
                    <td>SUB TOTAL</td>
                    <td></td>
                    <td class="text-right">R$ {{ number_format($subtotalValor, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px;">Nenhum registro encontrado no período selecionado.</td>
                </tr>
            @endforelse

            @if($dadosAgrupados->count() > 0)
                <tr class="linha-total">
                    <td><b>TOTAL GERAL</b></td>
                    <td></td>
                    <td></td>
                    <td class="text-right">R$ {{ number_format($totalGeralValor, 2, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection
