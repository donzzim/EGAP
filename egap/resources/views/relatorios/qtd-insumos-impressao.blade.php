@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Quantidade de Insumos de Impressão')

@section('tabela')
    <style>
        /* Esconder tabela padrão de brasão (este relatório não a utiliza) */
        table[width="100%"] { display: none; }

        .container { font-family: Arial, sans-serif; width: 100%; color: #333; }

        /* Estilos do Formulário Cinzento (Well) */
        .well { background-color: #f5f5f5; padding: 20px; border-radius: 4px; border: 1px solid #e3e3e3; text-align: center; margin-bottom: 20px; }
        .well h1 { font-size: 22px; font-weight: bold; margin-top: 0; margin-bottom: 15px; color: #333; }
        .form-inline { display: inline-block; }
        .form-inline label { font-weight: bold; margin: 0 5px; font-size: 13px; }
        .form-inline input[type="date"] { padding: 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; }
        .btn-success { background-color: #5cb85c; color: white; border: 1px solid #4cae4c; padding: 7px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 10px; font-size: 13px;}
        .btn-success:hover { background-color: #449d44; }

        .periodo-header { font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 25px; margin-top: 25px; }
        h3.uo-title { font-size: 15px; font-weight: bold; margin-top: 30px; margin-bottom: 10px; text-transform: uppercase; }
        h3.resumo-title { text-align: center; margin-top: 15px; font-size: 15px; font-weight: bold; }

        .tabela-limpa { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 15px; border: 1px solid #ddd; }
        .tabela-limpa th, .tabela-limpa td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        .tabela-limpa th { background-color: #fff; font-weight: bold; }

        .tabela-resumo { width: 60%; margin: 0 auto 40px auto; border-collapse: collapse; font-size: 12px; border: 1px solid #ddd; }
        .tabela-resumo th, .tabela-resumo td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }

        .bg-striped tbody tr:nth-child(odd) { background-color: #f9f9f9; }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

        /* Na hora de imprimir o papel, o formulário desaparece! */
        @media print {
            .no-print { display: none !important; }
        }
    </style>

    <div class="container">

        <div class="well no-print">
            <h1>Quantidade de Insumos de Impressão Fornecidos por Unidade Judiciária</h1>
            <form method="GET" action="{{ route('relatorios.gerais.imprimir') }}" class="form-inline">
                <input type="hidden" name="relatorio" value="{{ $filtros['relatorio'] ?? 'qtd_insumos_impressao' }}">

                <label>Período: </label>
                <input type="date" name="data_inicio" value="{{ $filtros['data_inicio'] ?? '' }}" required>
                <label>a</label>
                <input type="date" name="data_termino" value="{{ $filtros['data_termino'] ?? '' }}" required>

                <button type="submit" class="btn-success">Enviar</button>
            </form>
        </div>

        @if($mostrarDados)
            <div class="periodo-header">Período: {{ $periodoStr }}</div>

            @forelse($porUO as $unidade => $itens)
                <h3 class="uo-title">{{ $unidade }}</h3>
                <table class="tabela-limpa bg-striped">
                    <thead>
                        <tr>
                            <th width="35%">Setor</th>
                            <th width="40%">Material</th>
                            <th width="7%" class="text-center">Atendido</th>
                            <th width="9%" class="text-center">Valor Unitário</th>
                            <th width="9%" class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $tAtendido = 0; $tGasto = 0; @endphp
                        @foreach($itens as $linha)
                            @php $tAtendido += $linha->atendido; $tGasto += $linha->total; @endphp
                            <tr>
                                <td>{{ $linha->Setor }}</td>
                                <td>{{ $linha->descricao_detalhada }}</td>
                                <td class="text-center">{{ number_format($linha->atendido, 0, ',', '.') }}</td>
                                <td class="text-center">R$ {{ number_format($linha->valor_unitario, 2, ',', '.') }}</td>
                                <td class="text-center">R$ {{ number_format($linha->total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="text-right">Total</td>
                            <td class="text-center">{{ number_format($tAtendido, 0, ',', '.') }}</td>
                            <td></td>
                            <td class="text-center">R$ {{ number_format($tGasto, 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <h3 class="resumo-title">Resumo</h3>
                <table class="tabela-resumo bg-striped">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="65%">Material</th>
                            <th width="10%" class="text-center">Atendidos</th>
                            <th width="10%">Valor Unit.</th>
                            <th width="10%">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $resumoLocal = collect($itens)->groupBy('descricao_detalhada')->map(function($g, $desc) {
                                return (object)['desc' => $desc, 'atendidos' => collect($g)->sum('atendido'), 'val_unit' => collect($g)->first()->valor_unitario, 'total' => collect($g)->sum('total')];
                            })->sortBy('desc')->values();
                            $i = 1;
                        @endphp
                        @foreach($resumoLocal as $sub)
                            <tr>
                                <td class="text-center">{{ $i++ }}</td>
                                <td>{{ $sub->desc }}</td>
                                <td class="text-center">{{ number_format($sub->atendidos, 0, ',', '.') }}</td>
                                <td>R$ {{ number_format($sub->val_unit, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($sub->total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="text-right">Total</td>
                            <td class="text-center">{{ number_format($resumoLocal->sum('atendidos'), 0, ',', '.') }}</td>
                            <td></td>
                            <td>R$ {{ number_format($resumoLocal->sum('total'), 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            @empty
                <p class="text-center" style="padding: 20px;">Nenhum insumo fornecido no período.</p>
            @endforelse

            @if($porUO->isNotEmpty())
                <h3 class="resumo-title" style="margin-top: 40px; padding: 10px; background-color: #f5f5f5; border: 1px solid #e3e3e3; border-bottom: none; width: 60%; margin-left: auto; margin-right: auto;">Resumo Geral por Material</h3>
                <table class="tabela-resumo bg-striped" style="margin-top: 0;">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="65%">Material</th>
                            <th width="10%" class="text-center">Atendidos</th>
                            <th width="10%">Valor Unit.</th>
                            <th width="10%">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach($resumoGeralMaterial as $sub)
                            <tr>
                                <td class="text-center">{{ $i++ }}</td>
                                <td>{{ $sub->descricao }}</td>
                                <td class="text-center">{{ number_format($sub->atendido, 0, ',', '.') }}</td>
                                <td>R$ {{ number_format($sub->valor_unitario, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($sub->total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="text-right">Total</td>
                            <td class="text-center">{{ number_format($resumoGeralMaterial->sum('atendido'), 0, ',', '.') }}</td>
                            <td></td>
                            <td>R$ {{ number_format($resumoGeralMaterial->sum('total'), 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <h3 class="resumo-title" style="margin-top: 40px; padding: 10px; background-color: #f5f5f5; border: 1px solid #e3e3e3; border-bottom: none; width: 60%; margin-left: auto; margin-right: auto;">Gasto Total por Unidade</h3>
                <table class="tabela-resumo bg-striped" style="margin-top: 0;">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="75%">Unidade</th>
                            <th width="20%">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach($resumoGeralUnidade as $sub)
                            <tr>
                                <td class="text-center">{{ $i++ }}</td>
                                <td>{{ $sub->descricao }}</td>
                                <td>R$ {{ number_format($sub->total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="text-right">Total</td>
                            <td>R$ {{ number_format($resumoGeralUnidade->sum('total'), 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        @endif
    </div>
@endsection
