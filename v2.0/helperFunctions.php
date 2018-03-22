<?php
/**

This is a library with miscellaneous functions used throughout the app

FUNCTIONS DEFINED IN THIS SCRIPT

01) myConnect
02) askConnectData
03) askAndConnect
04) saveMyXml
05) isStandardName
06) setNewName
07) getJournalFromArray
08) chooseJournal
09) files2get
10) escapeString // out of use
11) cut2fit
12) validateData
13) arrayToXml
14) xmlToArray
15) echoElement
16) same2
17) myExecute
18) newIdField
19) getNewId
20) backupXml
21) countErrors
22) translate2utf8 // out of use
23) translateArray2utf8
24) processCollation


Developed in 2017 by Bernardo Amado

*/


require_once("config.php");
require_once("cleanEncoding.php");

// #01)
function myConnect($host, $user, $pass, $db, $charset = "utf8") {
	try {
		$options = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset",
		);
		echo "\nTrying to connect with charset = $charset ............ ";
		$conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass, $options);
		echo "Ok!\n";
		return $conn;
	}
	catch (PDOException $e) {
		exit("\nError when trying to connect to the database. ". $e->getMessage() . "\n");
	}
}

// #02)
function askConnectData() {
	$host = readline("database host: ");
	$user = readline("database user: ");
	$pass = readline("password for $user: ");
	$db = readline("database name: ");
	
	return ["host" => $host, "user" => $user, "pass" => $pass, "db" => $db];
}


// #03)
/**
function that asks for the connection data and tries to connect returning the connection
*/
function askAndConnect() {
	$connData = askConnectData();
	
	return myConnect($connData["host"], $connData["user"], $connData["pass"], $connData["db"]);
}


// #04)
function saveMyXml(&$xml, $filename, $migrate) {
	$keepFilename = true;
	$updatedFileName = null; 
	
	if ($filename !== null) {
		if ($migrate) {
			$updatedFileName = "migrated_$filename";
		}
		$updatedFileName = $filename;
		$resp = readline("The file will be saved by the name of '$updatedFileName'. Do you want to keep this name? (Y/n) : ");
		if ($resp === "n" || $resp === "N") {
			$keepFilename = false;
		}
	}
	else {
		$keepFilename = false;
	}
	
	if (!$keepFilename) {
		$updatedFileName = readline("Enter the name you want for the file: ");
	}
	
	if ($xml->save($updatedFileName)) {
		echo "\nFile '$updatedFileName' saved successfully!\n"; 
		return true;
	}
	else {
		echo "\nCould not save the file '$updatedFileName'.\n";
	}
	
	return false;
}


// #05)
function isStandardName($name) {
	$words = explode("-", $name);
	if (sizeof($words) === 4) {
		if (is_numeric($words[0]) && is_numeric($words[1]) && is_numeric($words[2])) {
			return true;
		}
	}
	return false;
}


// #06)
function setNewName($name, &$dataMapping, &$msg = null) {
	$words = explode("-", $name);
	if (array_key_exists($words[0], $dataMapping["article_id"]) && array_key_exists($words[1], $dataMapping["file_id"])) {
		array_splice($words, 0, 1, $dataMapping["article_id"][$words[0]]);
		array_splice($words, 1, 1, $dataMapping["file_id"][$words[1]]);
		$newName = implode("-", $words);
		return $newName;
	}
	else {
		$msg = "";
		if (!array_key_exists($words[0], $dataMapping["article_id"])) {
			$msg .=  "Article_id " . $words[0] . " not in dataMappings. \n";
		}
		if (!array_key_exists($words[1], $dataMapping["file_id"])) {
			$msg .= "File_id " . $words[1] . " not in dataMappings. \n";
		}
	}
	
	return null;
}


// #07)
/**
return the Journal from the array filtering by journal_id or path, the default being journal_id
$value is the value to search. Returns the journal array if match found, or false otherwise
*/
function getJournalFromArray($array, $value, $filter = null) {
	
	if ($filter === null) {
		$filter = "journal_id";
	}
	
	foreach($array as $journal) {
		if ($journal[$filter] === $value) {
			return $journal;
		}
	}
	
	return false;
}


