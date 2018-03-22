<?php

$filename = readline("Digite o nome do arquivo a ser indentado: ");
/*$option = readline("Escolha um dos charsets ( 1 - 'utf-8' | 2 - 'iso-8859-1' ) [default utf-8]: ");

if ($option == 2) {
	$charset = "ISO-8859-1";
}
else {
	$charset = "UTF-8";
}

echo "\nChosen charset: $charset\n";
*/

$charset = "UTF-8";

$xml = new DOMDocument("1.0", $charset);

@$xml->loadHTMLFile($filename);

$xsl = new DOMDocument("1.0", $charset);
$xsl->load("indent.xsl");

$proc = new XSLTProcessor;
$proc->importStyleSheet($xsl);

if (file_put_contents("indented_$filename", $proc->transformToXML($xml))) {
	echo "\narquivo indentado com sucesso e criado indented-$filename\n";
}
else {
	echo "\nNão foi possível criar o arquivo indented-$filename\n";
}
