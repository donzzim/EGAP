@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Relatório Estatístico de Consumo')

@section('tabela')
    <style>
        table[width="100%"] { display: none; } /* Esconde brasão padrão */

        .container { font-family: Arial, sans-serif; width: 100%; color: #333; font-size: 11px; }

        .well { background-color: #f5f5f5; padding: 20px; border-radius: 4px; border: 1px solid #e3e3e3; text-align: center; margin-bottom: 20px; }
        .well h1 { font-size: 22px; font-weight: bold; margin-top: 0; margin-bottom: 5px; color: #333; }
        .well h2 { font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 15px; color: #555; }

        .form-inline { display: inline-block; margin-top: 10px; }
        .form-inline label { font-weight: bold; margin: 0 5px; font-size: 13px; }
        .form-inline input[type="text"], .form-inline select { padding: 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; }
        .form-inline select { width: 350px; max-width: 100%; }
        .form-inline input[type="text"] { width: 60px; text-align: center; }

        .btn-primary { background-color: #006dcc; color: white; border: 1px solid #0044cc; padding: 7px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 10px; font-size: 13px;}
        .check-container { margin-top: 15px; font-size: 12px; }

        .ano-header { font-size: 26px; font-weight: bold; text-align: center; margin-bottom: 20px; margin-top: 20px; }

        .unidade-titulo { background-color: #ffff00; font-size: 20px; font-weight: bold; padding: 5px 10px; margin-top: 40px; margin-bottom: 0; border: 1px solid #ddd; border-bottom: none;}

        .tabela-grid { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 20px; border: 1px solid #ddd; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #ddd; padding: 6px 4px; text-align: center; vertical-align: middle;}
        .tabela-grid th { background-color: #fff; font-weight: bold; }
        .tabela-grid th.cinza, .tabela-grid td.cinza { background-color: #e7e7e8 !important; font-weight: bold;}
        .tabela-grid td.vermelho { background-color: #ffacaa !important; color: #ef0709 !important; font-weight: bold; }
        .tabela-grid td.esquerda { text-align: left; }

        .bg-striped tbody tr:nth-child(odd) { background-color: #f9f9f9; }

        @media print {
            .naoimprimir { display: none !important; }
            .unidade-titulo, .cinza, .vermelho { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .pagebreak { page-break-before: always; }
        }
    </style>

    <div class="container">

        <div class="well naoimprimir">
            <h1>Relatório Estatístico de Consumo de Materiais do Almoxarifado</h1>
            <h2>Meta do Ato Normativo TJES Nº 069/2020</h2>

            <form method="GET" action="{{ route('relatorios.gerais.imprimir') }}" class="form-inline">
                <input type="hidden" name="relatorio" value="{{ $filtros['relatorio'] ?? 'estatistico_consumo_almoxarifado_meta' }}">

                <label>Unidade: </label>
                <select name="unidade">
                    <optgroup label="Unidades">
                        <option value="-1|T" {{ (isset($filtros['unidade']) && $filtros['unidade'] == '-1|T') ? 'selected' : '' }}>Tribunal e Corregedoria</option>
                        <option value="-2|C" {{ (isset($filtros['unidade']) && $filtros['unidade'] == '-2|C') ? 'selected' : '' }}>Comarcas</option>
                    </optgroup>
                    <optgroup label="Setores">
                        @foreach($listaSetores as $s)
                            <option value="{{ $s->val }}" {{ (isset($filtros['unidade']) && $filtros['unidade'] == $s->val) ? 'selected' : '' }}>
                                {{ $s->label }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>

                <label>Ano: </label>
                <input type="text" name="ano" value="{{ $ano_padrao }}" required>

                <button type="submit" class="btn-primary">Enviar</button>

                <div class="check-container">
                    <label style="margin-right: 15px;">
                        <input type="checkbox" name="excel" value="S" {{ (isset($filtros['excel']) && $filtros['excel'] == 'S') ? 'checked' : '' }}>
                        Exportar para Excel
                    </label>
                    <label>
                        <input type="checkbox" name="meta" value="S" {{ (isset($filtros['meta']) && $filtros['meta'] == 'S') ? 'checked' : '' }}>
                        Apenas os itens fora da Meta
                    </label>
                </div>
            </form>
        </div>

        @if($mostrarDados)
            <div class="ano-header">{{ $ano }}</div>

            @forelse($relatorioFinal as $index => $grupo)
                <div class="{{ $index > 0 ? 'pagebreak' : '' }}">
                    <div class="unidade-titulo">{{ $grupo->unidade }}</div>

                    <table class="tabela-grid bg-striped">
                        <thead>
                            <tr>
                                <th width="15%">Material de Consumo</th>
                                <th>Qtde Atendida 1o Trimestre {{ $anoAnt }}</th>
                                <th>Qtde Atendida 2o Trimestre {{ $anoAnt }}</th>
                                <th>Qtde Atendida 3o Trimestre {{ $anoAnt }}</th>
                                <th>Qtde Atendida 4o Trimestre {{ $anoAnt }}</th>
                                <th class="cinza">QTDE Total Atendida {{ $anoAnt }}</th>

                                <th>Qtde Atendida 1o Trimestre {{ $ano }}</th>
                                <th>Resultado 1o Trimestre</th>
                                <th>Qtde Atendida 2o Trimestre {{ $ano }}</th>
                                <th>Resultado 2o Trimestre</th>
                                <th>Qtde Atendida 3o Trimestre {{ $ano }}</th>
                                <th>Resultado 3o Trimestre</th>
                                <th>Qtde Atendida 4o Trimestre {{ $ano }}</th>
                                <th>Resultado 4o Trimestre</th>

                                <th class="cinza">QTDE Total Atendida {{ $ano }}</th>
                                <th>Resultado Anual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grupo->itens as $linha)
                                <tr>
                                    <td class="esquerda">{{ $linha->descricao }}</td>

                                    <td>{{ number_format($linha->ant_q1, 0, ',', '.') }}</td>
                                    <td>{{ number_format($linha->ant_q2, 0, ',', '.') }}</td>
                                    <td>{{ number_format($linha->ant_q3, 0, ',', '.') }}</td>
                                    <td>{{ number_format($linha->ant_q4, 0, ',', '.') }}</td>
                                    <td class="cinza">{{ number_format($linha->ant_total, 0, ',', '.') }}</td>

                                    <td class="{{ $linha->res_q1['fora'] ? 'vermelho' : '' }}">{{ number_format($linha->res_q1['val'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($linha->res_q1['perc'], 2, ',', '.') }} %</td>

                                    <td class="{{ $linha->res_q2['fora'] ? 'vermelho' : '' }}">{{ number_format($linha->res_q2['val'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($linha->res_q2['perc'], 2, ',', '.') }} %</td>

                                    <td class="{{ $linha->res_q3['fora'] ? 'vermelho' : '' }}">{{ number_format($linha->res_q3['val'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($linha->res_q3['perc'], 2, ',', '.') }} %</td>

                                    <td class="{{ $linha->res_q4['fora'] ? 'vermelho' : '' }}">{{ number_format($linha->res_q4['val'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($linha->res_q4['perc'], 2, ',', '.') }} %</td>

                                    <td class="cinza {{ $linha->res_total['fora'] ? 'vermelho' : '' }}">{{ number_format($linha->res_total['val'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($linha->res_total['perc'], 2, ',', '.') }} %</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <p class="text-center" style="padding: 20px; font-size: 14px;">Nenhum dado encontrado para a configuração selecionada.</p>
            @endforelse
        @endif
    </div>
@endsection
