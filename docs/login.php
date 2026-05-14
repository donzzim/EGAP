<?php 
require '../configuration.php';
require "../libraries/phpass/PasswordHash.php";
require "connect_db.php";


// error_reporting(E_ALL);
// ini_set('display_errors','On');

$mensagem = '<div class="alert alert-warning" role="alert"><strong><center>Atenção!</center></strong></br>Utilize a <strong>SENHA UNIFICADA</strong>.
<br>Dúvidas? Acione o Help Desk da <strong>STI</strong>.</div>';
if(isset($_SESSION['mensagem'])){
	$mensagem = $_SESSION['mensagem'];
}

function mask($val, $mask) { //mascara para CPF e Matricula
	$maskared = '';
	$k = 0;
	for($i = 0; $i<=strlen($mask)-1; $i++) {
			if($mask[$i] == '#') {
					if(isset($val[$k])) $maskared .= $val[$k++];
			} else {
					if(isset($mask[$i])) $maskared .= $mask[$i];
			}
	}
	return $maskared;
}

function searchUser($cpf){ //function de para busca de usuario na base
	global $conn;
	$cpfMask 	= mask($cpf,'###.###.###-##');
	$cpfNoZeros = ltrim($cpf, '0');

	$stmt = $conn->query("SELECT DISTINCT u.id id_user, u.name, l.unidade_judiciaria id_unidade, uni.UnidadeOrganizacional unidade, l.setor id_setor, se.Setor setor,
	info.cpf, info.cargo, info.matricula, u.block,
	(SELECT m.group_id FROM jos_user_usergroup_map m WHERE m.user_id = u.id AND m.group_id = 12) useradmin,
	(SELECT m.group_id FROM jos_user_usergroup_map m WHERE m.user_id = u.id AND m.group_id = 27) levantamento
	FROM jos_users u 
		LEFT OUTER JOIN mat_lotacao l ON u.id = l.id_user  
		LEFT OUTER JOIN mat_infousers info ON u.id = info.usuario_id
		LEFT OUTER JOIN mat_setores uni ON uni.id = l.unidade_judiciaria
		LEFT OUTER JOIN mat_setores se ON se.id = l.setor
	WHERE (se.cns IS NULL OR se.cns='') AND se.Setor IS NOT NULL AND (info.cpf='$cpf' OR info.cpf='$cpfMask' OR info.cpf='$cpfNoZeros') ");
	$stmt->execute();
	$user = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $user;
}

function searchUserEmail($email){ //function de para busca de usuario na base
	global $conn;

	$stmt = $conn->query("SELECT DISTINCT u.id id_user, u.name, l.unidade_judiciaria id_unidade, uni.UnidadeOrganizacional unidade, l.setor id_setor, se.Setor setor,
	info.cpf, info.cargo, info.matricula, u.block,
	(SELECT m.group_id FROM jos_user_usergroup_map m WHERE m.user_id = u.id AND m.group_id = 12) useradmin,
	(SELECT m.group_id FROM jos_user_usergroup_map m WHERE m.user_id = u.id AND m.group_id = 27) levantamento
	FROM jos_users u 
		LEFT OUTER JOIN mat_lotacao l ON u.id = l.id_user  
		LEFT OUTER JOIN mat_infousers info ON u.id = info.usuario_id
		LEFT OUTER JOIN mat_setores uni ON uni.id = l.unidade_judiciaria
		LEFT OUTER JOIN mat_setores se ON se.id = l.setor
	WHERE (se.cns IS NULL OR se.cns='')  AND se.Setor IS NOT NULL AND UPPER(u.email)=UPPER('$email') ");
	$stmt->execute();
	
	$user = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $user;
}

//Buscando a Comarca a partir do setor
function searchComarca($setor){
	global $conn;

	$stmt = $conn->query("SELECT codigodaUO FROM mat_setores WHERE id=$setor");	
	$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	return count($data) ? $data[0]['codigodaUO'] : 766;
}

//Função que busca a lotação atual do usuario 
function lotacaoAtual($user_id){
	global $conn;

	$lotAtual = [];
	$stmt = $conn->query("SELECT DISTINCT l.setor FROM mat_lotacao l WHERE setor IS NOT NULL AND l.id_user = $user_id");
	$lotacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($lotacoes as $lotacao) {
		$lotAtual[] = $lotacao['setor'];
	}
	return $lotAtual;
}


//Função que adiciona uma lotação ao usuario
function addLotacao($user_id, $setorPrinc, $setoresAd, $existe){
	global $conn;

	if ($setoresAd) {
		unset($setorPrinc['count']);
		unset($setoresAd['count']);
		
		$setores = array_unique(array_merge($setorPrinc, $setoresAd));

		if ($existe) {
			$lotAtual = lotacaoAtual($user_id); //função chamada para retornar a lotação atual
			$addSetor = array_diff($setores, $lotAtual);
			$setores = array_merge($setores, $addSetor);
		}	

		foreach ($setores as $setor) {
			$comarca = searchComarca($setor);
			if($setor){
				// Trava contra duplicidade de lotação
				$checkLot = $conn->query("SELECT id FROM mat_lotacao WHERE id_user = $user_id AND setor = $setor");
				if(count($checkLot->fetchAll(PDO::FETCH_ASSOC)) == 0){
					$stmt = $conn->query("INSERT INTO mat_lotacao (usuario, date_time, id_user, unidade_judiciaria, setor)
															  values($user_id, now(), $user_id, $comarca, $setor)");	
				}
			}
		}
	}else{
		unset($setorPrinc['count']);
		
		if ($existe) {
			$lotAtual = lotacaoAtual($user_id); //função chamada para retornar a lotação atual
			$addSetor = array_diff($setorPrinc, $lotAtual);
			$setores = array_merge($setorPrinc, $addSetor);
		}

		foreach ($setores as $setor) {
			$comarca = searchComarca($setor);
			if($setor){
				// Trava contra duplicidade de lotação
				$checkLot = $conn->query("SELECT id FROM mat_lotacao WHERE id_user = $user_id AND setor = $setor");
				if(count($checkLot->fetchAll(PDO::FETCH_ASSOC)) == 0){
					$stmt = $conn->query("INSERT INTO mat_lotacao (usuario, date_time, id_user, unidade_judiciaria, setor)
															  values($user_id, now(), $user_id, $comarca, $setor)");	
				}
			}
		}
	}

}	



$srv = "10.100.192.107";
$port = "389";

if(isset($_POST['inputLogin'])){

	$_SESSION['codigousuario'] = null;
	$_SESSION['nomeusuario']   = null;
	$_SESSION['codigounidade'] = null;
	$_SESSION['unidade']       = null;
	$_SESSION['codigosetor']   = null;
	$_SESSION['setoratual']    = null;
	$_SESSION['setor']         = null;
	$_SESSION['cpf']  		   = null;
	$_SESSION['cargo'] 		   = null;
	$_SESSION['matricula'] 	   = null;
	$_SESSION['lotacao']	   = null;	
	$_SESSION['useradmin'] 	   = false;
	$_SESSION['levantamento']  = false;

	
	$username = trim($_POST['inputLogin']);
	$password = trim($_POST['inputSenha']);


	$phpass = new PasswordHash(10, true);
	$hash 	= $phpass->HashPassword($password);

	//iniciando conexão com ldap

	$cn 	= "cn=cargainicialoid";
	$user 	= "$cn,ou=servicos,o=tjes";
	$pass 	= "awc$43wb@";

	$ldap = ldap_connect($srv,$port);
	$bind = ldap_bind($ldap, $user, $pass);

	
	$result = ldap_search($ldap, "ou=ID,o=tjes", "tjloginRede=$username");
	$data 	= ldap_get_entries($ldap, $result);

	

	if($data['count'] == 1){
		
		$cargo 			=  strtoupper($data[0]['title'][0]);
		$tipoUsuario	=  strtoupper($data[0]['tjtipousuario'][0]);
		$nome 			=  strtoupper($data[0]['fullname'][0]);
		$matricula 		=  isset($data[0]['tjmatricularhcorreg']) ? $data[0]['tjmatricularhcorreg'][0] : 0;
		$setorPrinc 	=  isset($data[0]['tjorgaoprinc']) ? $data[0]['tjorgaoprinc'] : null;
		$setoresAd  	=  isset($data[0]['tjorgaoadic']) ? $data[0]['tjorgaoadic'] : null;
		$cpf 			=  $data[0]['cn'][0];

		$email 			=  strtoupper($data[0]['mail'][0]);
		if(isset($data[0]['tjgoogleemailprinc'][0])) $email =  strtoupper($data[0]['tjgoogleemailprinc'][0]);
		// $tipoUsuario. ' - '. 
		// $nome 	. ' - '. 	
		// $email . ' - '. 		
		// $matricula 	. ' - '. 
		// $setorPrinc . ' - '. 
		// $setoresAd  . ' - '. 
		// $cpf 		);
	
		$usuario = "cn=$cpf,ou=ID,o=TJES";
		$senha = $password;
		$bind_usr = ldap_bind($ldap, $usuario, $senha);
	
		if ($bind_usr) {

			if ($tipoUsuario != 'E' && $matricula != 0) {
				$matricula 	= mask($matricula,'###.###-##');
			}
		$cpfMask		= mask($cpf,'###.###.###-##');

		// $usr = searchUser($cpf); //buscando usuario na base do patrimonio
		$usr = searchUserEmail($email); //buscando usuario na base do patrimonio

		if (!$usr) {
			// Trava contra duplicidade de email na jos_users
			$stmtCheckUser = $conn->query("SELECT id FROM jos_users WHERE UPPER(email) = UPPER('$email')");
			$userExiste = $stmtCheckUser->fetchAll(PDO::FETCH_ASSOC);

			if (count($userExiste) == 0) {
				if ($tipoUsuario == 'E') {
					$stmt = $conn->query("INSERT INTO jos_users (name, username, email, password, block, sendEmail, registerDate, lastvisitDate, params)
					values('$nome', '$username', '$email', '$hash', 1, 0, now(), now(), '{}')");
				}else{

					$stmt = $conn->query("INSERT INTO jos_users (name, username, email, password, block, sendEmail, registerDate, lastvisitDate, params) values('$nome', '$username', '$email', '$hash', 0, 0, now(), now(), '{}')");
				}
				$user_id = $conn->lastInsertId();
			
				$stmt = $conn->query("INSERT INTO mat_infousers (date_time, usuario_id, cpf, matricula, cargo)
															values(now(), $user_id, '$cpfMask', '$matricula', '$cargo')");
		
				$stmt = $conn->query("INSERT INTO jos_user_usergroup_map 
									values($user_id, 13)");	

				addLotacao($user_id, $setorPrinc, $setoresAd, false);	
			} else {
				// Usuário existe na jos_users, mas o searchUserEmail retornou vazio. Processa como atualização.
				$user_id = $userExiste[0]['id'];
				addLotacao($user_id, $setorPrinc, $setoresAd, true);
			}
		}else{ //se usuario ja existe no banco

			$user_id = $usr[0]['id_user'];
			$nameExp = explode("(", utf8_encode($usr[0]['name']));
			if (count($nameExp) > 1 && $tipoUsuario == 'E') {
				$stmt = $conn->query("UPDATE jos_users SET username = '$username', email = '$email', password = '$hash'
				WHERE id = $user_id;");
			}else{
				$stmt = $conn->query("UPDATE jos_users SET name = '$nome', username = '$username', email = '$email', password = '$hash'
				WHERE id = $user_id;");
			}

			$stmt = $conn->query("UPDATE mat_infousers SET date_time = now(), cpf = '$cpfMask', matricula = '$matricula', cargo = '$cargo'
														WHERE usuario_id = $user_id;");
			
		   
			addLotacao($user_id, $setorPrinc, $setoresAd, true);
	
		}
	
		$user = searchUser($cpf);

		if ($user[0]['block'] == 0) {
			if($setorPrinc == null){
				$setorPrinc = lotacaoAtual($user[0]['id_user']);
			}			
			$lotacao 				   = array_column($user, 'id_setor');
			$_SESSION['codigousuario'] = $user[0]['id_user'];
			$_SESSION['nomeusuario']   = utf8_encode($user[0]['name']);
			$_SESSION['codigounidade'] = $user[0]['id_unidade'];
			$_SESSION['unidade']       = utf8_encode($user[0]['unidade']);
			$_SESSION['codigosetor']   = $setorPrinc[0];
			if($_SESSION['codigosetor']==3201) $_SESSION['codigosetor'] = 2933; //UNIDADE DE COORDENACAO DE PROGRAMAS
			if($_SESSION['codigosetor']==3202) $_SESSION['codigosetor'] = 2935; //GAB DES UBIRATAN
			if($_SESSION['codigosetor']==3212) $_SESSION['codigosetor'] = 2946; //GAB DES DEBORA
			if($_SESSION['codigosetor']==3217) $_SESSION['codigosetor'] = 2948; //GAB DES FABIO BRASIL
			if($_SESSION['codigosetor']==5121) $_SESSION['codigosetor'] = 2561; //GAB DES JULIO
			if($_SESSION['codigosetor']==5122) $_SESSION['codigosetor'] = 2559; //GAB DES RACHEL
			if($_SESSION['codigosetor']==5124) $_SESSION['codigosetor'] = 2563; //GAB DES EDER
			if($_SESSION['codigosetor']==5125) $_SESSION['codigosetor'] = 2564; //GAB DES RAPHAEL
			if($_SESSION['codigosetor']==5123) $_SESSION['codigosetor'] = 2562; //GAB DES HELIMAR
			if($_SESSION['codigosetor']==5136) $_SESSION['codigosetor'] = 2931; //GAB DES SERGIO RICARDO
			if($_SESSION['codigosetor']==5209) $_SESSION['codigosetor'] = 2949; //GAB DES HELOISA
			if($_SESSION['codigosetor']==5210) $_SESSION['codigosetor'] = 2950; //GAB DES MARCOS FEU ROSA
			if($_SESSION['codigosetor']==5215) $_SESSION['codigosetor'] = 2953; //GAB DES CONVOCADO ALDARY
			if($_SESSION['codigosetor']==5127) $_SESSION['codigosetor'] = 2929; //GAB DES MARIANNE
			// if($_SESSION['codigosetor']==2514) $_SESSION['codigosetor'] = 2527; //GAB DES ARTHUR
			
			if($_SESSION['codigosetor']==3130) $_SESSION['codigosetor'] = 2957; //NAPES
			if($_SESSION['codigosetor']==2532) $_SESSION['codigosetor'] = 2519; //CACHOEIRO DE ITAPEMIRIM - 2ª VARA DA INFÂNCIA E JUVENTUDE
			if($_SESSION['codigosetor']==3590) $_SESSION['codigosetor'] = 2534; //SERRA - 4º JUIZADO ESPECIAL CÍVEL 
			if($_SESSION['codigousuario']==2790 && $_SESSION['codigosetor']==89) $_SESSION['codigosetor'] = 95; //COMARCA DE BAIXO GUANDU
			if($_SESSION['codigousuario']==399 && $_SESSION['codigosetor']==2586) $_SESSION['codigosetor'] = 2534; //CARIACICA - 7º CEJUSC
			if($_SESSION['codigousuario']==288 && $_SESSION['codigosetor']==1817) $_SESSION['codigosetor'] = 1061; //CARIACICA - 7º CEJUSC
			if($_SESSION['codigousuario']==1030 && $_SESSION['codigosetor']==5119) $_SESSION['codigosetor'] = 2549; //VITORIA - 12º CEJUSC
			if($_SESSION['codigousuario']==1760 && $_SESSION['codigosetor']==2527) $_SESSION['codigosetor'] = 2514; //GAB DES ARTHUR - DANIELA
			
			//if($_SESSION['codigousuario']==3917 && $_SESSION['codigosetor']==1599) $_SESSION['codigosetor'] = 1005; //COMARCA DE MARECHAL FLORIANO
			if($_SESSION['codigousuario']==1853) $_SESSION['codigosetor'] = 399; //MARCELO TAVARES
			
			$_SESSION['setoratual']    = $setorPrinc[0];
			// $_SESSION['codigosetor']   = $user[0]['id_setor'];
			// $_SESSION['setoratual']    = $user[0]['id_setor'];
			$_SESSION['setor']         = utf8_encode($user[0]['setor']);
			$_SESSION['cpf']           = preg_replace( '#[^0-9]#', '', $user[0]['cpf']);
			$_SESSION['cargo']    	   = utf8_encode($user[0]['cargo']);
			$_SESSION['matricula']     = $user[0]['matricula'];
			$_SESSION['lotacao']       = $lotacao;
		
			if($user[0]['useradmin']) 		$_SESSION['useradmin'] = true;
			if($user[0]['levantamento']) 	$_SESSION['levantamento'] = true;

			header('location: index.php');

		}else if($user[0]['block'] == 1 && $tipoUsuario == 'E'){
			$mensagem = '<div class="alert alert-danger" role="alert">Estagiarios necessitam de autorização prévia para acesso ao <strong>E-GAP</strong>!</div>';
		}else{
			$mensagem = '<div class="alert alert-danger" role="alert">Usuário bloqueado!</div>';
		}
		
	}else{
		$mensagem = '<div class="alert alert-danger" role="alert">Senha incorreta. Caso tenha esquecido sua senha, entre em contato com o Help Desk da <strong>STI</strong>.</div>';
	}
	
}else{
	$mensagem = '<div class="alert alert-danger" role="alert">Nome de usuário incorreto. Tente novamente!</div>';
}

ldap_unbind($ldap);
}
?>
<!DOCTYPE html>
<html lang="pt_br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<title>e-GAP - Requisição de Materiais e Inventário OnLine</title>

	<link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
	<link rel="stylesheet" type="text/css" href="lib/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">

	<script src="lib/jquery/jquery-3.1.0.min.js"></script>
	<script src="lib/bootstrap/js/bootstrap.min.js"></script>
	<script>
	$(document).ready(function(e) {
		if (navigator.userAgent.match(/msie/i) || navigator.userAgent.match(/trident/i) ){
			var msg = 'Sistema incompatível com Internet Explorer. Utilize o Google Chrome ou Firefox!';
			alert(msg);
			$('#mensagem').html('<div class="alert alert-danger" role="alert">'+msg+'</div>');
		}
	});

	</script>
</head>
<body>
	<div class="container-fluid">		
		<form class="form-signin" method="post">
			<h2 class="form-signin-heading"><img src="https://sistemas.tjes.jus.br/patrimonio/images/brasao.png"></h2>
				<div id="mensagem"><?php echo $mensagem; ?></div>

			<label for="inputLogin" class="sr-only">Login</label>
			<input type="text" id="inputLogin" name="inputLogin" class="form-control" placeholder="Usuário" required="" autofocus="">
			<br />
			<label for="inputSenha" class="sr-only">Senha</label>
			<input type="password" id="inputSenha" name="inputSenha" class="form-control" placeholder="Senha" required="">
			<br />
			<button class="btn btn-sm btn-primary btn-block" type="submit">Entrar <p class="glyphicon glyphicon-log-in"></p></button>		
		</form>
	
		<footer class="footer navbar-fixed-bottom">
			<div class="container-fluid" id="footer">
					e-GAP
			</div>
		</footer>
	</div>
</body>
</html>