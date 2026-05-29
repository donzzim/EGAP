@extends('relatorios.layout-tce')

@section('titulo_pagina', 'Aquisição de Materiais - Item Estoque/Visível')

@section('tabela')
    <style>
        .container { font-family: Verdana, sans-serif; width: 100%; color: #000; font-size: 10px; }

        /* Estilos do Formulário do Topo */
        .form-top { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #000; padding-bottom: 10px; }
        .form-inline { display: inline-block; }
        .form-inline label { font-weight: bold; margin: 0 5px; font-size: 12px; }
        .form-inline input[type="date"] { padding: 4px; border: 1px solid #ccc; font-size: 12px; }

        .btn-primary { background-color: #006dcc; color: white; border: 1px solid #0044cc; padding: 5px 15px; cursor: pointer; font-weight: bold; margin-left: 10px;}
        .btn-success { background-color: #5cb85c; color: white; border: 1px solid #4cae4c; padding: 8px 15px; cursor: pointer; font-weight: bold;}

        .mes-titulo { font-size: 18px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; }

        .tabela-grid { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 20px; border: 1px solid #000; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000; padding: 6px; }
        .tabela-grid th { font-weight: bold; text-align: center; text-transform: uppercase; }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

        .box-bottom { text-align: center; margin-top: 20px; margin-bottom: 40px; }

        @media print {
            .naoimprimir { display: none !important; }
            .form-top { border-bottom: none; margin-bottom: 0; padding-bottom: 0;}
        }
    </style>

    <div class="container">

        <div class="form-top naoimprimir">
            <form method="GET" action="{{ route('relatorios.gerais.imprimir') }}" class="form-inline" id="formPesquisa">
                <input type="hidden" name="relatorio" value="{{ $filtros['relatorio'] ?? 'aquisicao_materiais_estoque_visivel' }}">

                <label>Período: </label>
                <input type="date" name="data_inicio" value="{{ $data_inicio_padrao }}" required>
                <label>a</label>
                <input type="date" name="data_termino" value="{{ $data_termino_padrao }}" required>

                <button type="submit" class="btn-primary">Enviar</button>
            </form>
        </div>

        @if($mostrarDados)
            <table cellspacing="0" cellpadding="2" width="100%" border="0" style="margin-bottom: 20px;">
                <tr>
                    <td width="100px" rowspan="2" style="border-top: 1px solid #000; border-left: 1px solid #000; border-bottom: 1px solid #000;">
                        <img src="{{ asset('images/brasao-tjes.png') }}" width="70" height="70" border="0" />
                    </td>
                    <td valign="top" style="border-top: 1px solid #000; font-size: 14px; font-weight: bold;">TRIBUNAL DE JUSTIÇA DO ESTADO ES</td>
                    <td style="border-top: 1px solid #000;">&nbsp;</td>
                    <td style="border-top: 1px solid #000; border-right: 1px solid #000;">&nbsp;</td>
                </tr>
                <tr>
                    <td valign="bottom" style="border-bottom: 1px solid #000; font-size: 12px; font-weight: bold;">Relatório de Aquisição de Materiais - Item Estoque/Visível</td>
                    <td style="border-bottom: 1px solid #000;">&nbsp;</td>
                    <td valign="bottom" style="border-bottom: 1px solid #000; border-right: 1px solid #000; font-size: 12px; font-weight: bold;">Seção de Material de Consumo</td>
                </tr>
                <tr><td colspan="4" style="height: 10px;"></td></tr>
                <tr>
                    <td colspan="4" align="center" style="border: 1px solid #000; font-size: 18px; font-weight: bold; padding: 5px;">
                        Relatório de Aquisição de Materiais - Item Estoque/Visível
                    </td>
                </tr>
                <tr><td colspan="4" style="height: 10px;"></td></tr>
                <tr>
                    <td style="border: 1px solid #000; font-size: 11px; font-weight: bold; padding: 5px; text-transform: uppercase;">Período</td>
                    <td colspan="3" style="border: 1px solid #000; font-size: 13px; padding: 5px;">{{ $periodoStr }}</td>
                </tr>
            </table>

            <form method="GET" action="{{ route('relatorios.gerais.imprimir') }}">
                <input type="hidden" name="relatorio" value="aquisicao_materiais_estoque_visivel">
                <input type="hidden" name="data_inicio" value="{{ $data_inicio_padrao }}">
                <input type="hidden" name="data_termino" value="{{ $data_termino_padrao }}">

                @foreach($dadosAgrupados as $grupo)
                    <div class="mes-titulo">{{ $grupo->nome }}</div>

                    <table class="tabela-grid">
                        <tr>
                            <th width="5%">
                                <input type="checkbox" class="naoimprimir" id="checkAll_{{ Str::slug($grupo->nome) }}" onclick="toggleCheckboxes(this, 'item_{{ Str::slug($grupo->nome) }}')" />
                                <br class="naoimprimir">ITEM
                            </th>
                            <th width="55%">DESCRIÇÃO</th>
                            <th width="10%">QTDE</th>
                            <th width="15%">VALOR UNITÁRIO</th>
                            <th width="15%">VALOR TOTAL</th>
                        </tr>

                        @php
                            $seq = 1;
                            $tQtde = 0;
                            $tGasto = 0;
                            $tUnitario = 0;
                        @endphp

                        @foreach($grupo->itens as $linha)
                            @php
                                $tQtde += $linha->quantidade;
                                $tGasto += $linha->valor_total;
                                $tUnitario += $linha->preco_unitario;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $seq++ }}</td>
                                <td>
                                    <span class="naoimprimir">
                                        <input type="checkbox" name="materiais[]" class="item_{{ Str::slug($grupo->nome) }}" value="{{ $linha->id_descricao_detalhada }}" /> -
                                    </span>
                                    {{ $linha->descricao_detalhada }}
                                </td>
                                <td class="text-center">{{ number_format($linha->quantidade, 0, ',', '.') }}</td>
                                <td class="text-right">R$ {{ number_format($linha->preco_unitario, 2, ',', '.') }}</td>
                                <td class="text-right">R$ {{ number_format($linha->valor_total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach

                        <tr>
                            <td colspan="2" class="text-right" style="font-weight: bold;">TOTAL:</td>
                            <td class="text-center" style="font-weight: bold;">{{ number_format($tQtde, 0, ',', '.') }}</td>
                            <td class="text-right" style="font-weight: bold;">R$ {{ number_format($tUnitario, 2, ',', '.') }}</td>
                            <td class="text-right" style="font-weight: bold;">R$ {{ number_format($tGasto, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                @endforeach

                <div class="box-bottom naoimprimir">
                    <button type="submit" class="btn-success">Imprimir somente os selecionados</button>
                </div>
            </form>

            <script>
                function toggleCheckboxes(source, className) {
                    checkboxes = document.getElementsByClassName(className);
                    for(var i=0, n=checkboxes.length;i<n;i++) {
                        checkboxes[i].checked = source.checked;
                    }
                }
            </script>
        @endif
    </div>
@endsection
