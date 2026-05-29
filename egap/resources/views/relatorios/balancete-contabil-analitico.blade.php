@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Balancete Contábil')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-bottom: 20px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 4px 6px; }
        .linha-cabecalho th { font-weight: bold; text-align: center; font-size: 10px; text-transform: uppercase; }

        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 16px; padding: 6px; font-family: Verdana, sans-serif; margin-bottom: 15px;}

        @media print {
            .nova-pagina { page-break-before: always; }
        }
    </style>

    @if($dadosAgrupados->isEmpty())
        <h2 style="text-align: center; font-family: Verdana; margin-top: 50px;">Nenhum registro encontrado para os filtros informados.</h2>
    @endif

    @php $isFirst = true; @endphp

    @foreach($dadosAgrupados as $tipoMaterial => $itens)

        <div class="{{ $isFirst ? '' : 'nova-pagina' }}" style="{{ $isFirst ? '' : 'margin-top: 40px;' }}">

            <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
                <tr>
                    <td width="50%" align="left">Relatório de Balancete Contábil</td>
                    <td width="50%" align="right">Seção de Material de Consumo</td>
                </tr>
            </table>

            <div class="caixa-titulo">
                Balancete Contábil
            </div>

            @isset($filtros['data_inicio'], $filtros['data_termino'])
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                    <tr>
                        <td width="6%" style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 11px; font-weight: bold; padding: 4px 6px; text-transform: uppercase;">PERÍODO</td>
                        <td width="94%" style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 11px; padding: 4px 6px;">
                            {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
                        </td>
                    </tr>
                </table>
            @endisset

            <table class="tabela-grid">
                <tr class="linha-cabecalho">
                    <th rowspan="2" width="3%">Item</th>
                    <th rowspan="2" width="30%">Descrição</th>
                    <th rowspan="2" width="7%">Tipo</th>
                    <th colspan="2">Saldo Anterior</th>
                    <th colspan="2">Entrada</th>
                    <th colspan="2">Saída</th>
                    <th colspan="2">Saldo Atual</th>
                </tr>
                <tr class="linha-cabecalho">
                    <th width="7%">Qtde</th>
                    <th width="8%">Valor</th>
                    <th width="7%">Qtde</th>
                    <th width="8%">Valor</th>
                    <th width="7%">Qtde</th>
                    <th width="8%">Valor</th>
                    <th width="7%">Qtde</th>
                    <th width="8%">Valor</th>
                </tr>

                @php
                    $seq = 1;
                    $tSaQtd = 0; $tSaValor = 0;
                    $tEntQtd = 0; $tEntValor = 0;
                    $tSaiQtd = 0; $tSaiValor = 0;
                    $tAtQtd = 0; $tAtValor = 0;
                @endphp

                @foreach($itens as $item)
                    @php
                        $tSaQtd += $item->sa_qtd; $tSaValor += $item->sa_valor;
                        $tEntQtd += $item->ent_qtd; $tEntValor += $item->ent_valor;
                        $tSaiQtd += $item->sai_qtd; $tSaiValor += $item->sai_valor;
                        $tAtQtd += $item->atual_qtd; $tAtValor += $item->atual_valor;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $seq++ }}</td>
                        <td>{{ $item->descricao_detalhada }} - {{ $item->elemento }}</td>
                        <td style="text-align: center;">{!! str_replace(' ', '<br/>', $item->tipo_desc) !!}</td>

                        <td style="text-align: right;">{{ number_format($item->sa_qtd, 0, '', '.') }}</td>
                        <td style="text-align: right;">R$ {{ number_format($item->sa_valor, 2, ',', '.') }}</td>

                        <td style="text-align: right;">{{ number_format($item->ent_qtd, 0, '', '.') }}</td>
                        <td style="text-align: right;">R$ {{ number_format($item->ent_valor, 2, ',', '.') }}</td>

                        <td style="text-align: right;">{{ number_format($item->sai_qtd, 0, '', '.') }}</td>
                        <td style="text-align: right;">R$ {{ number_format($item->sai_valor, 2, ',', '.') }}</td>

                        <td style="text-align: right;">{{ number_format($item->atual_qtd, 0, '', '.') }}</td>
                        <td style="text-align: right;">R$ {{ number_format($item->atual_valor, 2, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold; text-transform: uppercase;">TOTAL</td>
                    <td></td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSaQtd, 0, '', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">R$ {{ number_format($tSaValor, 2, ',', '.') }}</td>

                    <td style="text-align: right; font-weight: bold;">{{ number_format($tEntQtd, 0, '', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">R$ {{ number_format($tEntValor, 2, ',', '.') }}</td>

                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSaiQtd, 0, '', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">R$ {{ number_format($tSaiValor, 2, ',', '.') }}</td>

                    <td style="text-align: right; font-weight: bold;">{{ number_format($tAtQtd, 0, '', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">R$ {{ number_format($tAtValor, 2, ',', '.') }}</td>
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
