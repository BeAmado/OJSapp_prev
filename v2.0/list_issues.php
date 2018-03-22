<?php

$host = readline("Digite o host onde está hospedado o banco de dados: ");
$user = readline("Digite o nome do usuario do banco de dados: ");
$pass = readline("Digite a senha para este usuario do banco de dados: ");
$db = readline("Digite o nome do banco de dados: ");

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_errno) {
	exit("Falha ao conectar com o MySQL: " . $conn->connect_error . "\n");
}


//pegar os nomes das revistas hospedadas
$journals = array();

$res = $conn->query("SELECT journal_id, path FROM journals");

//
while ($jou = $res->fetch_object()) {
	array_push($journals,  array("id" => $jou->journal_id, "path" => $jou->path));
}

//colocar um menu na tela para o usuário selecionar de qual revista ele deseja pegar os ids
echo "Revistas hospedadas:\n ";
foreach($journals as $journal) {
	echo $journal["id"] . " - " . $journal["path"] . "\n";
}

$journal_id = readline("Digite o número da revista que deseja listar as edições: ");

$id = filter_var($journal_id, FILTER_VALIDATE_INT);

$res = $conn->query("SELECT issue_id FROM issues WHERE journal_id='$id'");

$issues_ids = "";
$filename = "";

foreach($journals as $journal) {
	if ($journal["id"] == $journal_id) {
		$filename = $journal["path"] . "_issues_ids.txt";
	}
}

while ($row = $res->fetch_row()) {
	$issues_ids .= " ".$row[0];
}

$conn->close();

if ($issues_ids === "") {
	exit("\nEsta revista não possui nenhuma edição.\n");
}

echo "\nCriando o arquivo '$filename'...\n";

if (file_put_contents($filename, $issues_ids)) {
	echo "Arquivo $filename criado com sucesso!\n";
}
else {
	echo "Houve algum erro e não foi possível criar o arquivo '$filename'.\n";
}
