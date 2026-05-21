@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Bens Baixados - Bens Patrimoniais')

@section('tabela')
    <style>
        .tabela-limpa { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 11px; }
        .tabela-limpa th, .tabela-limpa td { border-left: none !important; border-right: none !important; padding: 4px; }
        .linha-cabecalho th { border-top: 1px solid #000 !important; border-bottom: 1px solid #000 !important; text-transform: uppercase; padding: 6px 4px; }
        .linha-conta td { border-bottom: 1px solid #000 !important; padding: 8px 4px; font-weight: bold; }
        .linha-subtotal td { border-bottom: 1px solid #000 !important; padding: 8px 4px; font-weight: bold; }
        .linha-item td { border-bottom: 1px solid #ccc !important; padding: 6px 4px; }
        
        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 14px; padding: 6px; text-transform: uppercase; font-family: Verdana, sans-serif; }
        .caixa-info { border: 1px solid #000 !important; padding: 4px 6px; font-family: Verdana, sans-serif; font-size: 11px; }
        
        .tabela-extrato { border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 11px; width: 50%; margin-top: 10px; }
        .tabela-extrato th, .tabela-extrato td { border: 1px solid #000 !important; padding: 4px; }
        
        @media print {
            .nova-pagina { page-break-before: always; }
        }
    </style>

    @if($agrupadoPorProcesso->isEmpty())
        <h2 style="text-align: center; font-family: Verdana; margin-top: 50px;">Nenhum bem baixado no período informado!</h2>
    @endif

    @php $isFirst = true; @endphp

    @foreach($agrupadoPorProcesso as $processo => $itensBase)
        
        @php 
            $itensPorConta = $itensBase->groupBy('conta_contabil'); 
            $info = $itensBase->first();
        @endphp

        <div class="{{ $isFirst ? '' : 'nova-pagina' }}" style="{{ $isFirst ? '' : 'margin-top: 40px;' }}">
            
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
                <tr><td class="caixa-titulo">RELATÓRIO DE BAIXA DOS BENS PATRIMONIAIS</td></tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
                <tr>
                    <td width="8%" class="caixa-info" style="font-weight: bold;">PERÍODO</td>
                    <td width="40%" class="caixa-info">
                        {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
                    </td>
                    <td width="2%" style="border: none !important;"></td>
                    <td width="50%" class="caixa-info">
                        @if(($filtros['situacao_contabil'] ?? 'Todos') !== 'Todos')
                            <span style="font-weight: bold;">SITUAÇÃO DO INVENTÁRIO:</span> {{ mb_strtoupper($filtros['situacao_contabil']) }}
                        @else
                            &nbsp;
                        @endif
                    </td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                <tr>
                    <td width="15%" class="caixa-info" style="font-weight: bold;">Processo Nº</td>
                    <td width="20%" class="caixa-info">{{ $info->processo }}</td>
                    <td width="35%" class="caixa-info"><span style="font-weight: bold;">Requisitante:</span> {{ $info->requisitante }}</td>
                    <td width="30%" class="caixa-info"><span style="font-weight: bold;">CNPJ:</span> {{ $info->cnpj }}</td>
                </tr>
                <tr>
                    <td class="caixa-info" style="font-weight: bold;">Data da Baixa</td>
                    <td colspan="3" class="caixa-info">{{ \Carbon\Carbon::parse($info->data_baixa_processo)->format('d/m/Y') }}</td>
                </tr>
                @if(!empty($info->observacao))
                <tr>
                    <td class="caixa-info" style="font-weight: bold;">Observação</td>
                    <td colspan="3" class="caixa-info">{{ $info->observacao }}</td>
                </tr>
                @endif
                @if(!empty($info->endereco))
                <tr>
                    <td class="caixa-info" style="font-weight: bold;">Endereço</td>
                    <td colspan="3" class="caixa-info">{{ $info->endereco }}</td>
                </tr>
                @endif
            </table>

            <table class="tabela-limpa">
                <tr class="linha-cabecalho">
                    <th width="5%" style="text-align: center;">ITEM</th>
                    <th width="10%" style="text-align: center;">PATRIM.</th>
                    <th width="40%" style="text-align: left;">DESCRIÇÃO DO BEM/MARCA/MODELO</th>
                    <th width="10%" style="text-align: right;">VALOR BRUTO</th>
                    <th width="12%" style="text-align: right;">VALOR LIQ. CONT.</th>
                    <th width="11%" style="text-align: right;">DEP. ACUMUL.</th>
                    <th width="12%" style="text-align: center;">TIPO DA BAIXA</th>
                </tr>

                @php 
                    $seq = 1; 
                    $totBrutoProc = 0; $totLiqProc = 0; $totDepProc = 0;
                    $extrato = []; // Para guardar o resumo no fim do processo
                @endphp

                @foreach($itensPorConta as $conta => $itens)
                    <tr class="linha-conta"><td colspan="7">{{ $conta }}</td></tr>
                    
                    @php $subBruto = 0; $subLiq = 0; $subDep = 0; @endphp

                    @foreach($itens as $linha)
                        @php
                            $marca = $linha->marca ? '/' . $linha->marca : '';
                            $modelo = $linha->modelo ? '/' . $linha->modelo : '';
                            
                            $subBruto += $linha->valor_bruto;
                            $subLiq += $linha->valor_liquido;
                            $subDep += $linha->depreciacao_acumulada;
                        @endphp
                        <tr class="linha-item">
                            <td style="text-align: center;">{{ $seq++ }}</td>
                            <td style="text-align: center;">{{ number_format($linha->patrimonio, 0, ',', '.') }}</td>
                            <td>{{ mb_strtoupper($linha->descricao) }}{{ mb_strtoupper($marca) }}{{ mb_strtoupper($modelo) }}</td>
                            <td style="text-align: right;">{{ number_format($linha->valor_bruto, 2, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($linha->valor_liquido, 2, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($linha->depreciacao_acumulada, 2, ',', '.') }}</td>
                            <td style="text-align: center;">{{ mb_strtoupper($linha->situacao) }}</td>
                        </tr>
                    @endforeach

                    @php
                        // Salva para o extrato final
                        $extrato[$conta] = ['bruto' => $subBruto, 'liq' => $subLiq, 'dep' => $subDep];
                        
                        $totBrutoProc += $subBruto;
                        $totLiqProc += $subLiq;
                        $totDepProc += $subDep;
                    @endphp

                    <tr class="linha-subtotal">
                        <td colspan="2"></td>
                        <td style="text-align: right;">SubTotal</td>
                        <td style="text-align: right;">{{ number_format($subBruto, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($subLiq, 2, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($subDep, 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                @endforeach

                <tr class="linha-subtotal">
                    <td colspan="3" style="text-align: right; font-size: 12px;">TOTAL GERAL</td>
                    <td style="text-align: right; font-size: 12px;">{{ number_format($totBrutoProc, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-size: 12px;">{{ number_format($totLiqProc, 2, ',', '.') }}</td>
                    <td style="text-align: right; font-size: 12px;">{{ number_format($totDepProc, 2, ',', '.') }}</td>
                    <td></td>
                </tr>
            </table>

            <h3 style="font-family: Verdana; font-size: 13px; margin-top: 20px; margin-bottom: 5px;">Extrato da Conta Contábil</h3>
            <table class="tabela-extrato">
                <tr><td colspan="4" style="background-color: #f5f5f5;">Processo Nº: <strong>{{ $info->processo }}</strong></td></tr>
                <tr>
                    <td align="center" style="font-weight: bold;">Conta</td>
                    <td align="center" style="font-weight: bold;">Valor Bruto</td>
                    <td align="center" style="font-weight: bold;">Valor Liq.</td>
                    <td align="center" style="font-weight: bold;">Dep. Acumul.</td>
                </tr>
                @foreach($extrato as $cc => $valores)
                <tr>
                    <td>{{ $cc }}:</td>
                    <td align="right"><strong>{{ number_format($valores['bruto'], 2, ',', '.') }}</strong></td>
                    <td align="right"><strong>{{ number_format($valores['liq'], 2, ',', '.') }}</strong></td>
                    <td align="right"><strong>{{ number_format($valores['dep'], 2, ',', '.') }}</strong></td>
                </tr>
                @endforeach
                <tr>
                    <td>Total:</td>
                    <td align="right"><strong>{{ number_format($totBrutoProc, 2, ',', '.') }}</strong></td>
                    <td align="right"><strong>{{ number_format($totLiqProc, 2, ',', '.') }}</strong></td>
                    <td align="right"><strong>{{ number_format($totDepProc, 2, ',', '.') }}</strong></td>
                </tr>
            </table>

        </div>
        @php $isFirst = false; @endphp
    @endforeach

    @if(!$resumoFinal->isEmpty())
        <div class="nova-pagina" style="margin-top: 40px; text-align: center;">
            <h2 style="font-family: Verdana; font-size: 16px;">DEPRECIAÇÃO ACUMULADA DAS SAÍDAS</h2>
            <center>
                <table class="tabela-limpa" style="width: 700px; text-align: left;">
                    <tr class="linha-cabecalho">
                        <th width="20%">CONTA CONTÁBIL</th>
                        <th width="20%">PROCESSO</th>
                        <th width="20%" style="text-align: right;">VALOR BRUTO</th>
                        <th width="20%" style="text-align: right;">VALOR LÍQUIDO</th>
                        <th width="20%" style="text-align: right;">DEPRECIAÇÃO<br>ACUMULADA</th>
                    </tr>
                    @php $gBruto = 0; $gLiq = 0; $gDep = 0; @endphp
                    @foreach($resumoFinal as $resumo)
                        @php
                            $gBruto += $resumo->valor_bruto;
                            $gLiq += $resumo->valor_liquido;
                            $gDep += $resumo->depreciacao_acumulada;
                        @endphp
                        <tr class="linha-item">
                            <td>{{ $resumo->conta_contabil }}</td>
                            <td>{{ $resumo->processo }}</td>
                            <td style="text-align: right;">{{ number_format($resumo->valor_bruto, 2, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($resumo->valor_liquido, 2, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($resumo->depreciacao_acumulada, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="linha-subtotal">
                        <td colspan="2" style="text-align: right; font-size: 12px;">TOTAL GERAL</td>
                        <td style="text-align: right; font-size: 12px;">{{ number_format($gBruto, 2, ',', '.') }}</td>
                        <td style="text-align: right; font-size: 12px;">{{ number_format($gLiq, 2, ',', '.') }}</td>
                        <td style="text-align: right; font-size: 12px;">{{ number_format($gDep, 2, ',', '.') }}</td>
                    </tr>
                </table>
            </center>
        </div>
    @endif
@endsection