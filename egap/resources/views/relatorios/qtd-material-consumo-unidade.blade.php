@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Quantidade de Materiais de Consumo')

@section('tabela')
    <style>
        table[width="100%"] { display: none; }
        
        .container { font-family: Arial, sans-serif; width: 100%; color: #333; }
        
        .well { background-color: #f5f5f5; padding: 20px; border-radius: 4px; border: 1px solid #e3e3e3; text-align: center; margin-bottom: 20px; }
        .well h1 { font-size: 22px; font-weight: bold; margin-top: 0; margin-bottom: 15px; color: #333; }
        
        .form-inline { display: inline-block; }
        .form-inline label { font-weight: bold; margin: 0 5px; font-size: 13px; }
        .form-inline input[type="date"], .form-inline select { padding: 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; }
        .form-inline select { width: 300px; max-width: 100%; }
        
        .btn-primary { background-color: #006dcc; color: white; border: 1px solid #0044cc; padding: 7px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 10px; font-size: 13px;}
        .btn-primary:hover { background-color: #0044cc; }

        .periodo-header { font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 25px; margin-top: 25px; }
        .unidade-selecionada { font-size: 20px; font-weight: bold; text-align: center; margin-bottom: 5px; }
        
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

        @media print {
            .no-print { display: none !important; }
        }
    </style>

    <div class="container">
        
        <div class="well no-print">
            <h1>Quantidade de Materiais de Consumo Fornecidos por Unidade Judiciária</h1>
            <form method="GET" action="{{ route('relatorios.gerais.imprimir') }}" class="form-inline">
                <input type="hidden" name="relatorio" value="{{ $filtros['relatorio'] ?? 'qtd_material_consumo_unidade' }}">
                
                <label>Unidade: </label>
                <select name="unidade_selecionada">
                    <option value="">-- TODAS --</option>
                    @foreach($listaUnidades as $val => $desc)
                        <option value="{{ $val }}" {{ (isset($filtros['unidade_selecionada']) && $filtros['unidade_selecionada'] == $val) ? 'selected' : '' }}>
                            {{ $desc }}
                        </option>
                    @endforeach
                </select>
                
                <label>Período: </label>
                <input type="date" name="data_inicio" value="{{ $filtros['data_inicio'] ?? '' }}" required>
                <label>a</label>
                <input type="date" name="data_termino" value="{{ $filtros['data_termino'] ?? '' }}" required>
                
                <button type="submit" class="btn-primary">Enviar</button>
            </form>
        </div>

        @if($mostrarDados)
            @if(!empty($unidDescricao))
                <div class="unidade-selecionada">{{ $unidDescricao }}</div>
            @endif
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
                <p class="text-center" style="padding: 20px;">Nenhum material fornecido no período e filtros informados.</p>
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