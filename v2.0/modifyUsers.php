<?php

function clearId($id){
     $LetraProibi = Array(" ",",",".","'","\"","&","|","!","#","$","¨","*","(",")","`","´","<",">",";","=","+","§","{","}","[","]","^","~","?","%");
     $special = Array('Á','È','ô','Ç','á','è','Ò','ç','Â','Ë','ò','â','ë','Ø','Ñ','À','Ð','ø','ñ','à','ð','Õ','Å','õ','Ý','å','Í','Ö','ý','Ã','í','ö','ã',
        'Î','Ä','î','Ú','ä','Ì','ú','Æ','ì','Û','æ','Ï','û','ï','Ù','®','É','ù','©','é','Ó','Ü','Þ','Ê','ó','ü','þ','ê','Ô','ß','‘','’','‚','“','”','„');
     $clearspc = Array('a','e','o','c','a','e','o','c','a','e','o','a','e','o','n','a','d','o','n','a','o','o','a','o','y','a','i','o','y','a','i','o','a',
        'i','a','i','u','a','i','u','a','i','u','a','i','u','i','u','','e','u','c','e','o','u','p','e','o','u','b','e','o','b','','','','','','');
     $newId = str_replace($special, $clearspc, $id);
     $newId = str_replace($LetraProibi, "", trim($newId));
     return strtolower($newId);
}


function fixDuplicateInterests(&$user) {
	$username = $user->getElementsByTagName("username")->item(0)->nodeValue;
	$duplicateInterests = 0;
	$interests = array();
	$interestsNodes = $user->getElementsByTagName("interests");
	$in = "";
	foreach($interestsNodes as $interest) {
		array_push($interests, $interest);
	}
	foreach($interests as $node) {
		if (clearId($node->nodeValue) === clearId($in)) {
			$duplicateInterests++;
			$node->parentNode->removeChild($node);
		}
		$in = $node->nodeValue;
	}
	
	if ($duplicateInterests) {
		echo "\nUsuário $username com $duplicateInterests interests duplicados\n";
	}
}
//FIM DE fixDuplicateInterests


function fillImportUsersInfo(&$xmlNode, $arr) {
	//$arr é um array com os dados necessários
	
	
		////////// XML SCHEMA ///////////////////////////
		//<!--<import_users_info>
		//	  <journal>
		//		  <name>
		//	  </journal>
		//	  <changed_users>
		//		  <users>...</users>
		//		  <num_changed_users>
		//		  <num_changed_users_registered>
		//	  </changed_users>
		//	  <num_users_original>
		//	  <num_users_registered>
		//</import_users_info>-->
		/////////////////////////////////////////////////
	
	
	$import_users_info = $xmlNode->createELement("import_users_info"); //NÓ RAIZ DO DOCUMENTO XML
		
	///////////////////// PREENCHENDO O NÓ JOURNAL //////////////////////////////
	$exportedJournalNameNode = $xmlNode->createElement("name", $arr["exportedJournalName"]); //CRIA O NÓ NAME DO JOURNAL EXPORTADO
	
	$journalNode = $xmlNode->createElement("journal"); //CRIA O NÓ JOURNAL
	
	$journalNode->appendChild($exportedJournalNameNode);
	/////////////////////////////////////////////////////////////////////////
	
	
	///////////////////////////////// PREENCHENDO O NÓ CHANGED_USERS /////////////////////////////////////////////////////////
	$changed_usersNode = $xmlNode->createElement("changed_users"); //CRIA O NÓ CHANGED_USERS
	
	$num_usersNode = $xmlNode->createElement("number_of_users", $arr["num_user_changes"]);
	$changed_usersNode->appendChild($num_usersNode);
	
	$num_users_registeredNode = $xmlNode->createElement("changed_users_already_registered", $arr["num_changed_users_registered"]);
	$changed_usersNode->appendChild($num_users_registeredNode);
	
	$changed_usersNode->appendChild($arr["users"]);
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	/////////////////// CRIANDO OS NÓS PARA GUARDARAS QUANTIDADES DE USUÁRIOS DO ARQUIVO ORIGINAL  ///////////////////
	
	$num_users_originalNode = $xmlNode->createElement("num_users_original", $arr["num_users_original"]);
	$num_users_registeredNode = $xmlNode->createElement("num_users_already_registered", $arr["num_users_registered"]);
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	////////////////  PREENCHENDO IMPORT_USERS_INFO E COLOCANDO-O COMO RAIZ DO DOCUMENTO ///////////////////////////////////
	
	$import_users_info->appendChild($num_users_originalNode);
	$import_users_info->appendChild($num_users_registeredNode);
	$import_users_info->appendChild($journalNode);
	$import_users_info->appendChild($changed_usersNode);
	
	$xmlNode->appendChild($import_users_info);
}
// FIM DE fillImportUsersInfo


