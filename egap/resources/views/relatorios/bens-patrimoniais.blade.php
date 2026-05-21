@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Bens Patrimoniais')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-top: 5px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 5px; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; font-size: 10px; }
        
        @media print {
            .nova-pagina { page-break-before: always; }
            .grafico-container { page-break-inside: avoid; }
        }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 10px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório de Bens Patrimoniais</td>
            <td width="50%" align="right">Seção de Patrimônio</td>
        </tr>
    </table>

    <table class="tabela-grid">
        <tr class="linha-cabecalho">
            <th width="3%">ITEM</th>
            <th width="15%">SETOR</th>
            <th width="8%">PATRIMÔNIO</th>
            <th width="20%">DESCRIÇÃO DO BEM/MARCA/MODELO</th>
            <th width="8%">DATA INCORPORAÇÃO</th>
            <th width="8%">VALOR AQUISIÇÃO</th>
            <th width="15%">FORNECEDOR</th>
            <th width="8%">PROCESSO</th>
            <th width="7%">NOTA FISCAL</th>
            <th width="8%">INVENTÁRIO</th>
        </tr>

        @php $seq = 1; $totalAquisicao = 0; @endphp

        @forelse ($dados as $linha)
            @php 
                $totalAquisicao += $linha->valor_aquisicao;
                $marca = $linha->marca ? '/' . $linha->marca : '';
                $modelo = $linha->modelo ? '/' . $linha->modelo : '';
            @endphp
            <tr>
                <td style="text-align: center;">{{ $seq++ }}</td>
                <td style="text-align: left;">{{ $linha->setor }}</td>
                <td style="text-align: center;">{{ number_format($linha->patrimonio, 0, ',', '.') }}</td>
                <td style="text-align: left;">{{ $linha->descricao }}{{ $marca }}{{ $modelo }}</td>
                <td style="text-align: center;">{{ $linha->data_incorporacao ? \Carbon\Carbon::parse($linha->data_incorporacao)->format('d/m/Y') : '' }}</td>
                <td style="text-align: right;">{{ number_format($linha->valor_aquisicao, 2, ',', '.') }}</td>
                <td style="text-align: left;">{{ $linha->fornecedor }}</td>
                <td style="text-align: center;">{{ $linha->processo }}</td>
                <td style="text-align: center;">{{ $linha->nota_fiscal }}</td>
                <td style="text-align: center;">{{ $linha->grupo_desc }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px;">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse

        @if($dados->count() > 0)
            <tr>
                <td colspan="5" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totalAquisicao, 2, ',', '.') }}</td>
                <td colspan="4"></td>
            </tr>
        @endif
    </table>

    @if($dados->count() > 0)
        <div class="nova-pagina" style="margin-top: 40px;">
            
            <table style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 10px;">
                <tr>
                    <td width="10%"><img src="{{ asset('images/brasao-tjes.png') }}" width="60" alt="Brasão"></td>
                    <td width="70%" style="padding-left: 10px;">
                        <div style="font-weight: bold; font-size: 14px;">TRIBUNAL DE JUSTIÇA DO ESTADO ES</div><br>
                        <div style="font-weight: bold; font-size: 12px;">Relatório de Bens Patrimoniais</div>
                    </td>
                    <td width="20%" style="text-align: right; vertical-align: top; font-weight: bold; font-size: 12px;">
                        {{ $data_emissao }}<br><br>Seção de Patrimônio
                    </td>
                </tr>
            </table>

            <table class="tabela-grid">
                <tr class="linha-cabecalho">
                    <th style="text-align: left;">Descrição</th>
                    <th>Inventariado a partir de 2015</th>
                    <th>Valor Aquisição</th>
                    <th>Inventariado antes de 2015</th>
                    <th>Valor Aquisição</th>
                    <th>Inventário Online</th>
                    <th>Valor Aquisição</th>
                    <th>A inventariar</th>
                    <th>Valor Aquisição</th>
                    <th>TOTAL</th>
                </tr>

                @php
                    $tg_qa = 0; $tg_va = 0;
                    $tg_qb = 0; $tg_vb = 0;
                    $tg_qc = 0; $tg_vc = 0;
                    $tg_qd = 0; $tg_vd = 0;
                    $tg_total = 0;
                @endphp

                @foreach($resumo as $r)
                    @php
                        $tg_qa += $r->qtd_a; $tg_va += $r->val_a;
                        $tg_qb += $r->qtd_b; $tg_vb += $r->val_b;
                        $tg_qc += $r->qtd_c; $tg_vc += $r->val_c;
                        $tg_qd += $r->qtd_d; $tg_vd += $r->val_d;
                        $tg_total += $r->qtd_total;
                    @endphp
                    <tr>
                        <td style="text-align: left;">{{ mb_strtoupper($r->descricao) }}</td>
                        <td style="text-align: center;">{{ $r->qtd_a }}</td>
                        <td style="text-align: right;">{{ number_format($r->val_a, 2, ',', '.') }}</td>
                        <td style="text-align: center;">{{ $r->qtd_b }}</td>
                        <td style="text-align: right;">{{ number_format($r->val_b, 2, ',', '.') }}</td>
                        <td style="text-align: center;">{{ $r->qtd_c }}</td>
                        <td style="text-align: right;">{{ number_format($r->val_c, 2, ',', '.') }}</td>
                        <td style="text-align: center;">{{ $r->qtd_d }}</td>
                        <td style="text-align: right;">{{ number_format($r->val_d, 2, ',', '.') }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $r->qtd_total }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td style="text-align: left; font-weight: bold;">TOTAL</td>
                    <td style="text-align: center; font-weight: bold;">{{ $tg_qa }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tg_va, 2, ',', '.') }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $tg_qb }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tg_vb, 2, ',', '.') }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $tg_qc }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tg_vc, 2, ',', '.') }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $tg_qd }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tg_vd, 2, ',', '.') }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $tg_total }}</td>
                </tr>
            </table>

            <div class="grafico-container" style="margin-top: 30px; text-align: center;">
                <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                <script type="text/javascript">
                    google.charts.load("current", {packages:["corechart"]});
                    google.charts.setOnLoadCallback(drawChart);
                    function drawChart() {
                        var data = google.visualization.arrayToDataTable([
                            ['Inventário', 'Quantidade'],
                            @foreach($chartData as $label => $qtd)
                                ['{!! $label !!}', {{ $qtd }}],
                            @endforeach
                        ]);

                        var options = {
                            title: 'Acurácia dos Bens {{ $filtros["acuracia"] ? "- " . $filtros["acuracia"] : "" }}',
                            pieSliceText: 'label',
                            is3D: true,
                            pieHole: 0.4,
                            height: 400,
                            legend: {position: 'right', textStyle: {fontSize: 12}}
                        };

                        var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
                        chart.draw(data, options);
                    }
                </script>
                <div id="piechart_3d" style="width: 100%; display: flex; justify-content: center;"></div>
            </div>
        </div>
    @endif
@endsection