// #08)
function chooseJournal($conn) {
	$journals = array();
	
	$res = $conn->query("SELECT journal_id, path FROM journals ORDER BY journal_id");

	while ($jou = $res->fetch(PDO::FETCH_ASSOC)) {
		array_push($journals,  $jou);
	}
	
	$res = null;
	
	//colocar um menu na tela para o usuário selecionar de qual revista ele deseja pegar os ids
	echo "\nHosted journals:\n";
	foreach($journals as $journal) {
		echo $journal["journal_id"] . " - " . $journal["path"] . "\n";
	}
	
	$jId = readline("\nEnter the id of the journal wanted: ");
	
	echo "\n";
	
	return getJournalFromArray($journals, $jId);
}


// #09)
function files2get($xml) {
	$article_files = $xml->getElementsByTagName("files");
	$filesMapping = array();
	
	foreach($article_files as $artFile) {
		$fileNames = $artFile->getElementsByTagName("file_name");
		foreach ($fileNames as $fileName) {
			$fileNewName = null;
			$prev = $fileName->previousSibling;
			if ($prev !== null) {
				if ($prev->nodeName === "file_new_name") {
					$fileNewName = $prev->nodeValue;
				}
			}
			else {
				$file_new_name = $fileName->parentNode->getElementsByTagName("file_new_name");
				if ($file_new_name->length > 0) {
					$fileNewName = $file_new_name->item(0)->nodeValue;
				}
			}
			if ($fileNewName !== null) {
				$filesMapping[$fileName->nodeValue] = $fileNewName;
			}
		}
	}
	
	return $filesMapping;
}


// #10)
/*function escapeString($str) {
	//$escaped = html_entity_decode($str);
	//$escaped = html_entity_decode($escaped);
	
	
	
	$escaped = html_entity_decode($escaped);
	//$escaped = htmlspecialchars($escaped);
	//$escaped = htmlentities($escaped);
	return $escaped;
}*/


// #11)
function cut2fit($str, $size) {
	$sizeOk = false;
	$test = $str;
	
	while (!$sizeOk) {
		//$escaped = escapeString($test);
		//$escaped = $str;
		if (strlen($test) <= $size) {
			$sizeOk = true;
			//$test = $escaped;
		}
		else {
			$test = substr($test, 0, -1);
		}
	}
	
	return $test;
}


// #12)
function validateData($type, &$data) {
	
	global $tables; //from config.php
	
	$properties = $tables[$type]["properties"];
	foreach ($data as $attr => $value) {
		if (array_key_exists($attr, $properties)) {
			if ($value === null) {
				if ($properties[$attr]["null"] === "no") {
					$data[$attr] = $properties[$attr]["default"];
				}
			}
			else if ($value === "" || $value === "0000-00-00" || $value === "0000-00-00 00:00:00"){
				$data[$attr] = $properties[$attr]["default"];
			}
			else if (strpos($properties[$attr]["type"], "varchar") !== false){
				//is of type varchar
				$size = (int) substr($properties[$attr]["type"], 8, -1);
				
				$newValue = cut2fit($value, $size);
				
				$data[$attr] = $newValue;
				
			}
			else if ($properties[$attr]["type"] === "text"){
				//is of type text
				
				//$data[$attr] = escapeString($value);
				$data[$attr] = $value;
			}
		}
	}
}