function addChangedUser(&$changedUsersNode, &$xmlNode, $userInfo) {

	$username_node = $xmlNode->createElement("username");
	$username_new = $xmlNode->createElement("new", $userInfo["new_username"]);
	$username_old = $xmlNode->createElement("old", $userInfo["username"]);
	$username_node->appendChild($username_old);
	$username_node->appendChild($username_new);
	
	$firstname_node = $xmlNode->createElement("firstname", $userInfo["firstname"]);
	$middlename_node = $xmlNode->createElement("middlename", $userInfo["middlename"]);
	$lastname_node = $xmlNode->createElement("lastname", $userInfo["lastname"]);
	$registered_node = $xmlNode->createElement("already_registered", $userInfo["registered"]);
	$email_node = $xmlNode->createElement("email", $userInfo["email"]);
	
	$user_changed = $xmlNode->createElement("user");
	$user_changed->appendChild($firstname_node);
	$user_changed->appendChild($middlename_node);
	$user_changed->appendChild($lastname_node);
	$user_changed->appendChild($username_node);
	$user_changed->appendChild($email_node);
	$user_changed->appendChild($registered_node);
	
	//GUARDAR O user_changed NOS USERS DO ARQUIVO username_changes.xml
	$changedUsersNode->appendChild($user_changed);
}
//FIM DE addChangedUser


