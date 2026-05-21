@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Média de Consumo por Material')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 11px; margin-bottom: 20px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 6px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; }
        
        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 16px; padding: 6px; font-family: Verdana, sans-serif; margin-bottom: 15px;}

        .btn-success { background-color: #5cb85c; color: white; border: 1px solid #4cae4c; padding: 8px 14px; font-size: 12px; font-weight: bold; cursor: pointer; border-radius: 4px; font-family: Verdana, sans-serif; }
        .btn-success:hover { background-color: #449d44; }
        
        @media print {
            .nao-imprimir { display: none !important; }
        }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório de Média de Consumo por Material</td>
            <td width="50%" align="right">Seção de Material de Consumo</td>
        </tr>
    </table>

    <div class="caixa-titulo">
        Média de Consumo por Material
    </div>

    @isset($filtros['data_inicio'], $filtros['data_termino'])
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
            <tr>
                <td width="8%" style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 11px; font-weight: bold; padding: 4px 6px;">PERÍODO</td>
                <td width="92%" style="border: 1px solid #000 !important; font-family: Verdana, sans-serif; font-size: 11px; padding: 4px 6px;">
                    {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    @endisset

    <table class="tabela-grid">
        <tr class="linha-cabecalho">
            <th width="50%" style="text-align: left;">
                <input type="checkbox" id="checkAll" class="nao-imprimir" style="margin-right: 5px;"> MATERIAL
            </th>
            <th width="10%">QTDE ATUAL</th>
            <th width="15%">QTDE CONSUMIDA</th>
            <th width="15%">CONSUMO MÉDIO</th>
            <th width="10%">QTDE MÊS</th>
        </tr>

        @forelse ($dados as $linha)
            <tr class="linha-material">
                <td style="text-align: left;">
                    <input type="checkbox" class="item-checkbox nao-imprimir" style="margin-right: 5px;"> 
                    {{ $linha->descricao_detalhada }}
                </td>
                <td style="text-align: center;">{{ number_format($linha->qtde_atual, 2, ',', '.') }}</td>
                <td style="text-align: center;">{{ number_format($linha->qtde_consumida, 2, ',', '.') }}</td>
                <td style="text-align: center;">{{ number_format($linha->consumo_medio, 2, ',', '.') }}</td>
                <td style="text-align: center;">{{ $linha->meses }} ({{ $linha->dias }} dias)</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse
    </table>

    @if($dados->count() > 0)
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
                    
                    checkboxes.forEach(cb => {
                        if (cb.checked) temSelecionado = true;
                    });

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