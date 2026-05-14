<?php
$end = $_SERVER['SERVER_ADDR'];
$url = $_SERVER['SCRIPT_NAME'];
$pag = basename(__FILE__);
$pos = strpos($url, $pag);
if ($pos) {
    $param = str_replace('.php', '', $pag);
    $pathurl = 'http://' . $end . str_replace($pag, 'index.php?p=' . $param, $url);
    header('Location: ' . $pathurl);
}

// error_reporting(E_ALL);
// ini_set('display_errors','On');




$id_inventario        = isset($_SESSION['idinventario']) ? $_SESSION['idinventario'] : ultimoInventarioFinalizado()[0]['id'];
$statusInventario  = isset($_SESSION['inventarioonline']) ? "Em execução" : "Finalizado";
$cod_setor           = $_SESSION['codigosetor'];
$cod_usuario        = $_SESSION['codigousuario'];
$optionComplemento = '';


$stmt = $conn->query("SELECT DISTINCT p.id, p.NumPatrimonio, (Select COUNT(id)  from mat_patrimonio  WHERE SituacaoBem IN(1, 7) AND Setor = {$_SESSION['codigosetor']} ) total_bens, p.NumerodePatAnterior, dd.descricao_resumida, p.Descricao, ma.Descricao Marca, mo.descricao Modelo, p.AndarSetor, p.EstadodeConservacao, p.NumerodeSerie, dr.imagem, com.descricao ComplementoSetor, p.acuracia acuracia, p.SituacaoBem FROM mat_patrimonio p LEFT OUTER JOIN mat_descricaoresumida dr ON p.DescricaoResumidadoBem = dr.id LEFT OUTER JOIN mat_descricaodetalhada dd ON p.id_descricaodetalhada = dd.id LEFT OUTER JOIN mat_marca ma ON p.Marca = ma.id LEFT OUTER JOIN mat_modelo mo ON p.Modelo = mo.id LEFT OUTER JOIN mat_complementosetor com ON p.ComplementoSetor = com.id WHERE p.SituacaoBem IN (1, 7, 8) AND p.Setor = {$_SESSION['codigosetor']} ORDER BY p.Descricao, ma.Descricao, mo.descricao, p.NumPatrimonio ");
$stmt->execute();
$bens = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = $bens[0]['total_bens'];

function marcas()
{
    global $conn;
    $stmt1 = $conn->query("SELECT max(id) as id, descricao FROM mat_marca WHERE descricao <> '' group by descricao ORDER BY descricao;");
    return $stmt1->fetchAll(PDO::FETCH_ASSOC);
}

function modelos()
{
    global $conn;
    $stmt2 = $conn->query("SELECT DISTINCT max(id) as id, descricao FROM mat_modelo group by descricao ORDER BY descricao");
    return $stmt2->fetchAll(PDO::FETCH_ASSOC);
}


function descricoes()
{
    global $conn;
    $stmt4 = $conn->query("SELECT max(id) as id, descricao_detalhada FROM mat_descricaodetalhada WHERE descricao_detalhada <> '' group by descricao_detalhada ORDER BY descricao_detalhada");
    return $stmt4->fetchAll(PDO::FETCH_ASSOC);
}

function verificaInventarioSetor()
{
    global $conn;
    global $cod_setor;
    global $id_inventario;

    $stmt     = $conn->query("SELECT situacao 
	FROM inv_atividades
	WHERE id_inventario = $id_inventario AND setor = $cod_setor");
    $inv = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($inv)) {
        $response = $inv[0]['situacao'];
    } else {
        $response = 'Aberto';
    }
    return $response;
}

/*****************************************************************************************************************************************/
//SQL PRINCIPAL 

