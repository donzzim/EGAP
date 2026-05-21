@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Relatório da Diferença Contábil')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 11px; margin-bottom: 30px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 6px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; font-size: 11px; }
        
        .texto-resumo { font-family: Verdana, sans-serif; font-size: 13px; margin-bottom: 10px; }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 25px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório da Diferença Contábil dos Bens Patrimoniais com Valor de Aquisição Inferior ao de Reavaliação</td>
            <td width="50%" align="right">Seção de Patrimônio</td>
        </tr>
    </table>

    @if($dados->isEmpty())
        <h2 style="text-align: center; font-family: Verdana; margin-top: 50px;">Nenhum registro encontrado.</h2>
    @endif

    @if($dados->isNotEmpty())
        <table class="tabela-grid">
            <tr class="linha-cabecalho">
                <th width="35%" style="text-align: left;">SITUAÇÃO</th>
                <th width="15%">QUANTIDADE</th>
                <th width="16%">VALOR AQUISIÇÃO</th>
                <th width="17%">VALOR REAVALIAÇÃO</th>
                <th width="17%">DIFERENÇA CONTÁBIL</th>
            </tr>

            @php
                $tQtde = 0; $tAquisicao = 0; $tReavaliacao = 0; $tDiferenca = 0;
            @endphp

            @foreach ($dados as $linha)
                @php
                    $tQtde += $linha->qtde;
                    $tAquisicao += $linha->aquisicao;
                    $tReavaliacao += $linha->reavaliacao;
                    $tDiferenca += $linha->diferenca;
                @endphp
                <tr>
                    <td style="text-align: left; font-weight: bold;">{{ mb_strtoupper($linha->situacao) }}</td>
                    <td style="text-align: right;">{{ number_format($linha->qtde, 0, ',', '.') }}</td>
                    <td style="text-align: right;">R$ {{ number_format($linha->aquisicao, 2, ',', '.') }}</td>
                    <td style="text-align: right;">R$ {{ number_format($linha->reavaliacao, 2, ',', '.') }}</td>
                    <td style="text-align: right;">R$ {{ number_format($linha->diferenca, 2, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr>
                <td style="text-align: left; font-weight: bold;">TOTAL</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($tQtde, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">R$ {{ number_format($tAquisicao, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">R$ {{ number_format($tReavaliacao, 2, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">R$ {{ number_format($tDiferenca, 2, ',', '.') }}</td>
            </tr>
        </table>
    @endif

    <div class="texto-resumo">
        Total Geral de Bens Móveis: <b>{{ number_format($totalAtivos, 0, ',', '.') }}</b>
    </div>
    <div class="texto-resumo">
        Total de Bens Móveis com Valor de Aquisição Inferior ao de Reavaliação: <b>{{ number_format($dados->sum('qtde'), 0, ',', '.') }}</b>
    </div>
    <div class="texto-resumo">
        Valor Total da Diferença Contábil ainda não Baixada na Conta Outros Bens Móveis: <b>R$ {{ number_format($dados->sum('diferenca'), 2, ',', '.') }}</b>
    </div>

@endsection