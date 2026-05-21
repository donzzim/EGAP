@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Relatório de Bens Permanentes')

@section('tabela')
    <style>
        .tabela-grid { width: 100%; border-collapse: collapse; font-family: Verdana, sans-serif; font-size: 11px; margin-bottom: 20px; }
        .tabela-grid th, .tabela-grid td { border: 1px solid #000 !important; padding: 6px; vertical-align: middle; }
        .linha-cabecalho th { font-weight: bold; text-transform: uppercase; text-align: center; }
        
        .caixa-titulo { border: 1px solid #000 !important; text-align: center; font-weight: bold; font-size: 16px; padding: 6px; font-family: Verdana, sans-serif; margin-bottom: 15px;}
    </style>

    <table style="width: 100%; font-family: Verdana, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding-bottom: 5px;">
        <tr>
            <td width="50%" align="left">Relatório de Bens Permanentes</td>
            <td width="50%" align="right">Seção de Patrimônio</td>
        </tr>
    </table>

    <div class="caixa-titulo">
        BENS PERMANENTES SEM TERMO DE RESPONSABILIDADE VÁLIDO VINCULADO
    </div>

    <table class="tabela-grid">
        <tr class="linha-cabecalho">
            <th width="4%">ITEM</th>
            <th width="8%">PATRIM.</th>
            <th width="20%">DESCRIÇÃO DO BEM</th>
            <th width="25%">UNIDADE ORGANIZACIONAL</th>
            <th width="15%">SETOR</th>
            <th width="10%">TERMO NO.<br>SITUAÇÃO</th>
            <th width="18%">OBSERVAÇÃO</th>
        </tr>

        @php $seq = 1; @endphp

        @forelse ($dados as $linha)
            <tr>
                <td style="text-align: center; font-weight: bold;">{{ $seq++ }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $linha->patrimonio }}</td>
                <td style="text-align: left; font-weight: bold; text-transform: uppercase;">{{ $linha->descricao }}</td>
                <td style="text-align: left; font-weight: bold; text-transform: uppercase;">{{ $linha->unidade }}</td>
                <td style="text-align: left; font-weight: bold; text-transform: uppercase;">{{ $linha->setor }}</td>
                <td style="text-align: center; font-weight: bold; text-transform: uppercase;">
                    {{ $linha->termo_completo }}<br>{{ $linha->situacao_desc }}
                </td>
                <td style="text-align: left; font-weight: bold; text-transform: uppercase;">{{ $linha->observacao }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">Nenhum bem permanente encontrado nestas condições.</td>
            </tr>
        @endforelse
    </table>

@endsection