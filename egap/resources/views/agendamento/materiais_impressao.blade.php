<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relação de Materiais - Transporte #{{ $solicitacao->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; background: #525659; display: flex; justify-content: center; }
        .page { background: white; width: 210mm; min-height: 297mm; padding: 15mm; box-sizing: border-box; box-shadow: 0 0 10px rgba(0,0,0,0.5); }
        .header-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 20px; }
        .header-table td { padding: 5px; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; }
        .declaration { text-align: justify; margin-bottom: 15px; font-size: 12px; }
        .info-table, .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td, .info-table th, .items-table td, .items-table th { border: 1px solid #000; padding: 5px; text-align: left; }
        .items-table th { text-align: center; font-weight: bold; }
        .items-table td { text-align: center; }
        .items-table td.desc { text-align: left; }
        .signatures { width: 100%; margin-top: 50px; text-align: center; }
        .signatures td { width: 33.33%; vertical-align: top; padding: 0 20px; }
        .sign-line { border-top: 1px solid #000; margin-bottom: 2px; }
        .sign-box { border: 1px solid #ccc; height: 80px; width: 80%; margin: 10px auto; }
        .footer { border-top: 1px solid #ccc; margin-top: 40px; padding-top: 10px; font-size: 10px; }
        .empty-state { text-align: center; padding: 20px; font-style: italic; }

        @media print {
            body { background: white; padding: 0; display: block; }
            .page { width: 100%; min-height: auto; padding: 0; box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="page">
        <table class="header-table">
            <tr>
                <td style="width: 80px; text-align: center;">
                    @if(file_exists(public_path('images/brasao.png')))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/brasao.png'))) }}" style="width: 60px;">
                    @endif
                </td>
                <td style="vertical-align: top;">
                    <b>TRIBUNAL DE JUSTIÇA DO ESTADO ES</b><br><br><br>
                    Relação de Materiais para Transporte
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    <b>Setor de Agendamento</b>
                </td>
            </tr>
        </table>

        <div class="title">RELAÇÃO DE MATERIAIS - TRANSPORTE Nº {{ $solicitacao->id }}</div>

        <div class="declaration">
            Declaro pelo presente documento de responsabilidade que os materiais especificados abaixo estão devidamente
            embarcados para o transporte solicitado. O transportador se compromete a zelar pelo bom uso e guarda dos
            mesmos até a efetivação da entrega, bem como informar eventual defeito ou avaria, sob pena de responsabilização.
        </div>

        <table class="info-table">
            <tr><td style="width: 140px; font-weight: bold;">SOLICITANTE</td><td>{{ mb_strtoupper($solicitacao->idSolicitanteRef?->name ?? 'NÃO INFORMADO') }}</td></tr>
            <tr><td style="font-weight: bold;">UNIDADE</td><td>{{ mb_strtoupper($unidade) }}</td></tr>
            <tr><td style="font-weight: bold;">SETOR</td><td>{{ mb_strtoupper($setor) }}</td></tr>
            @if($pedidoProtocolo)
                <tr><td style="font-weight: bold;">PEDIDO Nº</td><td>{{ $pedidoProtocolo }}</td></tr>
            @endif
            <tr><td style="font-weight: bold;">LOCAL DE SAÍDA</td><td>{{ mb_strtoupper($solicitacao->local_saida ?: 'NÃO INFORMADO') }}</td></tr>
            <tr><td style="font-weight: bold;">LOCAL DE DESTINO</td><td>{{ mb_strtoupper($solicitacao->local_destino ?: 'NÃO INFORMADO') }}</td></tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;">ITEM</th>
                    <th style="width: 70px;">PATRIMÔNIO</th>
                    <th style="text-align: left;">DESCRIÇÃO DO BEM/MARCA/MODELO</th>
                    <th style="width: 90px;">SÉRIE</th>
                    <th style="width: 70px;">SITUAÇÃO</th>
                    <th style="width: 50px;">ANDAR</th>
                    <th style="width: 70px;">TERMO</th>
                </tr>
            </thead>
            <tbody>
                @forelse($itens as $index => $bem)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $bem->NumPatrimonio }}</td>
                    <td class="desc">
                        {{ $bem->Descricao }}

                        @if($bem->marca_desc)
                            , Marca: {{ $bem->marca_desc }}
                        @endif

                        @if($bem->modelo_desc)
                            , Modelo: {{ $bem->modelo_desc }}
                        @endif
                    </td>
                    <td>{{ $bem->NumerodeSerie ?: '-' }}</td>
                    <td>{{ mb_strtoupper($bem->EstadodeConservacao ?? 'BOM') }}</td>
                    <td>{{ $bem->andar_atual ?: '-' }}</td>
                    <td>{{ $bem->termo_completo }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">Nenhum material vinculado a esta solicitação.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <table class="signatures">
            <tr>
                <td>
                    <div class="sign-line" style="width: 90%; margin: 0 auto 2px;">Setor (Emitente)</div>
                    <div>Carimbo e Assinatura</div>
                    <div class="sign-box"></div>
                </td>
                <td>
                    <div class="sign-line" style="width: 90%; margin: 0 auto 2px;">Setor (Destinatário)</div>
                    <div>Carimbo e Assinatura</div>
                    <div class="sign-box"></div>
                    <div style="text-align: center; margin-top: 5px;">Recebido em ____/____/______.</div>
                </td>
                <td>
                    <div class="sign-line" style="width: 90%; margin: 0 auto 2px;">Motorista</div>
                    <div>Assinatura</div>
                    <div class="sign-box"></div>
                    <div style="text-align: center; margin-top: 5px;">Embarcado em ____/____/______.</div>
                </td>
            </tr>
        </table>

        <div class="footer">
            Documento gerado em {{ $dataEmissao }}.<br>
            Os itens constantes nesta relação foram devidamente conferidos e embarcados, sendo sua guarda de
            responsabilidade do transportador até a efetivação da entrega.
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