// #13)
function arrayToXml(&$xml, &$currentNode, $dataFrame, $args = null) {
	
	///////////////////////  SETTING THE PARAMETERS  /////////////////////////////////////////////
	$addRootNode = false;
	$rootNode = null;
	$journal = null;
	$type = null;
	$isAssociative = false;
	
	if (is_array($args)) {
		if (array_key_exists("journal", $args)) {
			$journal = $args["journal"];
		}
		
		if (array_key_exists("rootNode", $args)) {
			if (array_key_exists("addRootNode", $args)) {
				$addRootNode = $args["addRootNode"];
				$rootNode = $xml->createElement($args["rootNode"]);
			}
			else {
				$rootNode = $xml->getElementsByTagName($args["rootNode"])->item(0);
			}

			if (is_array($journal)) {
				$rootNode->setAttribute("journal_original_path", $journal["path"]);
				$rootNode->setAttribute("journal_original_id", $journal["journal_id"]);
			}
		}
		if (array_key_exists("type", $args)) {
			$type = $args["type"];
		}
	}
	
	
	if ($type === null) {
		$type = "article";
	}
	else {
		//if the type is on the plural
		if (substr($type, strlen($type) - 1, 1) === "s") {
			//remove the 's' at the final to make it single
			$type = substr($type, 0, strlen($type) - 1);
		}
	}
	
	$fields = null;
	
	if (array_key_exists(0, $dataFrame)) {
		$isAssociative = false;
	}
	else {
		$isAssociative = true;
	}
	
	if ($isAssociative) {
		$fields = array_keys($dataFrame);
	}
	else {
		if (is_array($dataFrame[0])) {
			$fields = array_keys($dataFrame[0]);
		}
	}
	///////////////////////--------------------------------------------/////////////////////////////////////////
	
	if ($isAssociative) {
		$data = $dataFrame;
		//$dataNode = $xml->createElement($type);
		foreach($fields as $field) {
			$fieldNode = $xml->createElement($field);
			if (is_array($data[$field])) {
				$arguments = array();
				$arguments["type"] = $field;
				arrayToXml($xml, $fieldNode, $data[$field], $arguments);
			}
			else {
				$value = null;
				if (array_key_exists($field, $data)) {
					//$value = htmlspecialchars($data[$field]);
					//$value = htmlentities($data[$field]);
					//$value = $data[$field];
					if (is_string($data[$field])) {
						$value = htmlentities($data[$field]);
					}
					else {
						$value = $data[$field];
					}
				}
				$fieldNode->nodeValue = $value;
			}
			$currentNode->appendChild($fieldNode);
		}
		//end of foreach field
		
		
	}
	//end of if isAssociative
	else {
		foreach($dataFrame as $data) {
			$dataNode = $xml->createElement($type);
			
			foreach($fields as $field) {
				$fieldNode = $xml->createElement($field);
				if (is_array($data[$field])) {
					$arguments = array();
					$arguments["type"] = $field;
					arrayToXml($xml, $fieldNode, $data[$field], $arguments);
				}
				else {
					$value = null;
					if (array_key_exists($field, $data)) {
						//$value = htmlspecialchars($data[$field]);
						//$value = htmlentities($data[$field]);
						//$value = $data[$field];
						if (is_string($data[$field])) {
							$value = htmlentities($data[$field]);
						}
						else {
							$value = $data[$field];
						}
					}
					$fieldNode->nodeValue = $value;
				}
				$dataNode->appendChild($fieldNode);
				
			}
			//end of foreach field
			
			if ($addRootNode) {
				$rootNode->appendChild($dataNode);
			}
			else {
				$currentNode->appendChild($dataNode);
			}
			
		}
		
		//end of foreach dataFrame
	}
	
	if ($addRootNode) {
		$currentNode->appendChild($rootNode);
	}
	
}


// #14)
function xmlToArray($xml, $deep = false) {
	
	//if deep is true will get all the xml content including inner tags
	//by default deep is false meaning will get only the values of the tags without inner tags
	
	if ($xml->nodeType == XML_TEXT_NODE) {
		return html_entity_decode($xml->textContent);
	}
	
	if ($xml->hasChildNodes() && $deep) {
		$childNodes = $xml->childNodes;
		$nodeChild = $childNodes->item(0);
		if (substr($xml->nodeName, 0, strlen($xml->nodeName) - 1) === $nodeChild->nodeName || $nodeChild->nodeName === "article") {
			//child node name is the singular form of the parent node name
			$children = array();
			foreach ($childNodes as $child) {
				array_push($children, xmlToArray($child, true)); //recursive call
			}
			return $children;
		}
	}
	
	$element = array();
	
	foreach ($xml->childNodes as $node) {
		$saveValue = true;
		$name = $node->nodeName;
		
		if ($node->hasChildNodes()) {
			
			$nodeChild = $node->childNodes->item(0); 
			
			if ($nodeChild->nodeType === XML_TEXT_NODE) {
				// decode the htmlentities because arrayToXml uses the function htmlentities to encode special characters
				
				$value = html_entity_decode($nodeChild->textContent);
				
				
				/*
				//show the xml and array values //////////////
				echo "\n" . $node->nodeName . ":\n";
				echo "xml_value = ". $nodeChild->textContent;
				echo "\narray_value = " . $value;
				//////////////////////////////////////////////
				*/
			}
			
			else if ($deep){
				$value = xmlToArray($node, true); // recursive call
			}
			
			else {
				//the child is not a text node, probably is another dom element
				//since deep is false the value shouldn't be stored;
				$saveValue = false;
			}
			
		}
		else {
			// decode the htmlentities because arrayToXml uses the function htmlentities to encode special characters
			$value = html_entity_decode($node->nodeValue);
		}
		
		if ($saveValue) {
			$element[$name] = $value;
		}
		
	}
	
	return $element;
}


