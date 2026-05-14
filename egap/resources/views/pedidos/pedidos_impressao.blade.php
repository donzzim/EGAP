<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Impressão do Pedido {{ str_pad($pedido->id, 7, '0', STR_PAD_LEFT) }}/{{ optional($pedido->date_time)->format('Y') }}</title>
    <style>
        :root {
            color-scheme: light;
            --page-bg: #ffffff;
            --page-fg: #000000;
            --page-border: #000000;
        }

        body {
            font-family: Verdana, sans-serif;
            font-size: 10px;
            padding: 5px;
            background: var(--page-bg);
            color: var(--page-fg);
        }

        .titulo {
            font-size: 14px;
            font-weight: bold;
        }

        .titulo1 {
            font-size: 12px;
            font-weight: bold;
        }

        .cabecalho {
            border: 1px solid var(--page-border);
        }

        .titulo-coluna {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .registro-coluna {
            font-size: 10px;
        }

        .titulo-termo {
            font-size: 12px;
            font-weight: bold;
        }

        .texto {
            font-size: 11px;
            padding: 0 5px;
        }

        .texto-assinatura {
            font-size: 10px;
        }

        .quebra-pagina {
            page-break-after: always;
        }

        table {
            border-collapse: collapse;
        }

        hr {
            border-top-color: var(--page-border);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
                --page-bg: #111827;
                --page-fg: #f3f4f6;
                --page-border: #9ca3af;
            }
        }

        @media print {
            :root {
                color-scheme: light;
                --page-bg: #ffffff;
                --page-fg: #000000;
                --page-border: #000000;
            }

            @page {
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
@php
    $dataAtual = now()->format('d/m/Y');
    $numero = str_pad($pedido->id, 7, '0', STR_PAD_LEFT);
    $ano = optional($pedido->date_time)->format('Y');
    $dataPedido = optional($pedido->date_time)->format('d/m/Y H:i:s');
    $solicitante = $pedido->solicitante_get->name ?? '-';
    $setor = $pedido->setor_get->Setor ?? '-';
    $complementoSetor = $pedido->complementoSetor->Setor ?? '-';
    $atendidoPor = $pedido->responsavel_atendimento->name ?? '-';
    $atendidoEm = optional($pedido->DataTermino)->format('d/m/Y H:i:s');
    $observacao = $pedido->Observacao ?? '-';
    $total = 0;
    $seq = 1;
@endphp

<br>

<table cellspacing="0" cellpadding="2" width="97%" border="0">
    <tr>
        <td width="100px" rowspan="2" style="border-top: 1px solid var(--page-border); border-left: 1px solid var(--page-border); border-bottom: 1px solid var(--page-border);">
            <img src="{{ asset('images/brasao-tjes.png') }}" width="70" height="70" alt="Brasão">
        </td>
        <td valign="top" style="border-top: 1px solid var(--page-border);">
            <span class="titulo">TRIBUNAL DE JUSTIÇA DO ESTADO ES</span>
        </td>
        <td style="border-top: 1px solid var(--page-border);">&nbsp;</td>
        <td style="border-top: 1px solid var(--page-border);">&nbsp;</td>
        <td style="border-top: 1px solid var(--page-border);">&nbsp;</td>
        <td style="border-top: 1px solid var(--page-border); border-right: 1px solid var(--page-border);">&nbsp;</td>
    </tr>
    <tr>
        <td valign="bottom" style="border-bottom: 1px solid var(--page-border);">
            <span class="titulo1">Relatório<br>{{ $dataAtual }}</span>
        </td>
        <td style="border-bottom: 1px solid var(--page-border);">&nbsp;</td>
        <td style="border-bottom: 1px solid var(--page-border);">&nbsp;</td>
        <td style="border-bottom: 1px solid var(--page-border);">&nbsp;</td>
        <td valign="bottom" style="border-bottom: 1px solid var(--page-border); border-right: 1px solid var(--page-border);">
            <span class="titulo1">Seção de Material de Consumo</span>
        </td>
    </tr>
    <tr><td colspan="6">&nbsp;</td></tr>
    <tr class="cabecalho">
        <td colspan="6" align="center">
                <span class="titulo-termo">
                    Requisição de Material em Estoque (RME) - Atendimento - Pedido Nº {{ $numero }}/{{ $ano }}
                </span>
        </td>
    </tr>
    <tr><td colspan="6">&nbsp;</td></tr>

    <tr>
        <td class="cabecalho"><span class="titulo-coluna">Número</span></td>
        <td colspan="3" class="cabecalho"><span class="registro-coluna">{{ $numero }}</span></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td class="cabecalho"><span class="titulo-coluna">Setor/Complemento</span></td>
        <td colspan="3" class="cabecalho"><span class="registro-coluna">{{ $setor }}</span></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td class="cabecalho"><span class="titulo-coluna">Complemento</span></td>
        <td colspan="3" class="cabecalho"><span class="registro-coluna">{{ $complementoSetor }}</span></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td class="cabecalho"><span class="titulo-coluna">Solicitante</span></td>
        <td colspan="3" class="cabecalho"><span class="registro-coluna">{{ $solicitante }}</span></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td class="cabecalho"><span class="titulo-coluna">Data Pedido</span></td>
        <td colspan="3" class="cabecalho"><span class="registro-coluna">{{ $dataPedido }}</span></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td class="cabecalho"><span class="titulo-coluna">Atendimento</span></td>
        <td colspan="3" class="cabecalho">
            <span class="registro-coluna">{{ $atendidoPor }} ({{ $atendidoEm ?: '-' }})</span>
        </td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td class="cabecalho"><span class="titulo-coluna">Observação</span></td>
        <td colspan="3" class="cabecalho"><span class="registro-coluna">{{ $observacao }}</span></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr><td colspan="6">&nbsp;</td></tr>
</table>

<table cellspacing="0" cellpadding="2" width="97%" border="1">
    <tr class="cabecalho">
        <td align="center"><span class="titulo-coluna">Item</span></td>
        <td align="center"><span class="titulo-coluna">Descrição</span></td>
        <td align="center"><span class="titulo-coluna">Qtde. Solicitada</span></td>
        <td align="center"><span class="titulo-coluna">Qtde. Atendida</span></td>
        <td align="center"><span class="titulo-coluna">Valor Médio</span></td>
        <td align="center"><span class="titulo-coluna">Valor Total Médio</span></td>
    </tr>

    @foreach ($pedido->itens as $item)
        @php
            $descricao = $item->descricaoDetalhadaRel->descricao_detalhada
                ?? $item->materialRel->descricao_detalhada
                ?? '-';

            $qtdeSolicitada = $item->QuantidadeMaterial ?? 0;
            $qtdeAtendida = $item->QuantidadeMaterialAtendida ?? 0;
            $valorMedio = \App\Filament\Egap\Resources\Almoxarifado\PedidosResource::normalizarValorMonetario($item->valor_material ?? 0);
            $valorTotalMedio = $qtdeAtendida * $valorMedio;
            $total += $valorTotalMedio;
        @endphp

        <tr>
            <td align="center"><span class="registro-coluna">{{ $seq }}</span></td>
            <td align="left"><span class="registro-coluna">{{ $descricao }}</span></td>
            <td align="center"><span class="registro-coluna">{{ $qtdeSolicitada }}</span></td>
            <td align="center"><span class="registro-coluna">{{ $qtdeAtendida }}</span></td>
            <td align="center"><span class="registro-coluna">R$ {{ number_format($valorMedio, 2, ',', '.') }}</span></td>
            <td align="center"><span class="registro-coluna">R$ {{ number_format($valorTotalMedio, 2, ',', '.') }}</span></td>
        </tr>

        @php $seq++; @endphp
    @endforeach

    <tr>
        <td></td>
        <td align="center" class="titulo-coluna">TOTAL</td>
        <td></td>
        <td></td>
        <td></td>
        <td align="center" class="titulo-coluna">R$ {{ number_format($total, 2, ',', '.') }}</td>
    </tr>
</table>

<div style="padding-top: 100px; width: 100%">
    <center>
        <table>
            <tr>
                <td><span class="texto" style="font-size:11px;">Material separado por</span></td>
                <td align="right" valign="top" style="width: 400px; border-bottom: 1px solid var(--page-border);"></td>
                <td>em <span class="texto-assinatura">_____/_____/______</span></td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: center; font-family: Verdana; font-size: 10px;">
                    <b>Almoxarife: Assinatura e Carimbo</b>
                </td>
                <td></td>
            </tr>
        </table>

        <table style="margin-top: 100px;">
            <tr>
                <td valign="top">
                    <span class="texto" style="font-size:10px;"><b>Atenção:</b> Favor conferir o(s) material(is) no ato da entrega.</span><br>
                    <span class="texto" style="font-size:10px;">Não serão aceitas reclamações posteriores.</span><br>
                    <span class="texto" style="font-size:10px;">Declaro ter recebido o(s) material(is) listado(s) acima.</span><br>
                </td>
                <td valign="middle">
                    <p style="text-align: center;">
                        <br>
                        <span class="texto-assinatura">_____/_____/______</span><br>
                        <span><b>Assinatura e Carimbo</b></span>
                    </p>
                </td>
                <td valign="middle">
                    <p>
                        <br>
                    <hr style="width: 400px; border-top: 1px solid var(--page-border);">
                    <br>
                    </p>
                </td>
            </tr>
        </table>
    </center>
</div>
</body>
</html>
