@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Notas Fiscais por Fornecedor')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 11px; margin-bottom: 20px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 4px 6px; }
        .linha-cabecalho th { font-weight: bold; text-align: center; font-size: 11px; }
        .nf-header td { font-weight: bold; font-size: 12px; background-color: #f9f9f9; text-align: left; }

        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 16px; padding: 8px; font-family: Verdana, sans-serif; margin-bottom: 15px;}
        .fornecedor-titulo { font-family: Verdana, sans-serif; font-size: 14px; font-weight: bold; margin-top: 15px; margin-bottom: 5px; }

        .resumo-tabela { width: 50%; margin: 0 auto; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 11px; }
        .resumo-tabela th, .resumo-tabela td { border: 1px solid #000 !important; padding: 6px; text-align: center; }

        @media print {
            .quebra-pagina { page-break-after: always; }
            .resumo-container { page-break-before: always; padding-top: 40px; }
        }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório de Notas Fiscais por Fornecedor</td>
            <td width="50%" align="right">Seção de Almoxarifado</td>
        </tr>
    </table>

    <div class="caixa-titulo">
        Relatório de Notas Fiscais por Fornecedor
    </div>

    @isset($filtros['data_inicio'], $filtros['data_termino'])
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr>
                <td width="8%" style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 11px; font-weight: bold; padding: 4px 6px;">PERÍODO</td>
                <td width="92%" style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 11px; padding: 4px 6px;">
                    {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    @endisset

    @php
        $agrupadoPorFornecedor = $dados->groupBy('id_fornecedor');
    @endphp

    @forelse($agrupadoPorFornecedor as $idFornecedor => $itensFornecedor)
        @php $f = $itensFornecedor->first(); @endphp

        <div class="fornecedor-titulo">
            {{ $f->fornecedor }} - {{ $f->cnpj_formatado }}
        </div>

        @foreach($itensFornecedor->groupBy('id_notafiscal') as $idNf => $itensNf)
            @php $nf = $itensNf->first(); @endphp
            <table class="tabela-grid">
                <tr class="nf-header">
                    <td colspan="5">{{ $nf->tipo_doc_desc }}: {{ $nf->num_documento }} - {{ $nf->data_doc_formatada }}</td>
                </tr>
                <tr class="linha-cabecalho">
                    <th width="5%">Item</th>
                    <th width="45%">Descrição</th>
                    <th width="10%">Qtde</th>
                    <th width="20%">Valor Unitário</th>
                    <th width="20%">Valor Total</th>
                </tr>

                @php
                    $seq = 1;
                    $somaQtd = 0;
                    $somaValUnit = 0;
                    $somaValTotal = 0;
                @endphp

                @foreach($itensNf as $item)
                    @php
                        // No caso do item ser nulo (NF sem itens), ignoramos a soma
                        if($item->quantidade) {
                            $somaQtd += $item->quantidade;
                            $somaValUnit += $item->preco_unitario;
                            $somaValTotal += $item->valor_total;
                        }
                    @endphp
                    @if($item->quantidade)
                        <tr>
                            <td style="text-align: center;">{{ $seq++ }}</td>
                            <td>
                                {{ $item->descricao_resumida }}
                                {{ $item->descricao_detalhada ? ' - ' . $item->descricao_detalhada : '' }}
                                {{ $item->elemento_codigo ? ' - ' . $item->elemento_codigo : '' }}
                            </td>
                            <td style="text-align: center;">{{ $item->quantidade }}</td>
                            <td style="text-align: right;">R$ {{ number_format($item->preco_unitario, 4, ',', '.') }}</td>
                            <td style="text-align: right;">R$ {{ number_format($item->valor_total, 4, ',', '.') }}</td>
                        </tr>
                    @endif
                @endforeach

                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">Total: </td>
                    <td style="text-align: center; font-weight: bold;">{{ $somaQtd }}</td>
                    <td style="text-align: right; font-weight: bold;">R$ {{ number_format($somaValUnit, 4, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">R$ {{ number_format($somaValTotal, 4, ',', '.') }}</td>
                </tr>
            </table>
        @endforeach

    @empty
        @endforelse

    <div class="resumo-container">
        <h2 style="text-align: center; font-family: Verdana, sans-serif; font-size: 20px;">Quadro Resumo das Notas Fiscais</h2>
        <table class="resumo-tabela">
            <thead>
                <tr>
                    <th>Qtde. Notas Fiscais</th>
                    <th>Qtde. Itens</th>
                    <th>Soma do Valor Total das Notas</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($resumo->qtde_notas, 0, ',', '.') }}</td>
                    <td>{{ number_format($resumo->qtde_itens, 0, ',', '.') }}</td>
                    <td>R$ {{ number_format($resumo->total_notas, 4, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

@endsection
