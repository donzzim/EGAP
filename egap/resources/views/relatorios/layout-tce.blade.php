<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('titulo_pagina', 'Relatório TCE IN 34')</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 4px 6px; }
        th { font-weight: bold; text-align: left; background-color: #f9f9f9; text-transform: uppercase; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header-title { font-size: 16px; font-weight: bold; text-align: center; }
        .no-border-bottom { border-bottom: none; }
        .no-border-top { border-top: none; }
    </style>
</head>
<body>

    <table>
        <tr>
            <td width="10%" class="text-center" rowspan="2">
                <img src="/images/brasao.png" alt="Brasão" height="50">
            </td>
            <td width="70%"><b>TRIBUNAL DE JUSTIÇA DO ESTADO ES</b></td>
            <td width="20%" class="text-right"><b>{{ $data_emissao }}</b></td>
        </tr>
        <tr>
            <td>Relatório do TCE-IN 34 - Bens Patrimoniais</td>
            <td class="text-right">Seção de Patrimônio</td>
        </tr>
        <tr>
            <td colspan="3" class="header-title">RELATÓRIO DO TCE-IN 34</td>
        </tr>
    </table>

    @isset($filtros['data_inicio'], $filtros['data_termino'])
        <table>
            <tr>
                <th width="10%">PERÍODO</th>
                <td>
                    {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} até 
                    {{ \Carbon\Carbon::parse($filtros['data_termino'])->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    @endisset

    @yield('tabela')

</body>
</html>