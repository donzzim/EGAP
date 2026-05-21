@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Bens Incorporados - Bens Patrimoniais')

@section('tabela')
    <style>
        /* Neutraliza as bordas injetadas pelo layout-tce.blade.php */
        .tabela-limpa {
            width: 100%;
            border-collapse: collapse;
            font-family: Verdana, sans-serif;
            font-size: 12px;
        }
        .tabela-limpa th, .tabela-limpa td {
            border-left: none !important;
            border-right: none !important;
            padding: 4px;
        }
        .linha-cabecalho th {
            border-top: 1px solid #000 !important;
            border-bottom: 1px solid #000 !important;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 4px;
        }
        .linha-conta td {
            border-bottom: 1px solid #000 !important;
            padding: 8px 4px;
        }
        .linha-item-topo td {
            padding-top: 8px;
        }
        .linha-item-base td {
            border-bottom: 1px solid #ccc !important;
            padding-bottom: 8px;
        }
        .linha-subtotal td {
            border-bottom: 1px solid #000 !important;
            padding: 8px 4px;
        }
        
        /* Caixas do Cabeçalho */
        .caixa-titulo {
            border: 1px solid #000 !important;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 5px;
            text-transform: uppercase;
            font-family: Verdana, sans-serif;
        }
        .caixa-info {
            border: 1px solid #000 !important;
            padding: 5px;
            font-family: Verdana, sans-serif;
        }
    </style>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <tr>
            <td class="caixa-titulo">
                RELATÓRIO DE INCORPORAÇÃO DOS BENS PATRIMONIAIS
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <td width="8%" class="caixa-info" style="font-weight: bold; font-size: 11px;">PERÍODO</td>
            <td width="40%" class="caixa-info" style="font-size: 12px;">
                {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
            </td>
            <td width="2%" style="border: none !important;"></td>
            <td width="50%" class="caixa-info" style="font-size: 12px;">
                @if(($filtros['situacao_contabil'] ?? 'Todos') !== 'Todos')
                    <span style="font-weight: bold; font-size: 11px;">SITUAÇÃO DO INVENTÁRIO:</span> {{ mb_strtoupper($filtros['situacao_contabil']) }}
                @else
                    &nbsp;
                @endif
            </td>
        </tr>
    </table>

    <table class="tabela-limpa">
        <tr class="linha-cabecalho">
            <th width="4%" style="text-align: center;">ITEM</th>
            <th width="8%" style="text-align: center;">PATRIM.</th>
            <th width="46%" style="text-align: left;">DESCRIÇÃO DO BEM/MARCA/MODELO</th>
            <th width="14%" style="text-align: center;">FORMA AQUISIÇÃO</th>
            <th width="12%" style="text-align: center;">VALOR</th>
            <th width="16%" style="text-align: center;">DATA DA<br>INCORPORAÇÃO</th>
        </tr>

        @php 
            $totalGeral = 0; 
            $seq = 1;
        @endphp

        @forelse ($dadosAgrupados as $conta => $itens)
            <tr class="linha-conta">
                <td colspan="6">{{ $conta }}</td>
            </tr>
            
            @php $subtotal = 0; @endphp

            @foreach ($itens as $linha)
                @php 
                    $marca = $linha->marca ? '/' . $linha->marca : '';
                    $modelo = $linha->modelo ? '/' . $linha->modelo : '';
                    $subtotal += $linha->valor;
                    $totalGeral += $linha->valor;
                @endphp
                
                <tr class="linha-item-topo">
                    <td rowspan="2" style="text-align: center; border-bottom: 1px solid #ccc !important; vertical-align: middle;">{{ $seq++ }}</td>
                    <td style="text-align: center;">{{ number_format($linha->patrimonio, 0, ',', '.') }}</td>
                    <td>{{ $linha->descricao }}{{ $marca }}{{ $modelo }}</td>
                    <td style="text-align: center;">{{ $linha->forma_aquisicao }}</td>
                    <td style="text-align: center;">{{ number_format($linha->valor, 2, ',', '.') }}</td>
                    <td style="text-align: center;">{{ \Carbon\Carbon::parse($linha->data_incorporacao)->format('d/m/Y') }}</td>
                </tr>
                <tr class="linha-item-base">
                    <td style="font-weight: bold; font-size: 11px;">SETOR</td>
                    <td colspan="2">{{ $linha->setor }}</td>
                    <td style="font-weight: bold; font-size: 11px;">SITUAÇÃO</td>
                    <td>{{ $linha->situacao }}</td>
                </tr>
            @endforeach

            <tr class="linha-subtotal">
                <td colspan="3"></td>
                <td style="text-align: right; padding-right: 15px;"><b>SubTotal</b></td>
                <td colspan="2"><b>{{ number_format($subtotal, 2, ',', '.') }}</b></td>
            </tr>

        @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse

        @if($dadosAgrupados->count() > 0)
            <tr>
                <td colspan="4" style="text-align: right; font-size: 12px; font-weight: bold; padding-top: 15px; padding-right: 15px;">TOTAL GERAL</td>
                <td colspan="2" style="font-size: 12px; font-weight: bold; padding-top: 15px;">{{ number_format($totalGeral, 2, ',', '.') }}</td>
            </tr>
        @endif
    </table>
@endsection