@extends('egap.relatorios.layout-tce')

@section('titulo_pagina', 'Quantidade de Materiais por Unidade Judiciária')

@section('tabela')
    <style>
        /* Esconder o cabeçalho clássico com o brasão do layout base */
        table[width="100%"] { display: none; }
        
        .relatorio-container { 
            display: block !important; 
            width: 100%; 
            font-family: Arial, Helvetica, sans-serif; 
        }
        
        h1 { font-size: 22px; font-weight: bold; margin-bottom: 20px; color: #333; }
        h3 { font-size: 15px; font-weight: bold; margin-top: 35px; margin-bottom: 10px; text-transform: uppercase; color: #000; }
        
        .tabela-limpa { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 12px; 
            margin-bottom: 20px;
        }
        .tabela-limpa th { 
            border-bottom: 2px solid #ddd; 
            padding: 8px; 
            text-align: left; 
            font-weight: bold; 
            color: #333;
        }
        .tabela-limpa td { 
            border-top: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
            color: #555;
        }
        
        /* Efeito zebrado tipo Bootstrap */
        .tabela-limpa tbody tr:nth-child(odd) { 
            background-color: #f9f9f9; 
        }
        
        .text-center { text-align: center !important; }
    </style>

    <div class="relatorio-container">
        <h1>Quantidade de Materiais por Unidade Judiciária</h1>

        @forelse($dadosAgrupados as $unidade => $itens)
            <h3>{{ $unidade }}</h3>
            <table class="tabela-limpa">
                <thead>
                    <tr>
                        <th width="35%">Setor</th>
                        <th width="45%">Material</th>
                        <th width="10%" class="text-center">Solicitado</th>
                        <th width="10%" class="text-center">Atendido</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($itens as $linha)
                        <tr>
                            <td>{{ $linha->Setor }}</td>
                            <td>{{ $linha->descricao_detalhada }}</td>
                            <td class="text-center">{{ number_format($linha->solicitado ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($linha->atendido ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @empty
            <p style="padding: 20px; text-align: center; font-family: Arial;">Nenhum material solicitado encontrado para os filtros informados.</p>
        @endforelse
    </div>
@endsection