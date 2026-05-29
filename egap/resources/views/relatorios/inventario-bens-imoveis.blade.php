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
                INVENTÁRIO ANUAL {{ $ano }} - BENS IMÓVEIS
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
                <th width="3%">ITEM</th>
                <th width="8%">NÚM. REGISTRO</th>
                <th width="8%">INSC. GENÉRICA</th>
                <th width="8%">INSC. IMOBILIÁRIA</th>
                <th width="20%">DESCRIÇÃO DO BEM</th>
                <th width="3%">QTDE</th>
                <th width="8%">DATA<br>AQUISIÇÃO</th>
                <th width="10%">VALOR HISTÓRICO<br>OU REAVALIADO</th>
                <th width="10%">DEPRECIAÇÃO<br>ACUMULADA</th>
                <th width="10%">VALOR<br>CONTÁBIL</th>
                <th width="12%">LOCALIZAÇÃO<br>ATUAL</th>
            </tr>

            @php
                $seq = 1;
                $tValHistorico = 0;
                $tDepAcumulada = 0;
                $tValContabil = 0;
            @endphp

            @foreach ($itens as $linha)
                @php
                    $tValHistorico += $linha->valor_historico;
                    $tDepAcumulada += $linha->depreciacao;
                    $tValContabil += $linha->valor_contabil;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $seq++ }}</td>
                    <td style="text-align: left;">{{ $linha->patrimonio }}</td>
                    <td style="text-align: left;">{{ $linha->inscricao_generica }}</td>
                    <td style="text-align: left;">{{ $linha->inscricao_imobiliaria }}</td>
                    <td style="text-align: left;">{{ $linha->descricao }}</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: center;">{{ $linha->data_aquisicao && $linha->data_aquisicao != '0000-00-00 00:00:00' ? \Carbon\Carbon::parse($linha->data_aquisicao)->format('d/m/Y') : '' }}</td>
                    <td style="text-align: right;">{{ number_format($linha->valor_historico, 2, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($linha->depreciacao, 2, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($linha->valor_contabil, 2, ',', '.') }}</td>
                    <td style="text-align: left;">{{ $linha->localizacao }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="5" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: center; font-weight: bold;">{{ $itens->count() }}</td>
                <td></td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tValHistorico, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tDepAcumulada, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tValContabil, 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </table>

    @endforeach

@endsection