$sqlPrincipal = ('SELECT p.id, p.NumPatrimonio, ma.id as "id_marca", mo.id as "id_modelo", com.id as "id_comp", p.NumerodePatAnterior, dd.descricao_resumida, p.Descricao, ma.Descricao Marca, 
mo.descricao Modelo, p.AndarSetor, p.EstadodeConservacao, p.NumerodeSerie, dr.imagem, com.descricao ComplementoSetor, p.acuracia acuracia, p.SituacaoBem, p.id_inventario, 
p.sit_inventario, p.DataCadastro, "" atualizado_por, "" arquivo_digital, "" imagem_enviada, "" termo
FROM mat_patrimonio p 
INNER JOIN mat_descricaoresumida dr ON p.DescricaoResumidadoBem = dr.id 
INNER JOIN mat_descricaodetalhada dd ON p.id_descricaodetalhada = dd.id 
LEFT OUTER JOIN mat_marca ma ON p.Marca = ma.id 
LEFT OUTER JOIN mat_modelo mo ON p.Modelo = mo.id 
LEFT OUTER JOIN mat_complementosetor com ON p.ComplementoSetor = com.id 
WHERE  p.SituacaoBem IN (1, 7, 8) AND p.Setor =  ' . $cod_setor . '  AND 
	p.id NOT IN (SELECT IFNULL(iv.id_bem,0) FROM mat_itensinventario iv WHERE iv.id_inventario =   ' . $id_inventario . '    AND iv.setor = ' . $cod_setor . ' )');

$stmtPrincipal                 = $conn->query($sqlPrincipal);
$bens_inv                     = $stmtPrincipal->fetchAll(PDO::FETCH_ASSOC);
$total_bens                 = count($bens_inv);

//SQL PRINCIPAL

$count_inv = 0;
$count_bem = array();
$cad_manual = 0;
foreach ($bens_inv as $bem) {

    $stmtMov = $conn->query("SELECT CONCAT_WS('/',ter.num_termo, ter.ano_termo) termo, tra.SetorAtual, tra.SetorAnterior, 
    IF(arq.date_time > '2017-07-01', arq.situacao, 1) situacao, IFNULL((SELECT COUNT(*) FROM mat_transferencia t INNER JOIN mat_arquivodigital a ON t.termo = a.termo 
    WHERE a.situacao=1 AND t.NumPatrimonio = tra.NumPatrimonio),0) termosvalidos, IF(arq.arquivo_digital IS NULL, 'Sem termo','Com termo') arquivo, arq.situacao 
    AS termoSituacao FROM mat_transferencia tra 
    INNER JOIN mat_termos ter ON tra.Termo = ter.id 
    INNER JOIN mat_arquivodigital arq ON arq.termo = tra.Termo 
    WHERE tra.NumPatrimonio = " . $bem['id'] . " ORDER BY tra.id DESC ");
    $stmtMov->execute();
    $movim = $stmtMov->fetchAll(PDO::FETCH_NUM);
    if ($movim) {
        $termo = $movim[0][0];
        $termosvalidos = $movim[0][4];
    }else{
        $termosvalidos = 0;
    }
    if ($bem['SituacaoBem'] == 8 && $termosvalidos == 0) {
        $cad_manual = 1;
    }
    if (utf8_encode($bem['sit_inventario']) == 'A INVENTARIAR' && $cad_manual == 0) {
        $count_inv++;
    } else if (utf8_encode($bem['sit_inventario']) == 'EM TRANSFERÊNCIA') {
        if ($bem['SituacaoBem'] != 7) {
            $count_inv++;
        }
    }
}

?>
<style>
    @media print {

        /* body * {
        visibility: hidden ;
    } */
        .panel-heading {
            display: none
        }

        table,
        th,
        td {
            border: 1px solid;
            border-bottom-color: #333 !important;
            /* width: 100%; */
            height: 100%;
            zoom: 75%;
        }


        #bensmateriais {
            visibility: visible;
            border: 1px solid !important;
            /* width: 100%; */
        }
        .select2-container--open {
            z-index: 9999;
        }

        .material-print {
            width: 250px;
            text-align: center;
        }

        .panel {
            border-color: white;
        }

        .select-print {
            display: none
        }

        #header {
            display: none
        }

        #menu {
            display: none
        }

        .print {
            display: none
        }

        .obs {

            table,
            th,
            td {
                border: 1px solid !important;
                border-bottom-color: #333 !important;
                /* width: 100%; */
                height: 100%;
            }

            display: block;
            border: 1px solid;
            border-top-color: #333;
        }

        .pat-print {
            display: block;
        }

        #footer-opacity {
            display: none
        }

        .foto-pat {
            display: none;
        }

        #back-to-top {
            display: none
        }

        .table-striped {
            background-color: black !important;
            border-bottom-color: #333 !important;
        }
    }

    /* Tooltip Configs*/
    [data-tooltip] {
        display: inline-block;
        position: relative;
        padding: 4px;
    }

    /* Tooltip styling */
    [data-tooltip]:before {
        content: attr(data-tooltip);
        display: none;
        position: absolute;
        background: gray;
        color: #fff;
        padding: 4px 8px;
        font-size: 10px;
        line-height: 1;
        min-width: 70px;
        text-align: center;
        border-radius: 4px;
    }

    /* Dynamic horizontal centering */
    [data-tooltip-position="top"]:before,
    [data-tooltip-position="bottom"]:before {
        left: 50%;
        -ms-transform: translateX(-50%);
        -moz-transform: translateX(-50%);
        -webkit-transform: translateX(-50%);
        transform: translateX(-50%);
    }

    /* Dynamic vertical centering */
    [data-tooltip-position="right"]:before,
    [data-tooltip-position="left"]:before {
        top: 50%;
        -ms-transform: translateY(-50%);
        -moz-transform: translateY(-50%);
        -webkit-transform: translateY(-50%);
        transform: translateY(-50%);
    }

    [data-tooltip-position="top"]:before {
        bottom: 100%;
        margin-bottom: 6px;
    }

    [data-tooltip-position="right"]:before {
        left: 100%;
        margin-left: 6px;
    }

    [data-tooltip-position="bottom"]:before {
        top: 100%;
        margin-top: 6px;
    }

    [data-tooltip-position="left"]:before {
        right: 100%;
        margin-right: 6px;
    }

    /* Tooltip arrow styling/placement */
    [data-tooltip]:after {
        content: '';
        display: none;
        position: absolute;
        color: gray;
        width: 0;
        height: 0;
        border-color: transparent;
        border-style: solid;
    }

    /* Dynamic horizontal centering for the tooltip */
    [data-tooltip-position="top"]:after,
    [data-tooltip-position="bottom"]:after {
        left: 50%;
        margin-left: -6px;
    }

    /* Dynamic vertical centering for the tooltip */
    [data-tooltip-position="right"]:after,
    [data-tooltip-position="left"]:after {
        top: 50%;
        margin-top: -6px;
    }

    [data-tooltip-position="top"]:after {
        bottom: 100%;
        border-width: 6px 6px 0;
        border-top-color: #000;
    }

    [data-tooltip-position="right"]:after {
        left: 100%;
        border-width: 6px 6px 6px 0;
        border-right-color: #000;
    }

    [data-tooltip-position="bottom"]:after {
        top: 100%;
        border-width: 0 6px 6px;
        border-bottom-color: #000;
    }

    [data-tooltip-position="left"]:after {
        right: 100%;
        border-width: 6px 0 6px 6px;
        border-left-color: #000;
    }

    /* Show the tooltip when hovering */
    [data-tooltip]:hover:before,
    [data-tooltip]:hover:after {
        display: block;
        z-index: 50;
    }

    /* Tooltip configs */

    .btn-rodape {
        height: 32.85px;
        width: 170px;
    }

    #bensLocalizar {
        height: 32.85px;
        width: 198px;
    }

    #btn-confirmaInventario:hover {
        cursor: pointer;
    }

    .failure {
        color: #a94442;
        background-color: #f2dede;
        border-color: #ebccd1;
        display: none;
    }

    .alert-box {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    .margin {
        margin: 0px 5px;
    }

    .margin-top {
        margin: 1em 0em;
    }

    .margin-side {
        margin: 0em 1em;
    }

    #footer-opacity {
        background: transparent;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        border-top: none;

        padding: 3px 0 33px 0;
    }

    #bens-localizar {
        margin-right: 1em;
    }


    #back-to-top {
        position: fixed;
        bottom: 40px;
        right: 40px;
        z-index: 9999;
        width: 42px;
        height: 38px;
        text-align: center;
        line-height: 30px;
        background: #000000;
        color: #fff;
        cursor: pointer;
        border: 0;
        border-radius: 2px;
        text-decoration: none;
        transition: opacity 0.2s ease-out;
        opacity: 0;
    }

    #back-to-top:hover {
        background: #000000;
        width: 45px;
        height: 40px;
        opacity: 1;
    }

    #back-to-top.show {
        opacity: 0.7;
    }

    .focus-input {
        outline: 1px solid #71a4f7;
    }

    .loader {
        border: 4px solid #f3f3f3;
        border-radius: 50%;
        border-top: 4px solid #3498db;
        width: 30px;
        height: 30px;
        -webkit-animation: spin 2s linear infinite;
        /* Safari */
        animation: spin 2s linear infinite;
    }

    .modal {
        overflow-y: auto;
    }

    .arquivo:hover {
        background: #00008B;
        cursor: pointer;
    }

    .arquivo-sign {
        vertical-align: bottom !important;
        background-color: white !important;
        border: none;
        color: black !important;


    }

    .arquivo-sign:hover {
        background-color: #2F4F4F !important;
        cursor: pointer;
        color: white !important;
        border-style: solid !important;
        border-width: 2px !important;
    }

    .main-comp {
        display: flex;
        /* justify-content: space-between;  */
        align-items: center;
    }

    .edit {

        border-radius: 4px !important;
        height: 34px !important;
        border: 1px solid #aaa;
    }

    .pat-novo {

        border-radius: 4px !important;
        height: 34px !important;
        border: 1px solid #aaa;
    }

    .select2-selection {
        height: 34px !important;

    }

    [aria-labelledby="select2-filter_complemento-container"] {
        height: 30px !important;
        /* width           : 173px !important;   */
        border-radius: 0px !important;
    }

    #complementosetor {
        border-top-right-radius: 4px !important;
        border-bottom-right-radius: 4px !important;
    }

    [aria-labelledby='select2-complemento_setor_m_e-container'] {
        border-top-right-radius: 0px !important;
        border-bottom-right-radius: 0px !important;
    }

    .hide-comp {
        display: none;
    }

    .show-comp {
        display: table-row;
    }

    @media screen {

        .obs {
            display: none;
            /* width: 100%; */
        }

        .pat-print {
            display: none;
        }

    }

    /* Safari */
    @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<div class="col-md-12">

    <div class="panel panel-primary" id="all">
        <div class="panel-heading"><i class="fas fa-th-large"></i> Relação de Bens no Setor</div>
        <div class="panel-body">
            <div id="mensagem"></div>

            <!-- <form class="form-inline" name="form_filter" id="form_filter"  > -->
            <input type="hidden" name="p" id="p" value="bens" />
            <div class="row print form-inline">
                <form action="" id="formulario-pesquisa">
                    <div class="form-group" style="float: left;">
                        <input type="text" style="margin-left: 10px;" class="form-control input-sm" name="pesquisa" id="pesquisa-form" placeholder="Pesquisar bens" autocomplete="new-password" />
                </form>
                <select class="form-control filter" style="width: 200px; height: 30px !important; border-right-left" id="filter_complemento" name="filter_complemento">
                    <option value="0" selected disabled>Filtrar por complemento do setor</option>
                </select>
                <button type="button" id="btn-limpar" name="btn-limpar" class="btn btn-primary btn-sm" style="margin-left: ; height: 26px; width: 68px;" data-tooltip="Limpe a barra de pesquisa." data-tooltip-position="top"><i class="fas fa-trash-alt"></i> Limpar</button>
            </div>

            <div>
                <!--<a href="./api/imprimir_bens.api.php" target="_blank">--><button type="button" class="btn btn-primary" id="btnImprimir" onclick="printPage();" style="height: 26px; margin-left: 15px; font-size: small; margin-top: 2px;" data-tooltip="Imprima os bens consultados" data-tooltip-position="top">
                    <i class="fas fa-print"></i> Imprimir </button></a>
                <a href="./api/imprimir_bens.api.php?csv=on" target="_blank"><button type="button" class="btn btn-primary" id="btnImprimir" style="height: 26px; font-size: small; margin-top: 2px;" data-tooltip="Exporte os bens em planilha" data-tooltip-position="top">
                        <i class="fas fa-file-excel"></i> Exportar</button></a>
                <div class="form-group" style="margin-right: 10px ;float: right; margin-top: 7px;">
                    <div value="<?php echo $total; ?>">Total de Bens: <span class="badge badge-info" id="total-bens" style="display:initial;">0
                        </span> Total de bens selecionados <span class="badge badge-info" id="total-bens-selecionados">0</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- </form> -->
        <br>


        <table id="bensmateriais" class="table table-striped inv-table" style="table-layout: ; width:100%;">
            <thead>
                <tr>
                    <th style="width: 100px;" class="text-center">Patrimônio<br /> (cód. barras)</th>
                    <th style="width: 100px;" class="text-center print">Patrimônio<br />Antigo</th>
                    <th style="width: 250px;" class="material-print">Material</th>
                    <th style="width: 225px;">
                        <center>Complemento do<br /> Setor<center>
                    </th>
                    <?php if ($statusInventario != 'Finalizado') {
                        if (verificaInventarioSetor() != 'Finalizado') {
                            echo "<th class='inv' style='width: 200px;'><center>Situação<br>Inventário</center></th>";
                        }
                    } ?>
                    <th class="" style="width: 145px;">
                        <center>Termo</center>
                    </th>
                    <th class="print" style="width: 100px;">
                        <center>Foto<br /> Ilustrativa</center>
                    </th>
                    <th class="print" style="width: 175px;">
                        <center>Selecionar todos<br />
                            <input type="checkbox" id="selectall-bens" style="transform: scale(1.3)">
                        </center>
                    </th>
                    <th class="obs" style="width: 150px; height: 65.56px;">
                        <center>Observação</center>
                    </th>
                </tr>
            </thead>
            <tbody id="materiais">

            </tbody>

        </table>
        <div class="footer-button form-inline" id="footer-opacity">
            <center>
                <!-- Confirm if user wants to transfer  -->
                <button class="btn btn-primary btn-md btn-rodape" style="width: 155px !important;" id="transferir" onclick="confirmation()" data-tooltip="Selecione os bens e clique para escolher o setor destinatário." data-tooltip-position="top"><i class="fas fa-random"></i> Transferir Bens</button>

                <!-- <button  class="btn btn-warning btn-sm"  id="btn-editar" data-toggle="modal" ><i class="fas fa-edit"></i>  Editar </button>  -->
                <button class="btn btn-info btn-md" data-toggle="modal" data-target="#modalNovo" style="width: 145px; height: 32.85px;" data-tooltip="Solicite a inlcusão de um bem" data-tooltip-position="top"><i class="fas fa-plus-circle"></i> Incluir Bem</button>

                <!-- função na pasta js ./js/script.js -->
                <button class="btn btn-danger btn-md btn-rodape" id="bensNaoLocalizar" style="width: 180px !important;" data-tooltip="Selecione os bens e clique para registrar a informação." data-tooltip-position="top" data-toggle="modal"><i class="fas fa-thumbs-down"></i> Bem Não Localizado</button>

                <button type="button" name="bensLocalizar" id="bensLocalizar" class="btn btn-success btn-md " data-toggle="modal" data-target="" data-tooltip="Selecione os bens e clique para confirmar localização ou alterar responsabilidade." data-tooltip-position="top">
                    <i class="fas fa-thumbs-up"></i> Confirmar a Localização
                </button>
                <?php if ($statusInventario != 'Finalizado' && $total > 0) {
                    if (verificaInventarioSetor() != 'Finalizado') { ?>
                        <button class="btn btn-primary btn-md inv btn-rodape" data-toggle="modal" data-target="#modalFinalizarInv" id='btn-confirmaInventario' data-tooltip="" data-tooltip-position="top" disabled><i class="fas fa-check-circle"></i> Finalizar Inventário</button>
                <?php }
                } ?>
            </center>
        </div>
    </div>
