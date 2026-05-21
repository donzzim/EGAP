@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Resumo do Inventário do Almoxarifado - Material de Consumo')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-bottom: 20px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 4px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; }
        
        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 14px; padding: 6px; font-family: Verdana, sans-serif; margin-bottom: 15px;}
        
        @media print {
            .nova-pagina { page-break-before: always; }
        }
    </style>

    @php 
        $porCC = $dados->groupBy(fn($i) => $i->cc_codigo . ' ' . $i->cc_descricao); 
        $isFirst = true;
    @endphp

    @if($dados->isEmpty())
        <h2 style="text-align: center; font-family: Verdana; margin-top: 50px;">Nenhum registro encontrado para os filtros informados.</h2>
    @endif

    @foreach($porCC as $nomeCC => $itens)
        <div class="{{ $isFirst ? '' : 'nova-pagina' }}" style="{{ $isFirst ? '' : 'margin-top: 40px;' }}">
            
            <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
                <tr>
                    <td width="60%" align="left">Relatório do TCE - Tabela 14 - Resumo do Inventário do Almoxarifado - Material de Consumo</td>
                    <td width="40%" align="right">Seção de Material de Consumo</td>
                </tr>
            </table>

            <div class="caixa-titulo">
                RELATÓRIO DE INVENTÁRIO DO ALMOXARIFADO
            </div>

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
                    <td colspan="4" style="text-align: left; font-weight: bold;">CENTRO DE CUSTO: {{ mb_strtoupper($nomeCC) }}</td>
                    <td colspan="5" style="font-weight: bold;">RESUMO DO INVENTÁRIO DO ALMOXARIFADO - MATERIAL DE CONSUMO</td>
                </tr>
                <tr class="linha-cabecalho">
                    <th colspan="4" style="border-top: none !important; border-left: none !important;">&nbsp;</th>
                    <th colspan="5">VALORES DO INVENTÁRIO FÍSICO</th>
                </tr>
                <tr class="linha-cabecalho">
                    <th width="10%">CONTA<br>CONTÁBIL</th>
                    <th width="12%">CÓD. NAT.<br>DESPESA</th>
                    <th width="8%">ITEM<br>PATRIMONIAL</th>
                    <th width="30%">DESCRIÇÃO P/ SUBITEM CONTÁBIL</th>
                    <th width="8%">SALDO ANTERIOR</th>
                    <th width="8%">ENTRADAS</th>
                    <th width="8%">SAÍDAS</th>
                    <th width="8%">SALDO ATUAL</th>
                    <th width="8%">SAÍDAS ACUMULADAS</th>
                </tr>

                @php 
                    $tSA = 0; $tEnt = 0; $tSai = 0; $tSAt = 0; $tSAcum = 0;
                @endphp

                @foreach ($itens as $linha)
                    @php 
                        $tSA += $linha->sa; $tEnt += $linha->entradas; $tSai += $linha->saidas; $tSAt += $linha->saldo_atual; $tSAcum += $linha->saidas_acum;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $linha->conta_contabil }}</td>
                        <td>{{ $linha->produto }}</td>
                        <td>{{ $linha->item_patrimonial }}</td>
                        <td>{{ $linha->descricao }}</td>
                        <td style="text-align: right;">{{ number_format($linha->sa, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->entradas, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->saidas, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->saldo_atual, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($linha->saidas_acum, 2, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSA, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tEnt, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSai, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSAt, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSAcum, 2, ',', '.') }}</td>
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
                            <td width="20%" style="text-align: right; vertical-align: top; font-weight: bold; font-size: 12px;">{{ $data_emissao }}</td>
                        </tr>
                    </table>
                </div>
            @endif
        </div>
        @php $isFirst = false; @endphp
    @endforeach

    @if($dados->count() > 0)
        <div class="nova-pagina" style="margin-top: 40px;">
            <div class="caixa-titulo">RELATÓRIO DE INVENTÁRIO DO ALMOXARIFADO POR CENTRO DE CUSTOS</div>
            <table class="tabela-grid">
                <tr class="linha-cabecalho">
                    <th width="10%">CÓDIGO</th>
                    <th width="42%">CENTRO DE CUSTO</th>
                    <th width="10%">SALDO ANTERIOR</th>
                    <th width="9%">ENTRADAS</th>
                    <th width="9%">SAÍDAS</th>
                    <th width="10%">SALDO ATUAL</th>
                    <th width="10%">SAÍDAS ACUMULADAS</th>
                </tr>
                @php $tSA = 0; $tEnt = 0; $tSai = 0; $tSAt = 0; $tSAcum = 0; @endphp
                @foreach ($porCC as $ccNome => $itens)
                    @php 
                        $s1 = $itens->sum('sa'); $s2 = $itens->sum('entradas'); $s3 = $itens->sum('saidas'); $s4 = $itens->sum('saldo_atual'); $s5 = $itens->sum('saidas_acum');
                        $tSA += $s1; $tEnt += $s2; $tSai += $s3; $tSAt += $s4; $tSAcum += $s5;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $itens->first()->cc_codigo }}</td>
                        <td>{{ $itens->first()->cc_descricao }}</td>
                        <td style="text-align: right;">{{ number_format($s1, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($s2, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($s3, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($s4, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($s5, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">TOTAL</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSA, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tEnt, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSai, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSAt, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSAcum, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="nova-pagina" style="margin-top: 40px;">
            <div class="caixa-titulo">RELATÓRIO DE INVENTÁRIO DO ALMOXARIFADO POR CONTA CONTÁBIL</div>
            <table class="tabela-grid">
                <tr class="linha-cabecalho">
                    <th width="10%">CÓDIGO</th>
                    <th width="10%">PRODUTO</th>
                    <th width="32%">DESCRIÇÃO</th>
                    <th width="10%">SALDO ANTERIOR</th>
                    <th width="9%">ENTRADAS</th>
                    <th width="9%">SAÍDAS</th>
                    <th width="10%">SALDO ATUAL</th>
                    <th width="10%">SAÍDAS ACUMULADAS</th>
                </tr>
                @php $tSA = 0; $tEnt = 0; $tSai = 0; $tSAt = 0; $tSAcum = 0; @endphp
                @foreach ($dados->groupBy(fn($i) => $i->conta_contabil . '|' . $i->produto) as $grupo => $itens)
                    @php 
                        $s1 = $itens->sum('sa'); $s2 = $itens->sum('entradas'); $s3 = $itens->sum('saidas'); $s4 = $itens->sum('saldo_atual'); $s5 = $itens->sum('saidas_acum');
                        $tSA += $s1; $tEnt += $s2; $tSai += $s3; $tSAt += $s4; $tSAcum += $s5;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $itens->first()->conta_contabil }}</td>
                        <td>{{ $itens->first()->produto }}</td>
                        <td>{{ $itens->first()->descricao }}</td>
                        <td style="text-align: right;">{{ number_format($s1, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($s2, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($s3, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($s4, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($s5, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSA, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tEnt, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSai, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSAt, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSAcum, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="nova-pagina" style="margin-top: 40px;">
            <div class="caixa-titulo">RELATÓRIO DE INVENTÁRIO DO ALMOXARIFADO POR ITEM PATRIMONIAL</div>
            <table class="tabela-grid">
                <tr class="linha-cabecalho">
                    <th width="40%">ITEM PATRIMONIAL</th>
                    <th width="12%">SALDO ANTERIOR</th>
                    <th width="12%">ENTRADAS</th>
                    <th width="12%">SAÍDAS</th>
                    <th width="12%">SALDO ATUAL</th>
                    <th width="12%">SAÍDAS ACUMULADAS</th>
                </tr>
                @php $tSA = 0; $tEnt = 0; $tSai = 0; $tSAt = 0; $tSAcum = 0; @endphp
                @foreach ($dados->groupBy('item_patrimonial') as $itemPat => $itens)
                    @php 
                        $s1 = $itens->sum('sa'); $s2 = $itens->sum('entradas'); $s3 = $itens->sum('saidas'); $s4 = $itens->sum('saldo_atual'); $s5 = $itens->sum('saidas_acum');
                        $tSA += $s1; $tEnt += $s2; $tSai += $s3; $tSAt += $s4; $tSAcum += $s5;
                    @endphp
                    <tr>
                        <td>{{ $itemPat }}</td>
                        <td style="text-align: right;">{{ number_format($s1, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($s2, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($s3, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($s4, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($s5, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="text-align: right; font-weight: bold;">TOTAL</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSA, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tEnt, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSai, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSAt, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tSAcum, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    @endif
@endsection