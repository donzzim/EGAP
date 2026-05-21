@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Pedido de Materiais')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 10px; margin-bottom: 20px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 4px; vertical-align: middle; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; font-size: 9px; }
        
        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 14px; padding: 6px; font-family: Verdana, sans-serif; margin-bottom: 15px;}
        .texto-justificativa { font-size: 9px; line-height: 1.2; }
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Pedido de Materiais</td>
            <td width="50%" align="right">Seção de Patrimônio</td>
        </tr>
    </table>

    <div class="caixa-titulo">
        RELATÓRIO DE PEDIDOS DE MATERIAIS
    </div>

    <table class="tabela-grid">
        <tr class="linha-cabecalho">
            <th width="3%"># <input type="checkbox" disabled></th>
            <th width="15%">UNIDADE/SETOR</th>
            <th width="15%">DESCRIÇÃO</th>
            <th width="20%">JUSTIFICATIVA DO REQUISITANTE</th>
            <th width="8%">PEDIDO<br>PROTOCOLO<br>DATA</th>
            <th width="5%">QTDE<br>SOLICITADA</th>
            <th width="5%">QTDE<br>VALIDADA</th>
            <th width="5%">QTDE JÁ<br>ATENDIDA</th>
            <th width="5%">QTDE A<br>SER<br>ATENDIDA</th>
            <th width="9%">OBSERVAÇÃO</th>
            <th width="5%">VALIDADO<br>EM</th>
            <th width="5%">SITUAÇÃO</th>
        </tr>

        @php $seq = 1; @endphp

        @forelse ($dados as $linha)
            <tr>
                <td style="text-align: center;">{{ $seq++ }} <input type="checkbox"></td>
                <td style="text-align: left; text-transform: uppercase;">{{ $linha->setor_nome }}</td>
                <td style="text-align: left; text-transform: uppercase;">{{ $linha->descricao_material }}</td>
                <td style="text-align: left;" class="texto-justificativa">{{ $linha->justificativa }}</td>
                <td style="text-align: center;">
                    <b>{{ $linha->pedido_id }}/{{ \Carbon\Carbon::parse($linha->date_time)->format('Y') }}</b><br>
                    {{ $linha->data_protocolo_formatada }}
                </td>
                <td style="text-align: center;">{{ $linha->qtde_solicitada }}</td>
                <td style="text-align: center;">{{ $linha->qtde_validada }}</td>
                <td style="text-align: center;">{{ $linha->qtde_atendida }}</td>
                <td style="text-align: center;">{{ $linha->qtde_a_ser_atendida }}</td>
                <td style="text-align: left; font-size: 9px;">{{ $linha->observacao }}</td>
                <td style="text-align: center;">{{ $linha->validado_em_formatada }}</td>
                <td style="text-align: center;">{{ $linha->situacao_desc }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="12" style="text-align: center; padding: 20px;">Nenhum registro encontrado para os filtros informados.</td>
            </tr>
        @endforelse
    </table>

@endsection