</div>
</div>
<a href="javascript:" id="return-to-top"><i class="icon-chevron-up"></i></a>
<!-- modal   -->
<div id="printThis">
    <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <!-- Modal Content: begins -->
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <div class="alert alert-danger alert-header" role="alert">
                        <center><strong>IMPORTANTE!</strong></center><br />
                        Para concluir a transferência, solicite <strong>assinatura eletrônica</strong> ao servidor recebedor dos bens (setor destinatário), caso contrário, eles permanecerão na relação de bens do setor remetente e a transferência ficará pendente no sistema.<br><br>

                        Se a retirada ocorrer pela equipe de logística, imprima este Termo e solicite o preenchimento dos campos no final do documento (identificação, assinatura e data de embarque). Em seguida, digitalize e envie <strong>o PDF, por meio do botão "anexar termo" constante no menu “Patrimônio>Movimentação de Materiais”,</strong> objetivando comprovar a retirada no setor.

                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body" id="termoHtml">

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
                    <button id="btnPrint" type="button" class="btn btn-default">Imprimir</button>
                </div>

            </div>
            <!-- Modal Content: ends -->

        </div>
    </div>
</div>

<!--EDITAR MATERIAIS-->
<div class="modal" tabindex="-1" role="dialog" id="modalEditar">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <center>
                    <h3 class="modal-title" style="font-size: 2em;">Editar patrimônio - <span id="numPat_e"></span></h3>
                </center>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                </button>
            </div>

            <div class="modal-body">
                <div class="row">

                    <span hidden id='id_bem_m_e'></span>
                    <span hidden id='num_pat_m_e'></span>
                    <span hidden id='status_pat_m_e'></span>
                    <span hidden id='sit_inv_m_e'></span>

                    <form id='formEditar'>
                        <div class='col-md-10'>
                            <div class="form-group" id="nserie">
                                <label for="num_serie_m_e">N° de série:</label>
                                <input type="text" class="form-control edit" id="num_serie_m_e">
                            </div>

                            <div name="Marca_edit" style="display:none;">
                                <label for="marca_m_e">Marca:</label>
                                <div class="input-group" id='igMarEd'>
                                    <select class="form-control edit" id="marca_m_e" name="marca" value="marca">
                                        <?php foreach (marcas() as $marca) { ?>
                                            <option value="<?php echo $marca['id']; ?>"><?php echo utf8_encode($marca['descricao']); ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="input-group-btn">
                                        <button class="btn btn-info" type="button" id='btnAddMarEd'>Novo</button>
                                    </span>
                                </div><!-- /input-group -->
                                <div class="input-group hidden" id='igMarAddEd'>
                                    <input type="text" class="form-control edit" id="marca_m_add_ed" placeholder="Adicione a marca">
                                    <span class="input-group-btn">
                                        <button class="btn btn-info" type="button" id='btnBackMarEd'>Voltar</button>
                                    </span>
                                </div><!-- /input-group -->
                            </div> <!-- Marca_edit -->

                            <!-- <br> -->
                            <div name="Modelo_edit" style="display:none;">
                                <label for="modelo_m_e">Modelo:</label>
                                <div class="input-group" id='igModEd'>
                                    <select class="form-control edit" id="modelo_m_e" name="modelo" value="modelo">
                                        <?php foreach (modelos() as $modelo) { ?>
                                            <option value="<?php echo $modelo['id']; ?>"><?php echo utf8_encode($modelo['descricao']); ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="input-group-btn">
                                        <button class="btn btn-info" type="button" id='btnAddModEd'>Novo</button>
                                    </span>
                                </div><!-- /input-group -->
                                <div class="input-group hidden" id='igModAddEd'>
                                    <input type="text" class="form-control edit" id="modelo_m_add_ed" placeholder="Adicione o modelo">
                                    <span class="input-group-btn">
                                        <button class="btn btn-info" type="button" id='btnBackModEd'>Voltar</button>
                                    </span>
                                </div><!-- /input-group -->
                            </div> <!-- Modelo_edit -->

                            <!-- Complemento-->
                            <div class="form-group">
                                <label for="complemento_setor_m_e">Complemento do setor:</label>
                                <div class="input-group" id='igCompEd'>
                                    <select class="form-control edit" style="width: 468.33px; height: 34px !important; display: none;" id="complemento_setor_m_e" name="complemento_setor" value="complemento_setor"><?php echo $optionComplemento; ?></select>
                                    <span class="input-group-btn">
                                        <button class="btn btn-info" type="button" style="height: 34px;" id='btnAddCompEd'>Novo</button>
                                    </span>
                                </div><!-- /input-group -->
                                <div class="input-group hidden" id='igCompAddEd'>
                                    <input type="text" class="form-control novo" autocomplete="new-form" id="complemento_setor_m_add_ed" style="width: 468.33px; border-top-left-radius: 4px !important; border-bottom-left-radius: 4px !important; border: 1px solid #aaa;" placeholder="Adicione o complemento de setor" value="">
                                    <span class="input-group-btn">
                                        <button class="btn btn-info" type="button" style="width: 61.77px; height: 34px; border-top-left-radius: 0px; border-bottom-left-radius: 0px;; border-top-right-radius: 4px; border-bottom-right-radius: 4px;" id='btnBackCompEd'>Voltar</button>
                                    </span>
                                </div><!-- /input-group -->
                                <!-- Complemento-->

                                <br>
                                <div class="form-group">
                                    <label for="estado_conservacao_m_e">Estado de conservação:</label>
                                    <select class="form-control  edit" id="estado_conservacao_m_e" name="estado_conservacao" value="estado_conservacao" style="border-radius: 4px;">
                                        <option value="ÓTIMO">ÓTIMO</option>
                                        <option value="BOM">BOM</option>
                                        <option value="REGULAR">REGULAR</option>
                                        <option value="RUIM">RUIM</option>
                                    </select>
                                </div>


                                <div class="form-group senha"> <!-- Senha-->
                                    <label for="senha_edit">Senha:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon" style="height: 34px !important; border: 1px solid #aaa;"><span class='glyphicon glyphicon-lock'></span></div>
                                        <input class="form-control input-sm" type="password" autocomplete="new-password" name="senha_edit" id="senha_edit" value="" style="border-top-right-radius: 4px !important; border-bottom-right-radius: 4px !important; height: 34px !important; border: 1px solid #aaa;" placeholder="Senha" />
                                    </div>
                                </div>
                            </div>
                    </form>
                    <hr>
                    <div class="alert alert-info" role="alert" style="font-size: 14px;">Ao confirmar edição, será gerado um Termo de Responsabilidade assinado eletronicamente.</div>

                </div> <!-- Fim row -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary fecharModal" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="btn-editarBem">Editar</button>
            </div>
        </div>
    </div>
</div>


</div>
</div>
</div>
<div class="modal fade" id="modalConfirmaEdit" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <center>
                    <h3 class="modal-title">Confirmar Edição do Bem</h3>
                </center>
            </div>

            <div class="modal-body">
                <div class='row'>
                    <div class='col-md-12'>
                        <p style="font-size:16px;">Para confirmar a localização do(s) bem(ns), digite sua senha unificada e clique em <strong>Confirmar</strong>!</p>
                    </div>
                    <div class='col-md-8 col-md-offset-2'>
                        <div class='form-group senha'>
                            <div class="input-group">
                                <div class="input-group-addon"><span class='glyphicon glyphicon-lock'></span></div>
                                <input class="form-control input-sm" type="password" name="senha_assinatura" id="senha_assinatura" value="" placeholder="Senha" />
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="alert alert-info" role="alert" style="font-size: 14px;">Ao confirmar a localização, será gerado um Termo de Responsabilidade assinado eletronicamente.</div>
            </div>
            <div class="modal-footer">
                <button type="button" id='dismissModal' class="btn btn-default" data-dismiss="modal">Voltar</button>
                <button type="button" class="btn btn-primary" id="btn-modalConfirma" onclick="bensLocalizar();">Confirmar</button>
            </div>
        </div>
    </div>
</div>
</div>
<!--EDITAR MATERIAIS-->

<!--NOVOS BENS-->
<div class="modal" tabindex="-1" role="dialog" id="modalNovo">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h3 class="modal-title text-center"> INCLUIR BEM</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <span id='msg_modal'> </span>
            <div class="modal-body">

                <form id="modal-novo">

                    <div class="row">

                        <div class='col-md-6'>
                            <br>
                            <input class="form-control pat-novo" type="number" id="num_pat_novo" placeholder="Nº Patrimonio*" required>
                            <br>
                            <input type="number" class="form-control pat-novo" id="num_pat_ant_novo" placeholder="Nº do Patrimônio Antigo">
                            <br>
                            <input type="text" class="form-control pat-novo" id="num_serie_novo" placeholder="Nº de série">
                            <br>
                            <input type="text" class="form-control pat-novo" id="marca_novo" placeholder="Marca">
                            <br>
                            <p style="color: red"> Os campos com * são de preenchimento obrigatório</p><br>

                            <button type="button" class="btn btn-success" id="btn-incluiBem" data-tooltip="Adicione mais um bem para inclusão" data-tooltip-position="bottom"><i class="fa fa-plus-circle" aria-hidden="true"></i> Adicionar</button><br>
                        </div>

                        <div class='col-md-6'>
                            <br>
                            <input type="text" class="form-control pat-novo" id="modelo_novo" placeholder="Modelo">

                            <br>

                            <input type="text" class="form-control pat-novo" id="complemento_novo" placeholder="Complemento do Setor*" required>

                            <br>

                            <select class="form-control pat-novo" id="estado_novo" name="estado_conservacao_novo" required>
                                <option value="" selected>Estado de conservação*</option>
                                <option value="ÓTIMO">ÓTIMO</option>
                                <option value="BOM">BOM</option>
                                <option value="REGULAR">REGULAR</option>
                                <option value="RUIM">RUIM</option>

                            </select>

                            <br>

                            <input type="text" class="form-control pat-novo" id="desc_novo" placeholder="Descrição do bem *">


                            <br>

                        </div>
                    </div> <!-- Fim row -->
                    <br>
                    <div style="display: none; font-size: 14px;" id="pat-novo-alert" class="alert alert-info" role="alert" data-setor=""></div>
                    <div class="alert alert-success" role="alert" id="alert-incluir" style="display: none"><br>
                        O(s) bem(ns)<div id="alert-lista"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="btn-fecharmodal" data-dismiss="modal">Fechar</button>
                        <button type="button" class="btn btn-primary" name="btn-novoBem" id="btn-novoBem" data-tooltip="Ao confirmar a solicitação, será enviado um e-mail para a Seção de Patrimônio com o pedido de inclusão" data-tooltip-position="top">Solicitar Inclusão</button>
                    </div>
                </form>
            </div> <!-- Fim modal body -->
        </div>
    </div>
</div>
</div>
<!--  NOVOS BENS-->

<div class="modal fade" id="modalConfirma" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <center>
                    <h3 class="modal-title">Confirmar Localização Do(s) Bem(ns)</h3>
                </center>
            </div>

            <div class="modal-body">
                <div class='row'>
                    <div class='col-md-12'>
                        <p style="font-size:16px;">Para confirmar a localização do(s) bem(ns), digite sua senha unificada e clique em <strong>Confirmar</strong>!</p>
                    </div>
                    <div class='col-md-8 col-md-offset-2'>
                        <div class='form-group senha'>
                            <div class="input-group">
                                <div class="input-group-addon"><span class='glyphicon glyphicon-lock'></span></div>
                                <input class="form-control input-sm" type="password" name="senha_assinatura" id="senha_localizar" value="" placeholder="Senha" />
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="alert alert-info" role="alert" style="font-size: 14px;">Ao confirmar a localização, será gerado um Termo de Responsabilidade assinado eletronicamente.</div>
            </div>
            <div class="modal-footer">
                <button type="button" id='dismissModal' class="btn btn-default" data-dismiss="modal" aria-label="Close" aria-hidden="true">Voltar</button>
                <button type="button" class="btn btn-primary" id="btn-modalConfirma" onclick="bensLocalizar();">Confirmar</button>
            </div>
        </div>
    </div>
</div>
</div>


<!-- CONFIRMATION modal  tabindex="-1"  -->
<div id="confirmar">
    <div id="confirmarModal" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <!-- Modal Content: begins -->
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header ">
                    <div style="display: flex;">
                        <div style="margin: auto;">
                            <h2><strong>Confirmar Transferência</strong></h2>
                        </div>
                        <button type="button" class="close text-right" style="margin-right: 5px;" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="alert alert-danger alert-header text-center" role="alert">
                    Obs.: Em caso de transferência de mais de um bem para o mesmo destinatário, selecione-os na mesma transferência.
                </div>
                <div class="modal-body" id="confirmarHtml">
                </div>


                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="transferirBensLocal()">Confirmar</button>
                    <button type="button" class="btn btn-danger" id='btnDismiss' data-dismiss="modal">Cancelar</button>
                </div>

            </div>
            <!-- Modal Content: ends -->

        </div>
    </div>
</div>

<div class="modal fade modal-nlocalizados" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="width: 750px">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <center>
                    <h3 class="modal-title" id="gridSystemModalLabel">Bens não Localizados</h3>
                </center>
            </div>
            <div class="modal-body">
                <p><i class="fa fa-info-circle fa-lg" aria-hidden="true"></i> Caso haja algum documento comprovando a transferência do(s) bem(ns), favor encaminhar para o e-mail da Seção de Patrimônio, patrimonio@tjes.jus.br.</p>
                <div class="form-group">
                    <textarea class="form-control input-sm email" id="nlocalizados-body" placeholder="Ex: Bem(ns) não localizado(s) no setor" rows="5" cols="35"></textarea>
                </div>
                <div>
                    <p id="corpo-email" style="text-align: justify;"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id='voltar' data-dismiss="modal">Voltar</button>
                <button type="button" id="send-nlocalizados" class="btn btn-primary" onclick="NaoLocalizados();" data-inv="<?php echo verificaInventarioSetor(); ?>"><i class="fa fa-share" aria-hidden="true"></i> Salvar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php
if ($statusInventario == "Em execução") {
    // Quando a situação do inventário no banco não estiver finalizada e não existir nenhum bem a inventariar no setor, exibir modal para finalização do inventário online.
    if (verificaInventarioSetor() != 'Finalizado') {
?>
        <div class="modal fade" id="modalFinalizarInv" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h3 class="modal-title text-center">Finalizar Inventário</h3>
                    </div>
                    <div id='modal-finalizar' class="modal-body">
                        <h5>Todos os bens foram inventariados. Finalize o inventário de seu setor clicando em "Finalizar"!</h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Não finalizar agora</button>
                        <button type="button" class="btn btn-primary" id="btn-modalFinalizarInv" data-dismiss="modal">Finalizar</button>
                    </div>
                </div>
            </div>
        </div>
        </div>
<?php }
} ?>


<a href="#" id="back-to-top" title="Back to top"><i class="fas fa-chevron-up"></i></a>
<iframe id="ifr" name="ifr" frameborder="0"></iframe>
<input type="text" value="1" id="statusMonitor" hidden>
<!--  Import toastr -->
<!-- <script src="./lib/toastr-master/build/toastr.min.js" type="text/javascript"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-te/1.4.0/jquery-te.min.js" integrity="sha512-813LH2NdwwzXnVfsmzSuAyyit5bRFdh997hN9Vzm0cdx3LdZV7TZNNb2Ag0dgJPD3J1Xn1Alg2YW70id+RtLrw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-te/1.4.0/jquery-te.css" integrity="sha512-YsCGey6C9bmPaAixXc6B7UwLMGW/xQOa0XfZB50ulfXIEOG25W+A2i5GxuYvTN03oX9wOmeN3T22DE/IKdEVcQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-te/1.4.0/jquery-te.js" integrity="sha512-p+t5jmGip+usIsbm05GROdslxcOlJ1N2SbM7Nm50G4VFidbMD/zkPq6g77/3GUxfCycKVwGk/IOJKnL7jptcmA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-te/1.4.0/jquery-te.min.css" integrity="sha512-i/BB4iyU9djSiXob0SEGOfX3Ld6mfBkFMMocNQV0VHmhtM9roKQuOo1wTDa9h+q9J0EjL3O3MrwrIMOoiQ6kxA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script>
    var bens_incluir = {};
    //$('#pesquisa-form').text('');
    $(document).ready(function() {
        // ===== Scroll to Top ==== 
        $("nlocalizados-body").jqte();
        toastr.options.preventDuplicates = true;

        toastr.options.preventOpenDuplicates = true;
        var texto = $('#pesquisa-form').text();

        // new Disableautofill('#teste-do-teste', {
        //     'fields': [
        //         '.pesquisa-form',  // pesquisa
        //     ],
        //     'asterisk': texto

        // });

        $('.pat-novo').keydown(function(event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });

        $("#complemento_setor_m_e").select2();
        $("#filter_complemento").select2({
            width: '173px',
        });
        $('#mySelect2').select2({
            dropdownParent: $('#confirmarModal')
        });


        //===== modal finalizar inventário =====

        var verifica_inv = "<?php echo verificaInventarioSetor(); ?>";
        var count_inv = "<?php echo $count_inv; ?>";


        // if(count_inv > 0){

        //     var msgInv = "Há bens a inventariar!";
        //     $("#btn-confirmaInventario").attr('title', msgInv);

        // }

        // if (verifica_inv == "Aberto"  && count_inv == 0){
        //     $('#modalFinalizarInv').modal('show');
        //     $('#btn-confirmaInventario').prop('disabled', false);
        //     var msgInv = "Clique para finalizar o inventário";
        //     $("#btn-confirmaInventario").attr('title', msgInv);
        // }

        // ===== fim modal finalizar inventário ===== 


        if ($('#back-to-top').length) {
            var scrollTrigger = 100, // px
                backToTop = function() {
                    var scrollTop = $(window).scrollTop();
                    if (scrollTop > scrollTrigger) {
                        $('#back-to-top').addClass('show');
                    } else {
                        $('#back-to-top').removeClass('show');
                    }
                };
            backToTop();
            $(window).on('scroll', function() {
                backToTop();
            });
            $('#back-to-top').on('click', function(e) {
                e.preventDefault();
                $('html,body').animate({
                    scrollTop: 0
                }, 700);
            });
        }


        //pesquisar

        $('#pesquisa-form').keydown(function(event) { //Previne o carregamento do input de pesquisa
            if (event.key === "Enter") {

                event.preventDefault();
            }
        })
        $('#pesquisa-form').keyup(function(event) {


            var cont = 0;
            var total = $('#total-bens').text();
            if (event.keyCode == 27 || $(this).val() == '') {
                $('#filter_complemento').val('0').trigger('change');
                $(this).val('');
                $('.inv-table tbody tr').removeClass('hide-comp').addClass('show-comp'); //.removeClass('show-comp').addClass('show-comp')
            } else {

                query = $.trim($(this).val()); //remove espaços em branco

                // query = query.replace(/ /gi, '|'); //adiciona OR for regex query

                // Itera sobre cada linha de sua tabela
                $('.inv-table tbody tr').each(function() {

                    // Verifica se alguma coluna da linha corrente possui a informação, caso não possua a linha é ocultada
                    if ($(this).text().search(new RegExp(query, "i")) < 0) {

                        $(this).removeClass('show-comp').addClass('hide-comp');
                        cont++;
                    } else {
                        if ($(this).hasClass('show-comp')) {

                            $(this).removeClass('hide-comp').addClass('show-comp');
                        }


                    }




                });
                if ($('.inv-table tbody').text().search(new RegExp(query, "i")) < 0) {

                    return toastr.error('Nenhum resultado encontrado. Limpe a pesquisa e preencha corretamente!', {
                        timeOut: 20000
                    });


                }

            }
        });

        //pesquisar


        //filtrar


        $('#filter_complemento').change(function() {
            var query = $("#filter_complemento option:selected").val().replace(/\s/g, '');
            if (query === 0) {
                $('.inv-table tbody tr').removeClass('show-comp').addClass(' show-comp');
            }

            $('.inv-table tbody tr').each(function() {
                var comp = $(this).find('td.complemento').data('comp');

                if (comp === query) {
                    $(this).removeClass('hide-comp').addClass('show-comp');
                    // if ($('#selectall-bens').is(':checked')) {
                    //     $(this).find('input').prop('checked', true);
                    // }
                } else {

                    $(this).removeClass('show-comp').addClass('hide-comp');
                    //$(this).find('input').prop('checked', false);
                }
            });


            //$('#selectall-bens').prop('checked', false);


        });

        //filtrar


        //limpar

        $("#btn-limpar").click(function() {
            $('#pesquisa-form').val('');
            $('#filter_complemento').val('0').trigger('change');
            $('.inv-table tbody tr').removeClass('hide-comp').addClass('show-comp');

        });

        //limpar

        //novos bens 

        var user = "<?php echo $_SESSION['codigousuario'] ?>";
        var setor_user = $('#select2-setor-atual-container').text();

        $('#btn-incluiBem').click(function() {

            var setor_existente = '';

            if ($('#alert-lista').text() != '') {
                $('#alert-incluir').text('').toggle();

            }

            if ($('#num_pat_novo').val() == '' || $('#complemento_novo').val() == '' || $('#estado_novo').val() == '' || $('#estado_novo').val() == null || $('#desc_novo').val() == '') {
                return toastr.error('Preencha os campos marcados com * !', {
                    timeOut: 20000
                });
            }

            if (Object.keys(bens_incluir).includes($('#num_pat_novo').val())) {
                return toastr.error('Este bem já foi adicionado a solicitação!', {
                    timeOut: 20000
                });
            }

            if (!($('#pat-novo-alert').css('display') == 'none')) {

                setor_existente = $("#pat-novo-alert").data('setor');
            }

            bens_incluir[$('#num_pat_novo').val()] = {
                'Complemento': $('#complemento_novo').val(),
                'EstadoDeConservacao': $('#estado_novo').val(),
                'Marca': $('#marca_novo').val(),
                'Modelo': $('#modelo_novo').val(),
                'NSerie': $('#num_serie_novo').val(),
                'NPatrimonioAntigo': $('#num_pat_ant_novo').val(),
                'Descricao': $('#desc_novo').val(),
                'SetorExistente': !($('#pat-novo-alert').css('display') == 'none') ? $("#pat-novo-alert").data('setor') : '',
                'CodigoUsuario': user,
                'SetorUsuario': setor_user,
            };


            $('#alert-lista').text('<span style="background-color: #4e743e; color: #ffffff;">' + $('#num_pat_novo').val() +
                '<button type="button" class="" data-dismiss="" aria-label="Close"><span aria-hidden="true">&times;</span></button></span>');
            $('#alert-incluir').show();
            $('#modalNovo').find("input").val('');
            $('#modalNovo').find("select").val('');
            $('#pat-novo-alert').hide();
            $("#pat-novo-alert").data('setor', '');

        });


        $('#num_pat_novo').blur(function() {

            var new_Numpat = $('#num_pat_novo').val();
            var form_novo = new FormData();
            form_novo.set('new_num_pat', new_Numpat);

            if (new_Numpat != '') {
                $.ajax({

                    type: 'POST',
                    url: './api/bensainventariar_operacoes.api.php',
                    processData: false,
                    contentType: false,
                    data: form_novo,
                    success: function(dados) {
                        var data = JSON.parse(dados);

                        if (dados != 0) {
                            $('#pat-novo-alert').text(data["msg"] + '. Deseja solicitar inclusão mesmo assim?').show();
                            $('#pat-novo-alert').data('setor', data["id_setor"]);

                            if (data["num_ant"] != null) {
                                $('#num_pat_ant_novo').val(data["num_ant"]);
                            }
                            if (data["num_serie"] != null) {
                                $('#num_serie_novo').val(data["num_serie"]);
                            }
                            if (data["marca"] != null) {
                                $('#marca_novo').val(data["marca"]);
                            }
                            if (data["modelo"] != null) {
                                $('#modelo_novo').val(data["modelo"]);
                            }
                            if (data["estado"] != null) {
                                $('#estado_novo').val(data["estado"]).change();
                            }
                            if (data["desc"] != null) {
                                $('#desc_novo').val(data["desc"]);
                            }

                        }

                    },
                    error: function(err) {
                        return toastr.error('Erro ao cadastar o patrimonio', {
                            timeOut: 10000
                        });
                    }
                });
            }
        });

        $('#modalNovo').on('hidden.bs.modal', function(e) {
            $(this).find("input").val('');
            $(this).find("select").val('');
            if (!($('#pat-novo-alert').css('display') == 'none')) {
                $('#pat-novo-alert').hide();
            }


        });

        $('#btn-novoBem').click(function() {

            //console.log($('#estado_novo').val());

            var new_Numpat = $('#num_pat_novo').val();
            var new_Patant = $('#num_pat_ant_novo').val();
            var new_serie = $('#num_serie_novo').val();
            var new_marca = $('#marca_novo').val();
            var new_modelo = $('#modelo_novo').val();
            var new_comp = $('#complemento_novo').val();
            var new_estado = $('#estado_novo').val();
            var new_desc = $('#desc_novo').val();


            if ((new_Numpat == '' || new_comp == '' || new_estado == '' || new_estado == null || new_desc == '') && Object.keys(bens_incluir).length == 0) {
                //console.log(Object.keys(bens_incluir).length);
                return toastr.error('Preencha os campos marcados com * !', {
                    timeOut: 20000
                });
            }



            var form_incluir;

            if (!(new_Numpat == '' || new_comp == '' || new_estado == '' || new_estado == null || new_desc == '')) {
                //console.log($('.pat-novo input, select').val());
                if (Object.keys(bens_incluir).includes(new_Numpat)) {
                    return toastr.error('Este patrimônio já está incluso na solicitação!', {
                        timeOut: 20000
                    });
                }

                if (new_Numpat == '' || new_comp == '' || new_estado == '' || new_estado == null || new_desc == '') {
                    return toastr.error('Preencha os campos marcados com * !', {
                        timeOut: 20000
                    });
                }

                bens_incluir[new_Numpat] = {
                    'Complemento': new_comp,
                    'EstadoDeConservacao': new_estado,
                    'Marca': new_marca,
                    'Modelo': new_modelo,
                    'NSerie': new_serie,
                    'NPatrimonioAntigo': new_Patant,
                    'Descricao': new_desc,
                    'SetorExistente': !($('#pat-novo-alert').css('display') == 'none') ? $("#pat-novo-alert").data('setor') : '',
                    'CodigoUsuario': user,
                    'SetorUsuario': setor_user,
                };



            }

            form_incluir = new FormData();
            form_incluir.set('incluir_pat', JSON.stringify(bens_incluir));

            //console.log(form_incluir);
            $.ajax({

                type: 'POST',
                dataType: 'text',
                url: './api/bensainventariar_operacoes.api.php',
                processData: false,
                contentType: false,
                data: form_incluir,
                success: function(dados) {
                    $('#modalNovo').modal('hide');
                    bens_incluir = {};
                    return toastr.success(dados, {
                        timeOut: 10000
                    });

                },
                error: function(err) {
                    return toastr.error('Erro ao solicitar inclusão do patrimônio', {
                        timeOut: 10000
                    });
                }
            });

        });
        //novos bens 

        $("#setornovo").change(function() {
            carregarComplementos(0);
        });
        mostrarBens();
        carregarComplementos(2);

        $('#bensNaoLocalizar').click(function() {

            if ($('input[type=checkbox]').is(":checked")) {
                $(".modal-nlocalizados").modal('show');
            } else {
                callToastr("error", "<strong>ERRO!</strong> Selecione pelo menos 1 material!");
            }
        });

        $(document).on('click', '.editar', function() {
            carregarComplementos(1);
            $('.op_ajax').remove();
            $ajustes = false;
            $('#complemento_setor_m_add_ed').val(null);

            if ($(this).data('sitbem') == 7) {
                $("#btn-editarBem").prop('disabled', 'disabled');
                return toastr.error('Não é possível editar um patrimônio em transferência!', {
                    timeOut: 10000
                });
            }

            let id_marca = $(this).data('idmarca');
            let id_modelo = $(this).data('idmodelo');
            let id_comp = $(this).data('idcomp');
            let marca = $(this).data('marca');
            let modelo = $(this).data('modelo');
            let desc = $(this).data('desc');
            let serie = $(this).data('serie');
            let estado = $(this).data('estado');
            let id_pat = $(this).data('id');
            let pat_status = $(this).data('status');
            let num_pat = $(this).data('numpat');
            let sit_inv = $(this).data('inv');
            let comp = $(this).data('comp');

            id_marca = id_marca ? id_marca : marca;
            id_modelo = id_modelo ? id_modelo : modelo;
            id_comp = id_comp ? id_comp : comp;

            $('#id_bem_m_e').val(id_pat);
            $('#num_pat_m_e').val(num_pat);
            $('#status_pat_m_e').val(pat_status);
            $('#sit_inv_m_e').val(sit_inv);

            $('#num_serie_m_e').val(serie);
            $('#numPat_e').html(num_pat);
            $('#marca_m_e').prepend('<option class="op_ajax" value="' + id_marca + '" selected="selected" >' + marca + '</option>');
            $('#modelo_m_e').prepend('<option class="op_ajax" value="' + id_modelo + '" selected="selected" >' + modelo + '</option>');
            $('#complemento_setor_m_e').prepend('<option  class="op_ajax" value="' + id_comp + '" selected="selected" >' + comp + '</option>');
            $('#estado_conservacao_m_e').prepend('<option class="op_ajax" value="' + estado + '" selected="selected" >' + estado + '</option>');

        });


        $('#formEditar').on('change paste', '#marca_m_e, #modelo_m_e, #descricao_detalhada_m_e, #descricao_detalhada_m_add_ed, #modelo_m_add_ed, #marca_m_add_ed', function() {
            $ajustes = true;
        });


        /*MODAL - EDITAR - EDITAR */
        $('#btn-editarBem').click(function() {

            if ($(this).data('sitbem') == 7) {
                $("#btn-editarBem").prop('disabled', 'disabled');
                return toastr.error('Não é possível editar um patrimônio em transferência!', {
                    timeOut: 10000
                });
            }

            let id_e = $("#id_bem_m_e").val();
            let pat_status = $("#status_pat_m_e").val();
            let sit_inv = $("#sit_inv_m_e").val();
            let numPatrimonio_e = $("#num_pat_m_e").val();
            let numSerie_e = $("#num_serie_m_e").val();
            let estadoConservacao_e = $("#estado_conservacao_m_e").val();
            //let marca_e					    = 	$("#marca_m_add_ed").val()              == '' ? $("#marca_m_e").val() : $("#marca_m_add_ed").val();
            //let modelo_e					= 	$("#modelo_m_add_ed").val()             == '' ? $("#modelo_m_e").val() : $("#modelo_m_add_ed").val();
            let complementoSetor_e = $("#complemento_setor_m_add_ed").val() != '' ? $("#complemento_setor_m_add_ed").val() : $("#complemento_setor_m_e").val();


            estadoConservacao_e = $.trim(estadoConservacao_e);
            complementoSetor_e = $.trim(complementoSetor_e);

            if (complementoSetor_e == '' || estadoConservacao_e == null) {
                return toastr.error('Não foi possível alterar os dados!', {
                    timeOut: 10000
                });
            }




            var form_data = new FormData();
            form_data.set('id_pat', id_e);
            form_data.set('pat_status', pat_status);
            form_data.set('sit_inv', sit_inv);
            form_data.set('num_pat', numPatrimonio_e);
            form_data.set('num_serie', numSerie_e);
            //form_data.set('marca', marca_e); 
            //form_data.set('modelo', modelo_e); 
            form_data.set('complemento_setor', complementoSetor_e);
            form_data.set('estado_conservacao', estadoConservacao_e);
            form_data.set('ajustes', $ajustes);

            var senha = $('#senha_edit').val();

            if (!senha) {
                callToastr("error", "<strong>ERRO!</strong> Senha é obrigatória.", "toast-bottom-full-width", 1);
                $('#senha_edit').focus();
                return false;
            }
            if (senha == '123456') {
                callToastr("error", "<strong>ERRO!</strong> Senha padrão. Favor alterar a senha.", "toast-bottom-full-width", 1);
                return false;
            }

            var materiais = id_e;

            var verificaInv = '<?php echo verificaInventarioSetor(); ?>';

            // FIRST CHECK IF PASSSWORD MATCH.
            $.ajax({
                type: 'POST',
                url: './api/assinatura.api.php',
                data: {
                    inputSenha: senha
                },
                success: function(result) {
                    $.ajax({

                        type: 'POST',
                        dataType: 'text',
                        url: './api/bensainventariar_operacoes.api.php',
                        processData: false,
                        contentType: false,
                        data: form_data,
                        success: function(dados) {

                            $.ajax({ //gera termo
                                type: "GET",
                                data: {
                                    m: materiais,
                                    verificaInv: verificaInv,
                                },
                                url: './localizar-materiais.php',

                                success: function(res) {
                                    var newWindow = window.open("", "new window", "width=900, height=800");
                                    newWindow.document.write(res);
                                    var termos = res;
                                    location.reload();
                                    return toastr.success('Patrimônio editado com sucesso!', {
                                        timeOut: 10000
                                    });

                                },
                                error: function(error) {
                                    callToastr("error", `<strong>ERRO!</strong> Há algum problema interno. Contate o administrador. ${error}`);

                                }
                            });

                        },
                        error: function(err) {

                            return toastr.error('Erro ao editar o patrimonio', {
                                timeOut: 10000
                            });
                        }
                    });
                },
                error: function(err) {
                    toastr.error("Senha incorreta. Não foi editar o patrimônio!", {
                        timeOut: 10000
                    });
                    return $('.senha').addClass("has-error");
                }
            });

        });


        $(document).on('click', '#btn-modalFinalizarInv', function() {

            $.ajax({
                type: 'POST',
                url: './api/bensainventariar_operacoes.api.php',
                data: {
                    'finalizarInv': true,
                    'verificaInv': verifica_inv
                },
                success: function() {
                    $('body').addClass("loading");

                    callToastr("success", "O inventário foi finalizado com sucesso !");
                    location.reload();

                },
                error: function(e) {
                    alert(e);
                }

            });

        });

        $(document).on('click', '#btnAddMarEd', function() {
            $('#igMarEd').hide();
            $('#marca_m_ed').val(null);
            $('#igMarAddEd').removeClass('hidden');
        });

        $(document).on('click', '#btnBackMarEd', function() {
            $('#igMarAddEd').addClass('hidden');
            $('#igMarEd').show();
            $('#marca_m_add_ed').val(null);
        });

        $(document).on('click', '#btnAddModEd', function() {
            $('#igModEd').hide();
            $('#modelo_m_ed').val(null);
            $('#igModAddEd').removeClass('hidden');
        });

        $(document).on('click', '#btnBackModEd', function() {
            $('#igModAddEd').addClass('hidden');
            $('#igModEd').show();
            $('#modelo_m_add_ed').val(null);
        });


        $(document).on('click', '#btnAddCompEd', function() {
            $('#igCompEd').hide();
            $('#complemento_setor_m_ed').val(null);
            $('#igCompAddEd').removeClass('hidden');
            $("#complemento_setor_m_add_ed").css("display", "block");
        });

        $(document).on('click', '#btnBackCompEd', function() {
            $('#igCompAddEd').addClass('hidden');
            $('#igCompEd').show();
            $('#complemento_setor_m_add_ed').val('');
            $("#complemento_setor_m_add_ed").css("display", "none");
        });

        $('#complemento_setor_m_ed').on('change', function() {
            $('complemento_setor_m_add_ed').val('');
        });

    });

    // Monitor "carregar mais" button
    $("#statusMonitor").change(toggleDisabled());


    function toggleDisabled() {
        var status = $("#statusMonitor").val();
        if (status == 0) {
            $("#more").addClass("disabled");
        } else {
            return;
        }
    }


    function changeSelected(arr) {
        document.querySelector("#total-bens-selecionados").innerHTML = `${arr.length}`;
    }
    var selectAllCalls = 0;
    var selectedMat = [];
    $("#selectall-bens").click(function() {
        if ($('#pesquisa-form').val() != '') {

            $('.select-print input:checkbox').each(function() {
                if ($(this).is(':visible')) {

                    $(this).prop('checked', $('#selectall-bens').is(':checked'));
                }
            });

        } else if ($("#filter_complemento").val() != null) {

            $('.select-print input:checkbox').each(function() {
                if ($(this).is(':visible')) {

                    $(this).prop('checked', $('#selectall-bens').is(':checked'));
                }
            });

        } else {

            $('[data-status="material-checkbox"]').not(this).prop('checked', this.checked);
            //$('[data-status="material-checkbox"]').prop('checked', this.checked);


        }
        selectAllCalls++;
        if (selectAllCalls % 2 == 0) {
            selectedMat = [];
            changeSelected(selectedMat);
            return;
        }

        allMat = $('input:checkbox:checked').map(function() {
            if (this.value != 'on' && !selectedMat.includes(this.value)) {
                return this.value;
            }
        }).get();
        selectedMat = selectedMat.concat(allMat);
        changeSelected(selectedMat);
    });

    // get number of selected materials
    $('#materiais').change(e => {

        let matValue = e.target.value;
        let elId = e.target.id;

        if ((elId == 'id_bem') || (elId == 'id_bem_localizar')) {
            if (selectedMat.includes(matValue)) {
                index = selectedMat.indexOf(matValue);
                selectedMat.splice(index, 1);
            } else {
                selectedMat.push(matValue);
            }
        }
        changeSelected(selectedMat);
    });

    $('#bensLocalizar').click(function() {
        if (selectedMat.length > 0) {
            $('#modalConfirma').modal('show');
        } else {
            $('#modalConfirma').modal('hide');
            return toastr.error('<strong>ERRO!</strong> Selecione pelo menos 1 material!', {
                timeOut: 10000
            });
        }
    });

    $('#manutencao').click(function() {
        if (selectedMat.length > 0) {
            $('#modalManutencao').modal('show');
        } else {
            $('#modalManutencao').modal('hide');
            return toastr.error('<strong>ERRO!</strong> Selecione pelo menos 1 material!', {
                timeOut: 10000
            });
        }
    });

    $('#voltar_manutencao').click(function() {
        $('#modalManutencao').modal('hide');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });

    function finalizarInv(x) {
        var inventariaveis = -2;
        var nao_inv = 0;
        var sit_inv;
        $('#bensmateriais tr').each(function() {
            sit_inv = $(this).find('.inventario').text();
            if (sit_inv != 'A INVENTARIAR') {
                inventariaveis += 1;
            } else {
                nao_inv += 1;
            }
        });
        if (inventariaveis - nao_inv == x) {
            $('#modalFinalizarInv').modal('show');
            $('#btn-confirmaInventario').prop('disabled', false);
            var msgInv = "Clique para finalizar o inventário";
            $("#btn-confirmaInventario").attr('data-tooltip', msgInv);
        } else {
            var msgInv = "Há bens a inventariar!";
            $("#btn-confirmaInventario").attr('data-tooltip', msgInv);
        }
    }
    //MANUNTENÇÃO - MAFE

    var manutencao = () => { 
        console.log(selectedMat[0]);
        var materiais = selectedMat;
        if (materiais == '' | selectedMat.length == 0) {
            callToastr("error", "<strong>ERRO!</strong> Selecione pelo menos 1 material!");
            return false;
        }


        $.ajax({
            type: 'POST',
            url: './api/manutencao.api.php',
            data: {
                arrMat: materiais.join(',')
            },
            success: function(res) {
                $('#tabelamanutencao').empty().html(res);
                $("#setornovo").select2({
                dropdownParent: $('#confirmarModal') // <== isso faz funcionar no modal
                });
                $("#complementosetor").select2({
                dropdownParent: $('#confirmarModal')
                });
                $("#setornovo").change(function() {
                    carregarComplementos(0);
                });


            },
            error: function(e) {
                alert(e);
            }
        });
    }

    // Transfer

    var observacao = $('#observacao').val();
    var setorUsuario = "<?php echo $_SESSION['setor'] ?>";
    var cod_setorUsuario = "<?php echo $cod_setor; ?>";

    var confirmation = () => {
    // asks user if he really want to transfer to the selected sector
    var materiais = selectedMat;
    if (materiais == '' || selectedMat.length == 0) {
        callToastr("error", "<strong>ERRO!</strong> Selecione pelo menos 1 material!");
        return false;
    }

    $.ajax({
        type: 'POST',
        url: './api/confirmar_transferencia.api.php',
        data: {
            arrMat: materiais
        },
        success: function(res) {
            $('#confirmarHtml').empty().html(res);
            $('#confirmarModal').modal('show');

            $('#confirmarModal').on('shown.bs.modal', function () {
                // Remove atributo que bloqueia o foco em navegadores como o Edge
                $(this).removeAttr('aria-hidden');

                // Reaplica Select2 com parent correto para funcionar no modal
                $("#setornovo").select2({
                    dropdownParent: $('#confirmarModal')
                });
                $("#complementosetor").select2({
                    dropdownParent: $('#confirmarModal')
                });

                // Reativa mudança de complemento ao trocar setor
                $("#setornovo").change(function() {
                    carregarComplementos(0);
                });
            });
        },
        error: function(e) {
            alert("Erro ao carregar modal de confirmação. Verifique o console.");
            console.error(e);
        }
    });
}


    function transferirBensLocal() { // upon user confirmation call this function
        $('#mensagem').html('');

        var materiais = selectedMat;
        var complemento = $("#complementosetor :selected").val();

        if (materiais == '') {
            callToastr("error", "<strong>ERRO!</strong> Selecione pelo menos 1 material!");
            return false;
        }

        if (complemento == "" || complemento.length === 0) {
            callToastr("error", "Por favor , selecione um complemento de setor!")
            return false;
        }


        complemento = $("#complementosetor :selected").val();
        observacao = $('#observacao').val();
        if (observacao == '') {
            return toastr.error('A justificativa de transferência é obrigatória!', {
                timeOut: 10000
            });
        }
        observacao = $('#observacao').val();
        $('#btnDismiss').trigger('click');
        codigoUnidade = <?php echo $_SESSION['codigounidade'] ?>;
        codigoSetorUsuario = <?php echo $_SESSION['codigosetor'] ?>;
        codigoUsuario = <?php echo $_SESSION['codigousuario'] ?>;
        //validação dos dados[0] antes de transferir os materiais
        function transferir() { //what really makes the transfer happen.
            var setorDestino = $("#setornovo :selected").val();
            $.ajax({
                type: 'POST',
                url: './api/transferencia.api.php',
                data: {
                    transferirMateriais: 'transferirMateriais',
                    materiais: materiais,
                    setor: setorDestino,
                    complemento: complemento,
                    observacao: observacao,
                    setorUsuario: setorUsuario,
                    codigoUnidade: codigoUnidade,
                    codigoSetorUsuario: codigoSetorUsuario,
                    codigoUsuario: codigoUsuario
                },
                success: function(result) {
                    if (result.response) {
                        $('.alert-header').show();
                        callToastr("success", "<strong>Transferência efetuada!</strong> Imprima o Termo de Responsabilidade.");
                        // *********************ABRIR TERMO PARA IMPRIMIR EM MODAL *****************************
                        $.ajax({
                            type: 'POST',
                            url: "./api/termo_responsabilidade_html.api.php",
                            data: {
                                termo: result.termo,
                                filtro: 1

                            },
                            success: function(results) {
                                $('#termoHtml').empty().html(results);
                                $('#myModal').modal().focus();

                                // var newWindow = window.open("", "new window", "width=900, height=800");
                                // newWindow.document.write(results);
                                materiais.forEach(e => {
                                    $(`[value="${e}"]`).hide();
                                    $(`[value="${e}"]`).parent().append("<span class='label label-primary'>Transferido</span>");
                                    selectedMat = [];
                                    changeSelected(selectedMat);
                                });
                                $(document).on('hidden.bs.modal', '#myModal', function(event) {
                                    location.reload();
                                });
                                //location.reload();
                            }

                        });


                    } else {
                        callToastr("error", `<strong>Erro!</strong> Não foi possível transferir os materiais. ${result.mensagem}`);
                    }
                },
                error: function(e) {
                    callToastr("error", `<strong>Erro!</strong> Não foi possível transferir os materiais. ${e}`);
                }
            });

        }
        transferir();
    }

    // end transfer
    function carregarComplementos(x) {
        codigo = $('#setornovo').val();
        $.ajax({
            type: 'POST',
            url: './api/transferencia.api.php',
            data: {
                carregarComplementos: 'carregarComplementos',
                codigoSetor: $('#confirmarModal').is(':visible') ? codigo : cod_setorUsuario,
                editar: x,

            },
            success: function(result) {

                $('#complementosetor').empty().append(result);
                $('#complemento_setor_m_e').append(result);
                //$('#filter_complemento').append(result);
            },
            error: function(e) {
                callToastr("error", `<strong>Erro!</strong> Não foi possível carregar os complementos do Setor. ${e}`);
            }
        });
    }

    function filtroComplemento() {

        var complementos = {};
        $('.inv-table tbody tr').each(function() {
            var comp_val = $(this).find('td.complemento').data('comp');
            var comp_text = $(this).find('td.complemento').text();

            if (!complementos[comp_val]) {
                complementos[comp_val] = {};
            }

            complementos[comp_val] = comp_text;

        });
        $.each(complementos, function(key, value) {
            $("#filter_complemento").append($('<option>', {
                value: key,
                text: value
            }));
        });



    }


    function bensLocalizar() {

        $('#mensagem').html('');

        var materiais = selectedMat;

        materiais = materiais.join(',');

        if (materiais == '' | materiais.length == 0) {
            callToastr("error", "<strong>ERRO!</strong> Selecione pelo menos 1 material!");
            return false;
        }

        var senha = $('#senha_localizar').val();

        if (!senha) {
            callToastr("error", "<strong>ERRO!</strong> Senha é obrigatória.", "toast-bottom-full-width", 1);
            $('#senha_localizar').focus();
            return false;
        }
        if (senha == '123456') {
            callToastr("error", "<strong>ERRO!</strong> Senha padrão. Favor alterar a senha.", "toast-bottom-full-width", 1);
            return false;
        }

        var verificaInv = '<?php echo verificaInventarioSetor(); ?>';

        // FIRST CHECK IF PASSSWORD MATCH.
        $.ajax({
            type: 'POST',
            url: './api/assinatura.api.php',
            data: {
                inputSenha: senha
            },
            success: function(result) {
                if (result.response) {
                    $('#dismissModal').trigger('click');
                    $('.alert-header').hide();
                    // MAKE DB QUERIES AND GET TERMS ARRAY
                    $.ajax({
                        type: "GET",
                        data: {
                            m: materiais,
                            verificaInv: verificaInv,
                        },
                        url: './localizar-materiais.php',

                        success: function(res) {
                            var newWindow = window.open("", "new window", "width=900, height=800");
                            newWindow.document.write(res);
                            var termos = res;
                            location.reload();

                        },
                        error: function(error) {
                            callToastr("error", `<strong>ERRO!</strong> Há algum problema interno. Contate o administrador. ${error}`);

                        }
                    });

                } else {

                    toastr.error("Senha incorreta. Não foi possível assinar os termos!", {
                        timeOut: 10000
                    });
                    return $('.senha').addClass("has-error");
                }
            },
            error: function(e) {

                callToastr("error", `<strong>ERRO!</strong> Não foi possível localizar os materiais. ${e}`);
            }
        });

    }

    function NaoLocalizados() {

        var materiais = $('input:checkbox:checked').map(function() {
            if (this.value != 'on')
                return this.value;
        }).get();

        var patrimonio = $('input:checkbox:checked').map(function() {
            if (this.value != 'on')
                return $(this).data('patrimonio');
        }).get();

        var desc = $('input:checkbox:checked').map(function() {
            if (this.value != 'on')
                return $(this).data('desc');
        }).get();

        var justificativa = $('#nlocalizados-body').val();


        if (justificativa == '') {
            callToastr("error", "<strong>ERRO!</strong> Preencha a justificativa!");
            return false;
        }

        //validação dos dados[0] antes de transferir os materiais
        $.ajax({
            type: 'POST',
            url: './api/transferencia.api.php',
            data: {
                bensNaoLocalizados: 'bensNaoLocalizados',
                materiais: materiais,
                patrimonio: patrimonio,
                desc: desc,
                justificativa: justificativa,
            },
            success: function(result) {
                if (result.response) {

                    callToastr("success", `<strong>Informação Registrada!</strong> Materiais atualizados.`);
                    location.reload();

                } else {
                    callToastr("error", `<strong>Houve algum erro!</strong> Tente novamente. ${result.mensagem}`);
                    location.reload();
                }
            },
            error: function(e) {
                callToastr("error", `<strong>Erro!</strong> Não foi possível realizar a ação. ${e} `);
            }
        });

    }

    function printTerm(termoId) {
        var imprimirTermo = termoId;

        $.ajax({
            type: 'POST',
            url: "./api/termo_responsabilidade_html.api.php",
            data: {
                termo: imprimirTermo,
                filtro: 1

            },
            success: function(results) {
                $('#messageModal').empty();
                $('#modalTermo').modal().focus();
                var newWindow = window.open("", "new window", "width=900, height=800");
                newWindow.document.write(results);
            },
            error: function(error) {
                alert("Não foi possível imprimir o termo. Contate o administrador.");
            }

        });

    }


    function printPage() {
        window.print();
    }

    var totalBens = <?php echo $total; ?>;


    // JQUERY AJAX TO RETURN DATA
    function mostrarBens(reset) {
        $('#materiais').empty().append('<tr><td id="loading"colspan="9"><center><h4 style="font-size: 18px; font-weight:bold"><strong>Carregando Bens no Setor...</strong></h4></center></td></tr>');
        getData(undefined, false, 0);


    }
    // ajax query to get Bens
    function getData(search, reset, searchOn) {
        let searchType = "";
        if (searchOn == 1) {
            if (search == undefined) { // validate if something is passed
                searchType = undefined;

            } else if (search == "NaoLocalizado") {
                searchType = "NaoLocalizado";
            } else { // tries to discover if it is a number or str
                let re = /\d+/;
                let convert = search.match(re);

                if (convert) {
                    searchType = "number";
                    search = search.trim();

                } else {
                    searchType = "string";
                    search = search.trim();

                }
            }
        } else {
            search = undefined;
            searchType = undefined;
        }
        $.ajax({ // GET DATA
            type: 'POST',
            url: "./api/carregar_bens.api.php",
            data: {
                // incrementRange: newRange,
                // oldRange: oldRange,
                total: totalBens,
                search: search,
                searchType: searchType
            },
            success: function(results) {
                $("#loading").hide();
                if (search) {
                    $('#materiais').empty().append(results);
                } else if (reset) {
                    $('#materiais').empty().html(results);
                } else {
                    $('#materiais').append(results);
                }
                var total_bens = $('#bensmateriais tr').length - 2;
                $('#total-bens').empty().html(total_bens);
                finalizarInv(total_bens);
                filtroComplemento();




            },
            error: function(err) {
                callToastr("error", `<strong>ERRO!</strong> Não foi possível obter os dados. ${err}`);
            }

        });
    }


//mafe

    function enviarManutencao() {

        var size = selectedMat.length;
        var list = {};
        console.log(size);
        var print=0;
        for (var i = 1; i <= size; i++) {
            var num = $("#numpat_" + i).text();
            var text = $("textarea#jus_" + i).val();
            if(text==''){
                print=1;
            }
            var info = [num,text];
            console.log(num);
            console.log(info);
            list[i]=info;
        }
        console.log("teste: "+print);
        if(print==1){
            callToastr("error", `<strong>ERRO!</strong>Preencha os campos`);
        }
        else{
        $.ajax({
            type: 'POST',
            url: "./api/manutencao.api.php",
            data: {
                enviado_manutencao: "S",
                elem: JSON.stringify(list) // Convertendo para JSON antes de enviar
            },
            success: function (results) {
                console.log(list);
                callToastr("success", `Solicitação de Manutenção enviada.`);
                $('#modalManutencao').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            },
            error: function (error) {
                callToastr("error", `<strong>ERRO!</strong> Não foi possível realizar a solicitação.`);
            }
        });
    }
}









</script>
<!-- print modal -->
<script>
    document.getElementById('btnPrint').onclick = function() {
        var conteudo = document.getElementById('termoHtml').innerHTML;
        ifra = document.getElementById('ifr');
        ifra.contentDocument.write(conteudo);
        window.frames['ifr'].focus();
        window.frames['ifr'].print();
    };
    // function printData()
    // {
    //    var divToPrint=document.getElementById("bensmateriais");
    //    newWin= window.open("");
    //    newWin.document.write(divToPrint.outerHTML);
    //    newWin.print();
    //    newWin.close();
    // }
</script>

<!-- modal manutenção-->
<div class="modal fade" id="modalManutencao" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <center>
                    <h3 class="modal-title">Confirmar Solicitação de Manutenção</h3>
                </center>
            </div>

            <div class="modal-body">
                <div class='row'>
                    <div class='col-md-12' id="tabelamanutencao">
                        <p style="font-size:16px;">Texto Texto</p>
                    </div>
                    <!-- conteudo do modal -->
                </div>
                
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default " id="voltar_manutencao" data-dismiss="modal" aria-label="Close" aria-hidden="true">Voltar</button>
                <button type="button" class="btn btn-primary" id="btn-modalConfirma" onclick="enviarManutencao()">Confirmar</button>
            </div>
        </div>
    </div>
</div>
</div>

