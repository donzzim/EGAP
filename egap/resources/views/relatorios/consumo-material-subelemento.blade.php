@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Consumo de Material por Subelemento de Despesa')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 11px; margin-bottom: 20px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 6px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; }

        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 16px; padding: 6px; font-family: Verdana, sans-serif; margin-bottom: 15px;}
        .subelemento-header td { font-weight: bold; font-size: 16px; text-align: left; padding-top: 15px !important; padding-bottom: 10px !important; }

        .btn-success { background-color: #5cb85c; color: white; border: 1px solid #4cae4c; padding: 8px 14px; font-size: 12px; font-weight: bold; cursor: pointer; border-radius: 4px; font-family: Verdana, sans-serif; }
        .btn-success:hover { background-color: #449d44; }

        @media print {
            .nao-imprimir { display: none !important; }
        }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório de Consumo de Material por Subelemento de Despesa</td>
            <td width="50%" align="right">Seção de Material de Consumo</td>
        </tr>
    </table>

    <div class="caixa-titulo">
        Consumo de Material por Subelemento de Despesa
    </div>

    @isset($filtros['data_inicio'], $filtros['data_termino'])
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
            <tr>
                <td width="8%" style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 11px; font-weight: bold; padding: 4px 6px; text-transform: uppercase;">PERÍODO</td>
                <td width="92%" style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 11px; padding: 4px 6px;">
                    {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    @endisset

    <table class="tabela-grid">
        <tr class="linha-cabecalho">
            <th width="55%" style="text-align: center;">
                <input type="checkbox" id="checkAll" class="nao-imprimir" style="margin-right: 5px;" checked> MATERIAL
            </th>
            <th width="15%">QTDE CONSUMIDA</th>
            <th width="15%">ÚLTIMO PREÇO</th>
            <th width="15%">SUBTOTAL</th>
        </tr>

        @php $totalGeral = 0; @endphp

        @forelse ($dadosAgrupados as $subelemento => $itens)
            @php $subTotalElemento = 0; @endphp

            <tr class="subelemento-header">
                <td colspan="4" style="border-left: 1px solid #000; border-right: 1px solid #000;">
                    SubElemento: {{ $subelemento }}
                </td>
            </tr>

            @foreach ($itens as $linha)
                @php
                    $subTotalElemento += $linha->subtotal;
                    $totalGeral += $linha->subtotal;
                @endphp
                <tr class="linha-material">
                    <td style="text-align: left;">
                        <input type="checkbox" class="item-checkbox nao-imprimir" style="margin-right: 5px;" checked>
                        {{ $linha->descricao_detalhada }}
                    </td>
                    <td style="text-align: center;">{{ number_format($linha->qtde_consumida, 0, ',', '.') }}</td>
                    <td style="text-align: center;">R$ {{ number_format($linha->ultimo_preco, 2, ',', '.') }}</td>
                    <td style="text-align: center;">R$ {{ number_format($linha->subtotal, 2, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="linha-subtotal">
                <td colspan="3" style="text-align: right; font-weight: bold; font-size: 14px;">Total</td>
                <td style="text-align: center; font-weight: bold; font-size: 14px;">R$ {{ number_format($subTotalElemento, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px;">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse

        @if($dadosAgrupados->count() > 0)
            <tr class="linha-totalgeral">
                <td colspan="3" style="text-align: right; font-weight: bold; font-size: 16px;">Total Geral</td>
                <td style="text-align: center; font-weight: bold; font-size: 16px;">R$ {{ number_format($totalGeral, 2, ',', '.') }}</td>
            </tr>
        @endif
    </table>

    @if($dadosAgrupados->count() > 0)
        <div style="text-align: center; margin-top: 20px; margin-bottom: 40px;" class="nao-imprimir">
            <button id="btn-imprimir-selecionados" class="btn-success">Imprimir somente os selecionados</button>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('checkAll');
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const btnImprimir = document.getElementById('btn-imprimir-selecionados');

            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = checkAll.checked);
                });
            }

            if (btnImprimir) {
                btnImprimir.addEventListener('click', function() {
                    let temSelecionado = false;
                    checkboxes.forEach(cb => { if (cb.checked) temSelecionado = true; });

                    if (!temSelecionado) {
                        alert('Por favor, selecione pelo menos um material para imprimir.');
                        return;
                    }

                    document.querySelectorAll('.linha-material').forEach(row => {
                        const cb = row.querySelector('.item-checkbox');
                        if (!cb.checked) {
                            row.style.display = 'none';
                        }
                    });

                    window.print();

                    setTimeout(() => {
                        document.querySelectorAll('.linha-material').forEach(row => {
                            row.style.display = '';
                        });
                    }, 500);
                });
            }
        });
    </script>
@endsection
