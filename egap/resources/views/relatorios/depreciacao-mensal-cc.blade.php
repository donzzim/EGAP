@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Depreciação Mensal por Centro de Custos - Bens Patrimoniais')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-top: 5px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 4px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; }

        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 14px; padding: 8px; text-transform: uppercase; font-family: Verdana, sans-serif; }
        .caixa-info { border: 1px solid #000 !important; padding: 4px 6px; font-family: Verdana, sans-serif; font-size: 11px; }

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

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
                <tr><td class="caixa-titulo">RELATÓRIO DE DEPRECIAÇÃO MENSAL POR CENTRO DE CUSTOS</td></tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                <tr>
                    <td width="6%" class="caixa-info" style="font-weight: bold;">PERÍODO</td>
                    <td width="94%" class="caixa-info">
                        {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
                    </td>
                </tr>
            </table>

            <table class="tabela-grid">
                <tr class="linha-cabecalho">
                    <td colspan="4" style="text-align: left; font-weight: bold; border-right: none !important;">CENTRO DE CUSTO: {{ mb_strtoupper($tituloCentroCusto) }}</td>
                    <td colspan="6" style="text-align: center; font-weight: bold;">RESUMO</td>
                </tr>
                <tr class="linha-cabecalho">
                    <th colspan="4" style="border-top: none !important; border-left: none !important; border-right: none !important;">&nbsp;</th>
                    <th colspan="6">VALORES DA DEPRECIAÇÃO</th>
                </tr>
                <tr class="linha-cabecalho">
                    <th width="7%">CONTA CONTÁBIL</th>
                    <th width="6%">ITEM PATRIMONIAL</th>
                    <th width="8%">CÓD. NAT. DESPESA</th>
                    <th width="25%">DESCRIÇÃO P/ SUBITEM CONTÁBIL</th>
                    <th width="9%">VALOR</th>
                    <th width="9%">VALOR RESIDUAL</th>
                    <th width="9%">DEPRECIAÇÃO MENSAL</th>
                    <th width="9%">DEPRECIAÇÃO ACUMULADA</th>
                    <th width="9%">VALOR LÍQUIDO CONTÁBIL</th>
                    <th width="9%">DEPRECIAÇÃO ACUMULADA DAS SAÍDAS</th>
                </tr>

                @php
                    $tVal = 0; $tRes = 0; $tDepM = 0; $tDepA = 0; $tLiq = 0; $tDepS = 0;
                @endphp

                @foreach ($itens as $linha)
                    @php
                        $tVal += $linha->valor_base;
                        $tRes += $linha->valor_residual;
                        $tDepM += $linha->dep_mensal;
                        $tDepA += $linha->dep_acumulada;
                        $tLiq += $linha->valor_liquido;
                        $tDepS += $linha->dep_saidas;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $linha->conta_contabil }}</td>
                        <td style="text-align: left;">{{ $linha->item_patrimonial }}</td>
                        <td style="text-align: left;">{{ $linha->cod_nat_despesa }}</td>
                        <td style="text-align: left;">{{ $linha->descricao }}</td>
                        <td style="text-align: right;">{{ number_format($linha->valor_base, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->valor_residual, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->dep_mensal, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->dep_acumulada, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->valor_liquido, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->dep_saidas, 2, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tVal, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tRes, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tDepM, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tDepA, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tLiq, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tDepS, 2, ',', '.') }}</td>
                </tr>
            </table>

            @if(!$loop->last)
                <div style="margin-top: 40px; page-break-inside: avoid;">
                    <table style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 5px;">
                        <tr>
                            <td width="10%"><img src="{{ asset('images/brasao-tjes.png') }}" width="60" alt="Brasão"></td>
                            <td width="70%" style="padding-left: 10px;">
                                <div style="font-weight: bold; font-size: 14px;">TRIBUNAL DE JUSTIÇA DO ESTADO ES</div><br>
                                <div style="font-weight: bold; font-size: 12px;">Relatório de Depreciação Mensal por Centro de Custos - Bens Patrimoniais</div>
                            </td>
                            <td width="20%" style="text-align: right; vertical-align: top; font-weight: bold; font-size: 12px;">
                                {{ $data_emissao }}<br><br>Setor de Patrimônio
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

        </div>
        @php $isFirst = false; @endphp
    @endforeach

@endsection
