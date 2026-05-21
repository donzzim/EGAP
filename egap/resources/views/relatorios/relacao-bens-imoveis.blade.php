@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Relatório dos Bens Imóveis')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-top: 15px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 4px; text-align: center; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; font-size: 9px; }
        
        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 14px; padding: 6px; text-transform: uppercase; font-family: Verdana, sans-serif; margin-bottom: 20px;}

        @media print {
            .nova-pagina { page-break-before: always; }
        }
    </style>

    @if($dadosAgrupados->isEmpty())
        <h2 style="text-align: center; font-family: Verdana; margin-top: 50px;">Nenhum registro encontrado para os filtros informados.</h2>
    @endif

    @php $isFirst = true; @endphp

    @foreach($dadosAgrupados as $tituloConta => $itens)

        <div class="{{ $isFirst ? '' : 'nova-pagina' }}" style="{{ $isFirst ? '' : 'margin-top: 40px;' }}">

            <div class="caixa-titulo">
                RELAÇÃO DOS BENS IMÓVEIS - {{ mb_strtoupper($tituloConta) }}
            </div>

            <table class="tabela-grid">
                <tr class="linha-cabecalho">
                    <th width="6%">NO. DE REGISTRO</th>
                    <th width="10%">DENOMINAÇÃO</th>
                    <th width="7%">DATA DE AQUISIÇÃO/<br>CONSTRUÇÃO/<br>INCORPORAÇÃO</th>
                    <th width="5%">TIPO DO<br>IMÓVEL</th>
                    <th width="7%">ESTADO DE<br>CONSERVAÇÃO</th>
                    <th width="7%">CONTA<br>CONTÁBIL</th>
                    <th width="6%">DATA DO<br>INGRESSO<br>CONTÁBIL</th>
                    <th width="6%">INSCRIÇÃO<br>GENÉRICA</th>
                    <th width="15%">ENDEREÇO</th>
                    <th width="4%">ÁREA<br>TOTAL</th>
                    <th width="4%">ÁREA<br>CONSTRUÍDA</th>
                    <th width="4%">VIDA ÚTIL<br>(MESES)</th>
                    <th width="6%">VALOR<br>HISTÓRICO</th>
                    <th width="6%">DATA DA<br>ÚLTIMA<br>REAVALIAÇÃO</th>
                    <th width="7%">VALOR<br>ATUALIZADO</th>
                </tr>

                @php 
                    $tValHistorico = 0; 
                    $tValAtualizado = 0; 
                @endphp

                @foreach ($itens as $linha)
                    @php 
                        $tValHistorico += $linha->valor_historico_1a_avaliacao;
                        $tValAtualizado += $linha->valor_atualizado;
                    @endphp
                    <tr>
                        <td>{{ $linha->num_registro }}</td>
                        <td>{{ $linha->denominacao }}</td>
                        <td>{!! $linha->datas_concat !!}</td>
                        <td>&nbsp;</td> <td>{{ $linha->estado_conservacao }}</td>
                        <td>{{ $linha->conta_contabil }}</td>
                        <td>{{ $linha->data_ingresso_contabil ? \Carbon\Carbon::parse($linha->data_ingresso_contabil)->format('d/m/Y') : '' }}</td>
                        <td>{{ $linha->inscricao_generica }}</td>
                        <td>{{ $linha->endereco }}</td>
                        <td>{{ $linha->area }}</td>
                        <td>{{ $linha->area_edificacao }}</td>
                        <td>{{ $linha->vida_util }}</td>
                        <td style="text-align: right;">{{ number_format($linha->valor_historico_1a_avaliacao, 2, ',', '.') }}</td>
                        <td>{{ $linha->data_reavaliacao ? \Carbon\Carbon::parse($linha->data_reavaliacao)->format('d/m/Y') : '' }}</td>
                        <td style="text-align: right;">{{ number_format($linha->valor_atualizado, 2, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="12" style="text-align: right; font-weight: bold;">TOTAL</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tValHistorico, 2, ',', '.') }}</td>
                    <td></td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($tValAtualizado, 2, ',', '.') }}</td>
                </tr>
            </table>

            @if(!$loop->last)
                <div style="margin-top: 40px; page-break-inside: avoid;">
                    <table style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 5px;">
                        <tr>
                            <td width="10%"><img src="{{ asset('images/brasao-tjes.png') }}" width="60" alt="Brasão"></td>
                            <td width="70%" style="padding-left: 10px;">
                                <div style="font-weight: bold; font-size: 14px;">TRIBUNAL DE JUSTIÇA DO ESTADO ES</div><br>
                                <div style="font-weight: bold; font-size: 12px;">Relatório dos Bens Imóveis</div>
                            </td>
                            <td width="20%" style="text-align: right; vertical-align: top; font-weight: bold; font-size: 12px;">
                                {{ $data_emissao }}<br><br>Seção de Patrimônio
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

        </div>
        @php $isFirst = false; @endphp
    @endforeach

@endsection