@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Bens Patrimoniais')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-bottom: 20px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 4px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; }

        .conta-titulo { font-family: Verdana, sans-serif; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px; border-bottom: 2px solid #000;">
        <tr>
            <td width="50%" align="left">Relatório de Bens Patrimoniais</td>
            <td width="50%" align="right">Seção de Patrimônio</td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <td style="text-align: center; font-weight: bold; font-size: 16px;">
                INVENTÁRIO ANUAL {{ $ano }} - BENS INTANGÍVEIS
            </td>
        </tr>
    </table>

    @if($dadosAgrupados->isEmpty())
        <h2 style="text-align: center; font-family: Verdana; margin-top: 50px;">Nenhum registro encontrado para os filtros informados.</h2>
    @endif

    @foreach($dadosAgrupados as $tituloConta => $itens)

        <div class="conta-titulo">
            CONTA CONTÁBIL: {{ $tituloConta }}
        </div>

        <table class="tabela-grid">
            <tr class="linha-cabecalho">
                <th width="4%">ITEM</th>
                <th width="12%">INSCRIÇÃO<br>GENÉRICA</th>
                <th width="30%">DESCRIÇÃO</th>
                <th width="5%">QTDE</th>
                <th width="10%">DATA<br>AQUISIÇÃO</th>
                <th width="10%">VALOR<br>AQUISIÇÃO</th>
                <th width="10%">AMORTIZAÇÃO<br>ACUMULADA</th>
                <th width="10%">VALOR<br>CONTÁBIL</th>
                <th width="9%">VIDA ÚTIL<br>REMANESCENTE</th>
            </tr>

            @php
                $seq = 1;
                $tQtde = 0;
                $tValAquisicao = 0;
                $tAmortAcumulada = 0;
                $tValContabil = 0;
            @endphp

            @foreach ($itens as $linha)
                @php
                    $tQtde += $linha->quantidade;
                    $tValAquisicao += $linha->valor_aquisicao;
                    $tAmortAcumulada += $linha->amortizacao_acumulada;
                    $tValContabil += $linha->valor_liquido_contabil;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $seq++ }}</td>
                    <td style="text-align: left;">{{ $linha->inscricao_generica }}</td>
                    <td style="text-align: left;">{{ $linha->descricao }}</td>
                    <td style="text-align: center;">{{ $linha->quantidade }}</td>
                    <td style="text-align: center;">
                        {{ ($linha->data_aquisicao && $linha->data_aquisicao != '0000-00-00 00:00:00' && $linha->data_aquisicao != '0000-00-00') ? \Carbon\Carbon::parse($linha->data_aquisicao)->format('d/m/Y') : '' }}
                    </td>
                    <td style="text-align: right;">{{ number_format($linha->valor_aquisicao, 2, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($linha->amortizacao_acumulada, 2, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($linha->valor_liquido_contabil, 2, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $linha->vida_util_remanescente }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: center; font-weight: bold;">{{ $tQtde }}</td>
                <td></td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tValAquisicao, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tAmortAcumulada, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tValContabil, 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </table>

    @endforeach

@endsection