function processUsers($user, &$changedUsers, &$arrInfo, &$xmlNode, &$conn) {
	
	$arrInfo["num_users_original"]++;
	$username = $user->getElementsByTagName("username")->item(0)->nodeValue;
	$username = $conn->real_escape_string($username);
	
	//PRIMEIRO ACERTANDO OS DADOS DOS interests PARA NÃO TER REPETIDO NO MESMO USUÁRIO
	fixDuplicateInterests($user);
	////////////////////////////////////////////////////////////////////////////////////
	
	$middlename = "";
	$firstname = $user->getElementsByTagName("first_name")->item(0)->nodeValue;
	$lastname = $user->getElementsByTagName("last_name")->item(0)->nodeValue;
	if ($user->getElementsByTagName("middle_name")->length == 1)  $middlename = $user->getElementsByTagName("middle_name")->item(0)->nodeValue;
	$email = $user->getElementsByTagName("email")->item(0)->nodeValue;
	$new_username = "";
	$registered = 0;
	
	//variável para controlar qual usuário será salvo no username_changes.xml
	$changeUsername = false;
	
	
	//VERIFICANDO PRIMEIRO SE O USUÁRIO JÁ FOI SALVO NO BANCO DE DADOS COM OUTRO USERNAME//////////////////////////
	$email = $conn->real_escape_string($email);
	$query = $conn->query("SELECT first_name, middle_name, last_name, email, username FROM users WHERE email='$email'");
	
	if ($query->num_rows > 0) {
		$registered = 1; // O USUÁRIO JÁ FOI REGISTRADO
		//echo "usuário já inscrito\n";
		$arrInfo["num_users_registered"]++;
		$db_user = $query->fetch_object();
		if ($username !== $db_user->username) {
			$changeUsername = true;
			$new_username .= $db_user->username;
			$arrInfo["num_changed_users_registered"]++;
		}
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	//SENÃO ENCONTRAR O EMAIL DO USUÁRIO É PORQUE ELE AINDA NÃO ESTÁ NO BANCO DE DADOS E DEVE-SE VERIFICAR SE SEU USERNAME ESTÁ 
	//DISPONÍVEL, MODIFICANDO-O SE NECESSÁRIO //////////////////////////////////////////////////////////////////////////////////
	else {
		
		$query = $conn->query("SELECT first_name, middle_name, last_name, email FROM users WHERE username='$username'");
		
		// USERNAME JÁ EM USO ///////////////////
		if ($query->num_rows > 0) {
			$changeUsername = true;
			//new xml tags to put in user
			$registered = 0;
			$db_user = $query->fetch_object();
			
			$num = 2;
			$name_ok = false;
			$new_username .= $username . $num;
			//testando novos username para ver qual pode ser utilizado pelo usuário //////////////////////
			while (!$name_ok) {
				$new_query = $conn->query("SELECT COUNT(*) as count FROM users WHERE username='$new_username'");
				if ($new_query->fetch_row()[0] > 0) {
					$num++;	
					$new_username = $username . $num;
				}
				else {
					$name_ok = true;	
				}
			}
			
			if ($email == $db_user->email) {
				$arrInfo["num_users_registered"]++;
				$registered = 1;
			}
			else {
				//só muda o username no xml se o usuário ainda não estiver no banco de dados
				$user->getElementsByTagName("username")->item(0)->nodeValue = $new_username;
			}
		}
		////// FIM DO IF USERNAME JÁ EM USO//////////
	}
	//FIM DO ELSE INDICANDO QUE O USUÁRIO AINDA NÃO ESTÁ NO BANCO DE DADOS /////////////////////////////////
	
	
	//++++++++++++++++ SE PRECISAR MUDAR O USERNAME +++++++++++++++++++++++++++++++++++++++++++++++++++++
	if ($changeUsername) {
		//COLOCANDO O USER NO ARQUIVO username_changes.xml///////////////////////////////////////////////
		
		$userInfo = array(
			"new_username" => $new_username,
			"username" => $username,
			"firstname" => $firstname,
			"middlename" => $middlename,
			"lastname" => $lastname,
			"registered" => $registered,
			"email" => $email
		);
		addChangedUser($changedUsers, $xmlNode, $userInfo);
		$arrInfo["num_user_changes"]++;
		//////////////////////////////////////////////////////////////////////////////////////////////////
	}
	//+++++++++++++++++ FIM DO IF changeUsername ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
}
//FIM DE processUsers

function getIndentationFile() {
	$indentFile = "indent.xsl";
	$cwd =  getcwd();
	$maxTries = 3;
	$try = 0;

	while ($try < $maxTries) {
		$try++;

		$ls = scandir($cwd);

		if (in_array($indentFile, $ls)) {
			break;
		}
		else if (in_array("OJSapp", $ls)){
			$cwd .= "/OJSapp";
		}
		else {
			echo "\nCould not locate the .xsl file for indentation.\n";
			$indentFile = readline("Please enter the name of the indentation .xsl file with its path: ");
			break;
		}
	}

	if ($try >= $maxTries) echo "\nReached maximum number of tries.\n";
	return "$cwd/$indentFile";
}


function modify($filename) {
	
	$db_host = readline("Digite o host do banco de dados do ojs de destino: ");
	$db_username = readline("Digite o username do banco de dados do ojs de destino: ");
	$db_password = readline("Digite o password do banco de dados do ojs de destino: ");
	$db_name = readline("Digite o nome do banco de dados do ojs de destino: ");
	
	$error = "";
	
	$xml = new DOMDocument;

	$xml_username_changes = new DOMDocument('1.0', 'utf-8');
	$users = $xml_username_changes->createElement("users"); // NÓ PARA GUARDAR OS USUÁRIOS MODIFICADOS
	
	if (!$xml->load($filename)) {
		echo "\nUsando encoding iso-8859-1 para abrir o xml...\n";
		$strXml = file_get_contents($filename);
		//$xml = new DOMDocument('1.0', 'iso-8859-1');
		if (!$xml->loadXml(utf8_encode($strXml))) {
			exit("\nNão foi possível abrir o arquivo $filename.\n");
		}
	}
	
	$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
	
	if ($conn->connect_errno) {
		exit("Failed to connect to MySQL: " . $mysqli->connect_error);
	}
	else {
		echo "\nConexão com o banco $db_name realizada com sucesso\n";
	}
	
	echo "\nMudando os usernames que já estão em uso no banco de dados $db_name...\n";
	
	$userNodes = $xml->getElementsByTagName("user"); //VARIÁVEL COM TODOS OS USERS DO XML ORIGINAL

	
	$info = array(
		"num_users_original" => 0, //VARIÁVEL PARA SABER QUANTOS USUÁRIOS POSSUI O ARQUIVO ORIGINAL
		"num_user_changes" => 0, //O NÚMERO DE USUÁRIOS QUE PRECISOU ALTERAR O USERNAME
		"num_users_registered" => 0, //QUANTOS USUÁRIOS DO ARQUIVO ORIGINAL JÁ ESTAVAM REGISTRADOS
		"num_changed_users_registered" => 0 //QUANTOS USUÁRIOS QUE PRECISARAM MUDAR DE USERNAME JÁ ESTAVAM REGISTRADOS
	);
	
	//LOOP PARA PERCORRER TODOS OS USUÁRIOS NO ARQUIVO XML///////////////////////////////////////
	foreach ($userNodes as $user) {
		processUsers($user, $users, $info, $xml_username_changes, $conn);
		//$user é o user atual
		//$users é o XmlNode com os usuários modificados
	}
	//FIM DO LOOP PARA PERCORRER TODOS OS USUÁRIO DO ARQUIVO//////
	
	
	$conn->close();
	
	// SÓ CRIA OS ARQUIVOS XML SE TIVER QUE FAZER ALTERAÇÃO EM ALGUM USUÁRIO
	if ($info["num_user_changes"] > 0) {

		$changed_filename = "changed_$filename";
		echo "Os usuários serão salvos no arquivo $changed_filename.";
		
		$keep_filename = readline("Deseja manter este nome de arquivo (S/n)? ");
		
		if ($keep_filename === "N" || $keep_filename === "n") {
			$changed_filename = readline("\nDigite o nome que deseja para o arquivo: ");
		}
		
		if ($xml->save("$changed_filename")) {
			echo "\nUsernames modificados com sucesso e salvo no arquivo $changed_filename.\n";
		}
		else {
			$error .= "\nNão conseguiu salvar o arquivo $changed_filename.\n";
		}
		
		$exportedJournalName = readline("Digite o nome da revista de onde os usuários foram exportados: ");
		
		$arrayImportUsersInfo = array(
			"num_user_changes" => $info["num_user_changes"],
			"num_changed_users_registered" => $info["num_changed_users_registered"],
			"users" => $users,
			"num_users_original" => $info["num_users_original"],
			"num_users_registered" => $info["num_users_registered"],
			"exportedJournalName" => $exportedJournalName
		);
		
		
		///////////  CRIANDO O ARQUIVO de username_changes.xml ///////////////////////////////////////////////
		fillImportUsersInfo($xml_username_changes, $arrayImportUsersInfo);
		
		$indentFile = getIndentationFile(); //the previous function in this same script

		$xsl = new DOMDocument;
		$xsl->load($indentFile);
		
		$proc = new XSLTProcessor;
		
		$proc->importStyleSheet($xsl);
		
		$newfilename = $exportedJournalName."_username_changes.xml";
		
		echo "\nOs usuários serão salvos no arquivo $newfilename.";
		
		$keep_filename = readline("Deseja manter este nome de arquivo (S/n)? ");
		
		if ($keep_filename === "N" || $keep_filename === "n") {
			$newfilename = readline("\nDigite o nome que deseja para o arquivo: ");
		}
		
		if (file_put_contents("$newfilename", $proc->transformToXML($xml_username_changes))) {
			echo "\n$newfilename criado com sucesso!\n";
		}
		else {
			$error .= "Não foi possível criar o arquivo $newfilename\n";
		}
		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		
	}//fim do if num_changes > 0
	
	else {
		$error = "Não há nenhum username repetido";
	}
	
	if ($error === "") {
		return true;
	}
	else {
		echo "\n$error";
		return false;
	}
}
//FIM DE modify

$users_file = readline("Digite o nome do arquivo com os usuários: ");
modify($users_file);
