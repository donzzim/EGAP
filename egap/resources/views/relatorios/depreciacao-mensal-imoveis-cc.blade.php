@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Depreciação Mensal Imóveis- Bens Patrimoniais')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-top: 10px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 5px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; }

        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 14px; padding: 8px; text-transform: uppercase; font-family: Verdana, sans-serif; }

        @media print {
            .nova-pagina { page-break-before: always; }
        }
    </style>

    @if($dadosAgrupados->isEmpty())
        <h2 style="text-align: center; font-family: Verdana; margin-top: 50px;">Nenhum registro encontrado para os filtros informados.</h2>
    @endif

    @php $isFirst = true; @endphp

    @foreach($dadosAgrupados as $tituloCentroCusto => $itens)

        <div class="{{ $isFirst ? '' : 'nova-pagina' }}" style="{{ $isFirst ? '' : 'margin-top: 40px;' }}">

            <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
                <tr>
                    <td width="50%" align="left">Relatório de Depreciação Mensal Imóveis- Bens Patrimoniais</td>
                    <td width="50%" align="right">Seção de Patrimônio</td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                <tr><td class="caixa-titulo">RELATÓRIO DE DEPRECIAÇÃO MENSAL IMÓVEIS</td></tr>
            </table>

            @isset($filtros['data_inicio'], $filtros['data_termino'])
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                    <tr>
                        <td width="8%" style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 11px; font-weight: bold; padding: 4px 6px;">PERÍODO</td>
                        <td width="92%" style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 11px; padding: 4px 6px;">
                            {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
                        </td>
                    </tr>
                </table>
            @endisset

            <table class="tabela-grid">
                <tr class="linha-cabecalho">
                    <td colspan="4" style="text-align: left; font-weight: bold; border-right: none !important;">CENTRO DE CUSTO: {{ mb_strtoupper($tituloCentroCusto) }}</td>
                    <td colspan="5" style="text-align: center; font-weight: bold;">RESUMO</td>
                </tr>
                <tr class="linha-cabecalho">
                    <th colspan="4" style="border-top: none !important; border-left: none !important; border-right: none !important;">&nbsp;</th>
                    <th colspan="5">VALORES DA DEPRECIAÇÃO</th>
                </tr>
                <tr class="linha-cabecalho">
                    <th width="10%">CONTA CONTÁBIL</th>
                    <th width="10%">ITEM PATRIMONIAL</th>
                    <th width="15%">DESCRIÇÃO P/ SUBITEM CONTÁBIL</th>
                    <th width="20%">IMÓVEL</th>
                    <th width="9%">VALOR ATUAL</th>
                    <th width="9%">VALOR RESIDUAL</th>
                    <th width="9%">DEPRECIAÇÃO MENSAL</th>
                    <th width="9%">DEPRECIAÇÃO ACUMULADA</th>
                    <th width="9%">VALOR LÍQUIDO CONTÁBIL</th>
                </tr>

                @php
                    $tValAtual = 0; $tValResidual = 0; $tDepMensal = 0; $tDepAcumulada = 0; $tValLiquido = 0;
                @endphp

                @foreach ($itens as $linha)
                    @php
                        $tValAtual += $linha->valor_atual;
                        $tValResidual += $linha->valor_residual;
                        $tDepMensal += $linha->depreciacao_mensal;
                        $tDepAcumulada += $linha->depreciacao_acumulada;
                        $tValLiquido += $linha->valor_liquido;

                        // Fake Item Patrimonial para o layout, o legado forçava isso baseado na subquery que removemos.
                        $itemPatrimonial = '3305';
                        if(str_contains($linha->conta_contabil, '1.2.3.2.1.01.02')) $itemPatrimonial = '3306';
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $linha->conta_contabil }}</td>
                        <td style="text-align: left;">{{ $itemPatrimonial }}</td>
                        <td style="text-align: left;">{{ $linha->descricao_subitem }}</td>
                        <td style="text-align: left;">
                            @if($linha->inscricao_generica)
                                {{ $linha->inscricao_generica }}<br>
                            @endif
                            @if($linha->num_registro)
                                {{ $linha->num_registro }}<br>
                            @endif
                            {{ $linha->descricao_imovel }}
                        </td>
                        <td style="text-align: right;">{{ number_format($linha->valor_atual, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->valor_residual, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->depreciacao_mensal, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->depreciacao_acumulada, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->valor_liquido, 2, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tValAtual, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tValResidual, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tDepMensal, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tDepAcumulada, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tValLiquido, 2, ',', '.') }}</td>
                </tr>
            </table>

            @if(!$loop->last)
                <div style="margin-top: 40px; page-break-inside: avoid;">
                    <table style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 5px;">
                        <tr>
                            <td width="10%"><img src="{{ asset('images/brasao-tjes.png') }}" width="60" alt="Brasão"></td>
                            <td width="70%" style="padding-left: 10px;">
                                <div style="font-weight: bold; font-size: 14px;">TRIBUNAL DE JUSTIÇA DO ESTADO ES</div><br>
                            </td>
                            <td width="20%" style="text-align: right; vertical-align: top; font-weight: bold; font-size: 12px;">
                                {{ $data_emissao }}
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

        </div>
        @php $isFirst = false; @endphp
    @endforeach

@endsection