// #15)
function echoElement($element, $indentation = 0) {
	$indentText = "";
	for ($i = 0; $i < $indentation; $i++) {
		$indentText .= " ";
	}
	foreach ($element as $key => $value) {
		
		echo "\n" . $indentText . $key . ": ";
		if (is_array($value)) {
			echoElement($value, $indentation + 4); //recursive call
		}
		else {
			echo $value;
		}
	}
}


// #16)
function same2($arr1, $arr2, &$args = null) {
	
	//echo "\n\n------------ INSIDE SAME 2 -------------\n\n";
	
	$map = null;
	$notCompare = null;
	$type = "article";
	$compare = "all";
	$matches = 0;
	
	if (is_array($args)) {
		
		if (array_key_exists("compare", $args)) {
			$compare = $args["compare"];
		}
		
		if (array_key_exists("notCompare", $args)) {
			$notCompare = $args["notCompare"];
		}
		
		if (array_key_exists("type", $args)) {
			$type = $args["type"];
		}
		
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	global $tables; //from config.php
	global $idFields; //from config.php
	$fields = $tables[$type]["attributes"];
	
	////////////////////////////////////////////////////////////////////////////
	
	$map = array();
	
	foreach ($fields as $field) {
		$matchedField = $field;
		if (in_array($field, $idFields)) {
			$matchedField = newIdField($field);
			$map[$matchedField] = $field;
		}
		$map[$field] = $matchedField;
	}
	
	foreach ($tables[$type]["primary_keys"] as $pk) {
		if (array_key_exists($pk, $map)){
			unset($map[$pk]);
		}
	}
	
	foreach ($tables[$type]["foreign_keys"] as $fk) {
		if (array_key_exists($fk, $map)){
			unset($map[$fk]);
		}
	}
	
	
	////////////////////////////////////////////////////////////////////////////
	
	if ($compare === "all") {
		//it's all ok
	}
	else if ($compare === "almost all") {
		
		if (is_array($notCompare)){
			foreach ($notCompare as $key) {
				unset($map[$key]);
			}
		}
		else if (array_key_exists($notCompare, $map)){
			unset($map[$notCompare]);
		}
	}
	else if (is_array($compare)) {
		$map = $compare;
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	foreach ($map as $key => $value) {
		if (array_key_exists($key, $arr1) && array_key_exists($value, $arr2)) {
			if ($arr1[$key] == $arr2[$value]) {
				$matches++;
			}
			else {
				return 0;
			}
		}
	}
	if ($matches > 0) {
		return 1;
	}
	else {
		//there was no match between the xmlItem and the dbItem
		return -1;
	}
}


// #17)
function myExecute($mode, $type, $arrData, &$stmt, &$errors, $args = null) {
	
	$params = $arrData["params"];
	$data = $arrData["data"];
	
	foreach ($params as $param) {
		$name = $param["name"];
		$attr = $param["attr"];
		
		if (array_key_exists("type", $param)) {
			$stmt->bindParam($name, $data[$attr], $param["type"]);
		}
		else {
			$stmt->bindParam($name, $data[$attr]);
		}
	}
	
	if ($stmt->execute()) {
		return true;
	}
	else {
		global $tables; //from config.php
		
		$pks = $tables[$type]["primary_keys"];
		
		$error = array();
		foreach ($pks as $pk) {
			$error[$pk] = $data[$pk];
		}
		$error["error"] = $stmt->errorInfo();
		
		array_push($errors[$type][$mode], $error);
		
		return false;
	}
}

// #18)
function newIdField($field) {
	$words = explode("_", $field);
	array_splice($words, -1, 0, "new");
	$newField = implode("_", $words);
	return $newField;
}

// #19)
function getNewId($type, &$stmt, &$data, &$dataMapping, &$errors, $args = null) {
	
	global $tables; //from config.php
	
	$pk = $tables[$type]["primary_keys"][0];
	
	if ($stmt->execute()) {
		
		$arguments = array();
		$arguments["type"] = $type;
		
		if (is_array($args)) {
			if (array_key_exists("compare", $args)) {
				$arguments["compare"] = $args["compare"];
			}
			if (array_key_exists("notCompare", $args)) {
				$arguments["notCompare"] = $args["notCompare"];
			}
		}
		
		$newId = newIdField($pk);
		
		while ($last = $stmt->fetch(PDO::FETCH_ASSOC)) {
			
			if (array_key_exists($pk, $last)) {
				$data[$newId] = $last[$pk];
			}
			
			$same = same2($data, $last, $arguments);
			
			if ( $same > 0) {
				if (!array_key_exists($data[$pk], $dataMapping[$pk])) {
					$dataMapping[$pk][$data[$pk]] = $data[$newId];
				}
				return true;
			}
			else if ($same === -1){
				echo "Returned -1";
			}
			else if ($same === 0) {
				echo "returned 0";
			}
		}
		//echo "\n\n--------------------- RETURNED FALSE -----------------------\n\n";
		//exit();
	}
	else {
		$error =  [$pk => $data[$pk], "error" => $stmt->errorInfo()];
		array_push($errors[$type]["getNewId"], $error);
	}
	return false;
}


// #20)
/**
creates a copy of the current file appending the datetime of the backup to its name
returns the name of the new file or false if could not copy
*/
function backupXml($filename) {
	
	$now = date("Y-m-d_H\hi\ms\s"); // will get the time like 2017-12-31_05h16m49s
	$name = "";
	
	if (substr($filename, -4) === ".xml") { //the last 4 characters of $filename
		$name = substr($filename, 0, -4); 
		$name .= $now . ".xml";
	}
	else {
		$name = $filename . "." . $now;
	}
	
	if (copy($filename, $name)) {
		return $name;
	}
	
	return false;
}


// #21)
/**
Returns the number of error entries in the array $errors
*/
function countErrors($errors) {
	$count = 0;
	foreach ($errors as $field) {
		if (array_key_exists("insert", $field)) {
			$count += count($field["insert"]);
		}
		if (array_key_exists("update", $field)) {
			$count += count($field["update"]);
		}
	}
	
	return $count;
}


// #22)
/**
gets a string data and translates to utf-8
*/
/*function translate2utf8($str) {
	$encoding = mb_detect_encoding($str);
	
	$in_encoding = $encoding;
	$out_encoding = $encoding;
	
	//echo "\nactual encoding is $encoding\n";
	
	if ($encoding === "UTF-8") {
		$out_encoding = "Windows-1252";
	}
	else {
		//$out_encoding = "UTF-8";
	}
	
	return iconv($in_encoding, $out_encoding."//TRANSLIT", $str);

}*/
//use the translate2utf8 from cleanEncoding.php


// #23)
/**
transforms the array data into the selected
*/
function translateArray2utf8(&$array) {
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			translateArray2utf8($array[$key]);
		}
		else if (is_string($value)){
			$array[$key] = unmessEncoding($value); // from cleanEncoding.php
		}
	}
}

// #24)
/**
applies translateArray2utf8 if needed
*/
function processCollation(&$item, $tableName, $collations) {
	if (array_key_exists($tableName, $collations)) {
		if (strpos($collations[$tableName], "latin") !== false) {
			//the item collation is of type ISO-8859-1 or very similar
			translateArray2utf8($item);
		}
	}
}

//#25)

function OJSappRootDir() {
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

// #26)
/**
returns an array with the tables collations and engines
*/

function getTablesInfo($conn = null, $dbName = null) {
	
	if ($conn === null) {
		$connData = AskConnectData(); // from helperFunctions.php function #02
		
		if ($dbName === null) $dbName = $connData["db"]; //saving the dbName to use later
		
		$conn = myConnect($connData["host"], $connData["user"], $connData["pass"], $connData["db"]); // from helperFunctions.php function #01
	}
	
	$stmt = $conn->prepare("SELECT TABLE_SCHEMA, TABLE_NAME, TABLE_COLLATION, ENGINE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=:tableSchema");
	
	$stmt->bindParam(":tableSchema", $dbName, PDO::PARAM_STR);
	
	if ($stmt->execute()) {
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	else {
		echo "\nThere was an error:\n";
		print_r($stmt->errorInfo());
		return false;
	}
	
}