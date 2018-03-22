<?php

/**
This is the main library for the OJSapp

//FUNCTIONS DEFINED IN THIS SCRIPT:
01) makeXmlDataMapping
02) saveDataMapping 
03) getDataMapping 
04) processFiles  --(getFiles) 
05) getUnpubArt 
06) setUnpubArt
07) getSections
08) setSections
09) getAnnouncements
10) setAnnouncements
11) getEmailTemplates (	DO NOT USE )
12) setEmailTemplates ( DO NOT USE )
13) getGroups
14) setGroups
15) treatExportErrors
16) treatImportErrors
17) menu
18) mainMenu
19) migrateFiles
20) myExport
21) myImport
22) myMigrate
23) myMain  /////////////// THE MAIN FUNCTION //////////////// 

Developed in 2017 by Bernardo Amado
*/

include_once("helperFunctions.php");
include_once("db2xml.php");
include_once("xml2db.php");

// #00)
/**
creates a backup of the dataMappings.xml
*/
function backupDataMapping($pathToXml = "dataMappings", $filename = "dataMappings.xml") {
	
	$now = date("Y-m-d_H:i:s");
	$name = "";
	
	if (substr($filename, -4) === ".xml") { //the last 4 characters of $filename
		$name = substr($filename, 0, -4); 
		$now = date("Y-m-d_H:i:s");
		$name .= $now . ".xml";
	}
	else {
		$name = $filename . "." . $now;
	}
	
	$filenameFull = $pathToXml . "/" . $filename;
	$nameFull = $pathToXml . "/backups/" . $name;
	
	if (copy($filenameFull, $nameFull)) {
		return $nameFull;
	}
	
	return false;
}


// #01)
/**
creates a xml data mapping from the array journalDataMapping which has the dataMappings for one specific journal
the structure will be :
<mappings>
	<field>
		<mapping>
			<old></old>
			<new></new>
		</mapping>
		...
		...
		...
	</field>
	...
	...
	...
</mappings>
where 'field' is the actual name of each field
*/
function makeXmlDataMapping(&$xml, &$mappings_node, $journalDataMapping) {
	foreach ($journalDataMapping as $field => $mapping) {
		$field_node = $xml->createElement($field);
		foreach ($mapping as $old => $new) {
			$mapping_node = $xml->createElement("mapping");
			
			$old_node = $xml->createElement("old", $old);
			$new_node = $xml->createElement("new", $new);
			
			$mapping_node->appendChild($old_node);
			$mapping_node->appendChild($new_node);
			
			$field_node->appendChild($mapping_node);
		}
		
		$mappings_node->appendChild($field_node);
	}
}


// #02)
/**
save the dataMapping for the specified journal in the dataMappings xml file

return values:
 
 -1 if could not load the file
  0 if could not save the data mapping in an xml file
  1 if the xml file with the data mapping was saved successfully
  
*/
function saveDataMapping($dataMapping, $journalName, $journalMapping = null, $pathToXml = "./dataMappings", $xmlFilename = "dataMappings.xml") {
	
	$xml = new DOMDocument("1.0", "UTF-8");
	
	$filename = $pathToXml . "/" . $xmlFilename;
	
	if (!$xml->load($filename)) {
		echo "\nCould not load '$filename'.\n";
		return -1;
	}
	
	//save a backup copy 
	backupDataMapping(); //from this script function #00 
	
	//the data mapping  //////
	$mappings = $xml->createElement("mappings");
	//arrayToXml($xml, $mappings, $dataMapping, ["type" => "mappings"]); // from helperFunctions.php function #13
	makeXmlDataMapping($xml, $mappings, $dataMapping); //from this script function #01
	//////////////////////////
	
	$data_mappings = $xml->getElementsByTagName("data_mappings")->item(0);
	
	$data_list = $data_mappings->getElementsByTagName("data_mapping");
	$append = true;
	if ($data_list->length > 0) {
		foreach ($data_list as $old_data) {
			if ($old_data->getAttribute("journal") === $journalName) {
				//put the new data_mapping in the file
				$old_mappings = $old_data->getElementsByTagName("mappings")->item(0);
				$old_data->replaceChild($mappings, $old_mappings);
				//$data_mappings->replaceChild($data_mapping, $old_data);
				$append = false;
				break;
			}
		}//end of the foreach
	}
	
	if ($append) {
		$data_mapping = $xml->createElement("data_mapping");
		
		$data_mapping->setAttribute("journal", $journalName);
		
		//the journal mapping/////
		$journal = $xml->createElement("journal");
		arrayToXml($xml, $journal, $journalMapping, ["type" => "journal"]); //from helperFunctions.php function #13
		//////////////////////////
		
		$data_mapping->appendChild($journal);
		$data_mapping->appendChild($mappings);
		
		$data_mappings->appendChild($data_mapping);
	}
	
	if ($xml->save($filename)) {
		echo "\n'$filename' successfully saved!\n";
		return 1;
	}
	
	return 0;
} 


// #03)
/**
get the dataMapping for the specified journal, from the $dataMappingXml file

return values:

 -1 if could not open the file
  0 if there is no data_mapping in the file
  $dataMapping array if found and null otherwise

*/
function getDataMapping($journalName, $dataMappingXml = "dataMappings.xml", $pathToXml = "./dataMappings") {
	
	$xml = new DOMDocument("1.0", "UTF-8");
	
	//$filename = $pathToXml . $dataMappingXml;
	$filename = $pathToXml . "/" . $dataMappingXml;
	
	if (!$xml->load($filename)) {
		echo "\nCould not open '$filename'.\n";
		return -1;
	}
	
	$data_mappings = $xml->getElementsByTagName("data_mapping");
	
	if ($data_mappings->length > 0) {
		foreach ($data_mappings as $data_mapping) {
			if ($data_mapping->getAttribute("journal") === strtolower($journalName)) {
				//$dataMapping = xmlToArray($data_mapping, true); //from helperFunctions.php function #14
				
				$dataMapping = array();
				
				/////////////////////// the journal mapping //////////////////////////////////
				$journal_mapping = $data_mapping->getElementsByTagName("journal")->item(0);
				
				$id_node = $journal_mapping->getElementsByTagName("id")->item(0);
				$journalOldId = $id_node->getElementsByTagName("old")->item(0)->nodeValue;
				$journalNewId = $id_node->getElementsByTagName("new")->item(0)->nodeValue;
				$dataMapping["journal_id"] = array($journalOldId => $journalNewId);
				
				$path_node = $journal_mapping->getElementsByTagName("path")->item(0);
				$journalOldPath = $path_node->getElementsByTagName("old")->item(0)->nodeValue;
				$journalNewPath = $path_node->getElementsByTagName("new")->item(0)->nodeValue;
				$dataMapping["journal_path"] = array($journalOldPath => $journalNewPath);
				///////////////////////////////////////////////////////////////////////////////
				
				
				//////////////////////  the data mappings  ////////////////////////////////////
				$mappings_node = $data_mapping->getElementsByTagName("mappings")->item(0);
				
				foreach ($mappings_node->childNodes as $field_node) {
					
					$fieldName = $field_node->tagName;
					$fieldMapping = array(); // array to store the mappings of the field
					
					$mapping_nodes = $field_node->getElementsByTagName("mapping"); //the mappings for the field
					
					// loop to map the old field value to the new field value
					foreach ($mapping_nodes as $mapping_node) {
						$old = $mapping_node->getElementsByTagName("old")->item(0)->nodeValue;
						$new = $mapping_node->getElementsByTagName("new")->item(0)->nodeValue;
						$fieldMapping[$old] = $new;
					}
					
					$dataMapping[$fieldName] = $fieldMapping;
				}
				///////////////////////////////////////////////////////////////////////////////
				
				return $dataMapping;
			}
		}//end of the foreach data_mapping
	}
	else {
		echo "\nThere is no data mapping in '$filename'.\n";
		return 0;
	}
	
	return null;
}


// #04)
/**
get the files from the old journal and copy them to the new journal files updating their names
return values:
   0 if all went well
   1 if could not know the journal_id
*/
function processFiles($filesDirOld, $filesDirNew, &$dataMapping, &$copied, &$errors) {
	$fileCopyErrors = array();
	$copiedFiles = array();
	$filesTranslation = array(
		"PB" => "public",
		"AT" => "attachment",
		"SP" => "supp",
		"CE" => "submission/copyedit",
		"SM" => "submission/original",
		"RV" => "submission/review",
		"ED" => "submission/editor",
		"LE" => "submission/layout"
	);
	$files = scandir($filesDirOld);
	
	//echo "\n inside process files \n";
	
	////// get the journalId /////////
	
	//echo "\ngetting the journal id\n";
	
	$values = array_values($dataMapping["journal_id"]);
	if (count($values) !== 1) {
		//do not know the journal_id
		echo "\nDon't know the journal_id to copy the files to the good location\n";
		print_r($dataMapping);
		return 1;
		
	}
	
	$journalId = $values[0];
	
	//////////////////////////////////
	
	foreach ($files as $file) {
		
		if ($file === "." || $file === "..") {
			//DO NOTHING
			
		}
		else if (is_dir("$filesDirOld/$file")) {
			processFiles("$filesDirOld/$file", $filesDirNew, $dataMapping, $copied, $errors); //recursive call
		}
		else if (array_key_exists($file, $dataMapping["file_name"])){
			
			$fileNewName = $dataMapping["file_name"][$file];
			$words = explode("-", $fileNewName);
			if (sizeof($words) === 4) {
				$type = substr($words[3], 0, 2);
				$articleId = $words[0];
				$folder = $filesTranslation[$type];
				$dirOk = false;
				$dir = "$filesDirNew/journals/$journalId/articles/$articleId/$folder";
				
				if (!file_exists($dir)) {
					if (mkdir($dir, 0777, true)) {
						$dirOk = true;
					}
					else {
						$fileCopyErrors[$file] = "Couldn't create the directory $dir";
					}
				}
				else {
					$dirOk = true;
				}
				
				if ($dirOk) {
					echo "\nCopying $file to $fileNewName .......... ";
					if (copy("$filesDirOld/$file", "$dir/$fileNewName")) {
						$copiedFiles[$file] = "$dir/$fileNewName";
						echo "OK\n";
						$copied++;
					}
					else {
						$fileCopyErrors[$file] = "Couldn't copy the file to $dir/$fileNewName";
						echo "Failed\n";
					}
				}
			}// end of if sizeof(words) === 4
			else {
				//TRATAR MELHOR
				echo "\nFilename $file is not standard.\n"; 
			}
			
		}//end of the if file_name in dataMappings
		else {
			//TRATAR MELHOR 
			//echo "\n$file is not in dataMappings.\n";
		}
	}//end of the foreach
	//$errors["copyFiles"] = $fileCopyErrors;
	
	if (!empty($fileCopyErrors)) {
		array_push($errors, $fileCopyErrors);
	}
	
	return 0;
}


// #05)
/**
get the unpublished articles from the old journal and save in a file *_unpubArticles.xml

return values:
 -2 if the user decided to stop the program
 -1 if did not fetch the unpublished articles
  0 if did not save the xml with the unpublished articles
  the 'xml filename' if saved the unpublished articles in the xml file successfully

*/
function getUnpubArt($conn = null, $journal = null, $collations) {
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to export data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	
	$verbose = false;
	$getKeywords = false;
	
	$resp = readline("Do you want the system to emit messages of each step? (y/N) : ");
	if ($resp === "y" || $resp === "Y") {
		$verbose = true;
	}
	
	$resp = readline("Do you want to export the keywords? (y/N) : ");
	if ($resp === "y" || $resp === "Y") {
		$getKeywords = true;
	}
	
	$args = array();
	$args["collations"] = $collations;
	$args["getKeywords"] = $getKeywords;
	$args["verbose"] = $verbose;
	
	$returnedData = fetchUnpublishedArticles($conn, $journal, $args); //from db2xml.php
	
	$numErrors = countErrors($returnedData["errors"]); // from helperFunctions.php function #21
	
	if ($numErrors > 0) {
		echo "\nThere were errors while fetching the unpublished articles:\n";
		print_r($returnedData["errors"]);
		
		$resp = readline("\n\nContinue the execution even with the errors? (y/N): ");
		
		if (strtolower($resp) !== "y" && strtolower($resp) !== "yes") {
			return -2;
		}
		
	}
	
	$unpubArticles = $returnedData["unpublished_articles"];
	
	if (!is_array($unpubArticles)) {
		echo "\nCould not fetch the unpublished articles.\n";
		return -1;
	}
	
	$xml = new DOMDocument("1.0", "UTF-8");
	
	/*if (!$xml->load("templates/unpublished_articles_template.xml")) {
		echo "\nNOTE: Could not load the unpublished_articles template\n";
		exit();
	}*/
	
	$unpublished_articles_node = $xml->getElementsByTagName("unpublished_articles")->item(0);
	
	$dumpArgs = array();
	$dumpArgs["addRootNode"] = true;
	$dumpArgs["rootNode"] = "unpublished_articles";
	$dumpArgs["journal"] = $journal;
	$dumpArgs["type"] = "article";
	arrayToXml($xml, $xml, $unpubArticles, $dumpArgs); //from helperFunctions.php function #13
	//arrayToXml($xml, $unpublished_articles_node, $unpubArticles, $dumpArgs); //from helperFunctions.php function #13
	
	$filename = $journal["path"] . "_unpubArticles.xml";
	
	if (saveMyXml($xml, $filename, false)) { //from helperFunctions.php function #04
		return $filename;
	}
	
	return 0;
	
}


// #06 
/**
read a *_unpubArticles.xml file and put in the database the data that are not already there

return value:
	 1 -> Everything went ok and imported at least one unpublished article
	 0 -> Everything went ok but did not import any unpublished article
	-1 -> Occurred some problem(s) while inserting the unpublished articles
	-2 -> Could not load the .xml file with the unpublished articles
	-3 -> Imported everything but did not save the data mapping
	-4 -> The user decided to stop the importation

*/
function setUnpubArt($xmlFiles, $conn = null, $journal = null) {
	
	$unpubArtFilename = null;
	$mappingsFilename = null;
	
	if (is_array($xmlFiles)) {
		if (array_key_exists("unpublished_articles", $xmlFiles)) {
			$unpubArtFilename = $xmlFiles["unpublished_articles"];
		}
		if (array_key_exists("data_mappings", $xmlFiles)) {
			$mappingsFilename = $xmlFiles["data_mappings"];
		}
	}
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to import data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	$mappingsXml = new DOMDocument("1.0", "UTF-8");
	$unpubArtXml = new DOMDocument("1.0", "UTF-8");
	
	//if (!$unpubArtXml->load($unpubArtFilename)) {
	if (@$unpubArtXml->loadHTMLFile($unpubArtFilename)) {
		//use the loadHTMLFile to be able to decode the htmlentities
		//the @ in before the variable name is to suppress the warnings
		//everything ok
	}
	else {
		echo "\nCould not load the xml file '$unpubArtFilename' for the sections.\n";
		return false;
	}
	
	$dataMapping = getDataMapping($journal["path"], $mappingsFilename); //from this script function #03
	
	if (!is_array($dataMapping)) {
		$dataMapping = array();
	}
	
	$returnedData = insertUnpublishedArticles($unpubArtXml, $conn, $dataMapping, $journal); //from xml2db.php function #03
	
	echo "\nThe returnedData:\n";
	print_r($returnedData);
	
	echo "\nThe dataMapping:\n";
	print_r($dataMapping);
	exit();
	
	if (!is_array($returnedData)) { 
		//ocurred some problem in insertUnpublishedArticles
		return -1; //TRATAR MELHOR
	}
	
	$insertedUsers = $returnedData["insertedUsers"];
	
	if (count($insertedUsers) > 0) {
		$newUsersXml = new DOMDocument("1.0", "UTF-8");
		
		$extraArgs = array();
		$extraArgs["addRootNode"] = true;
		$extraArgs["rootNode"] = "new_users";
		$extraArgs["journal"] = $journal;
		$extraArgs["type"] = "user";
		
		arrayToXml($newUsersXml, $newUsersXml, $insertedUsers, $extraArgs); // from helperFunctions function #13
		
		$usersFilename = $journal["path"] . "_newUsers.xml";
		
		
		echo "\nSaving the newly imported users in a .xml file:\n";
		saveMyXml($newUsersXml, $usersFilename, false); // from helperFunctions function #04 
	}
	
	$numErrors = countErrors($returnedData["errors"]); //from helperFunction function #21
	
	if ($numErrors > 0) {
		echo "\nErrors:\n";
		print_r($returnedData["errors"]);
		
		echo "\nNumber of errors: $numErrors\n";
		
		$resp = readline("The changes are not yet committed. Continue with the importation? (y/N): ");
		
		if (strtolower($resp) !== "y" &&  strtolower($resp) !== "yes") {
			return -4;
			//exit("\n\n----------- Haulting the application ----------\n\n");
		}
	}
	else {
		echo "\nThere were no errors while inserting the unpublished articles\n";
	}
	
	if ($returnedData["numInsertedArticles"] > 0 || count($insertedUsers) > 0) {
		
		//only save the dataMappings if at least one article or new user was inserted
		
		echo "\nImported " . $returnedData["numInsertedArticles"] . " new articles.";
		echo "\nImported " . count($insertedUsers) . " new users.\n";
	
		$unpublished_articles_node = $unpubArtXml->getElementsByTagName("unpublished_articles")->item(0);
		
		$oldPath = $unpublished_articles_node->getAttribute("journal_original_path");
		$newPath = $journal["path"];
		
		$oldId = $unpublished_articles_node->getAttribute("journal_original_id");
		$newId = $journal["journal_id"];
		
		$journalMapping = array(
			"path" => array("old" => $oldPath, "new" => $newPath), 
			"id" => array("old" => $oldId, "new" => $newId)
		);
		
		if (saveDataMapping($dataMapping, $journal["path"], $journalMapping)) { //from this script function #02
			return 1;
		}
		else {
			return -3;
		}
	}
	else {
		echo "\nDid not import any new article or user.\n";
	}
	
	return 0;
	
}


// #07
/**
get the sections from the old journal and save it in a file *_sections.xml

return values:
 -1 if did not fetch the sections
  0 if did not save the sections in the xml
  the filename if saved the sections in the xml successfully

*/
function getSections( $conn = null, $journal = null, $collations) {
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to export data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	$xml = new DOMDocument("1.0", "UTF-8");
	
	/*if (!$xml->load("templates/sections_template.xml")) {
		echo "\nNOTE: Could not load the sections template\n";
		exit();
	}*/
	
	$verbose = false;
	$resp = readline("Do you want the system to emit messages of each step? (y/N) : ");
	if ($resp === "y" || $resp === "Y") {
		$verbose = true;
	}
	
	$args["collations"] = $collations;
	$args["verbose"] = $verbose;
	
	$returnedData = fetchSections($conn, $journal, $args); //from db2xml.php
	
	$numErrors = countErrors($returnedData["errors"]); // from helperFunctions.php function #21
	
	if ($numErrors > 0) {
		echo "\nThere were errors while fetching the sections:\n";
		print_r($returnedData["errors"]);
		
		$resp = readline("\n\nContinue the execution even with the errors? (y/N): ");
		
		if (strtolower($resp) !== "y" && strtolower($resp) !== "yes") {
			return -2;
		}
		
	}
	
	$sections = $returnedData["sections"];
	
	if (!is_array($sections)) {
		echo "\nCould not fetch the sections.\n";
		return -1;
	}
	
	//$sections_node = $xml->getElementsByTagName("sections")->item(0);
	
	$dumpArgs = array();
	$dumpArgs["addRootNode"] = true;
	$dumpArgs["rootNode"] = "sections";
	$dumpArgs["journal"] = $journal;
	$dumpArgs["type"] = "section";
	arrayToXml($xml, $xml, $sections, $dumpArgs); //from helperFunctions.php #13
	//arrayToXml($xml, $sections_node, $sections, $dumpArgs); //from helperFunctions.php #13
	
	$sections_node = $xml->getElementsByTagName("sections")->item(0);
	$sections_node->setAttribute("journal_original_id", $journal["journal_id"]);
	$sections_node->setAttribute("journal_original_path", $journal["path"]);
	
	$filename = $journal["path"] . "_sections.xml";
	
	if (saveMyXml($xml, $filename, false)) { //from helperFunctions.php #04
		return $filename;
	}
	
	return 0;
}


// #07.5
/**
maps the journal sections and put in the dataMappings.xml
Returns the section mapping as an array

this function is necessary because the sections data mapping is a bit different 

*/
function mapJournalSections($sectionsFilename, $conn = null, $journal = null) {
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to map the sections -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	$xml = new DOMDocument("1.0", "UTF-8");
	
	if (@$xml->loadHTMLFile($sectionsFilename)) {
		//use the loadHTMLFile to be able to decode the htmlentities
		//the @ in before the variable name is to suppress the warnings
		//everything ok
	}
	else {
		echo "\nCould not load the file '$sectionsFilename'\n";
		return 2;
	}
	
	echo "\nMapping journal sections...\n";
	
	$sections = $xml->getElementsByTagName("sections")->item(0);
	
	$journalOldPath = $sections->getAttribute("journal_original_path");
	$journalOldId = $sections->getAttribute("journal_original_id");
	
	$settings = $sections->getElementsByTagName("setting");
	
	$sectionMapping = getDataMapping($journalOldPath);
	
	if (!is_array($sectionMapping)) {
		$sectionMapping = array();
	}
	
	if (!array_key_exists("journal_id", $sectionMapping)) $sectionMapping["journal_id"] = array($journalOldId => $journal["journal_id"]);
	
	if (!array_key_exists("section_id", $sectionMapping)) $sectionMapping["section_id"] = array();
	
	/*if (!array_key_exists("review_form_id", $sectionMapping)) $sectionMapping["review_form_id"] = array();*/
	
	foreach ($settings as $setting) {
		
		$name = $setting->getElementsByTagName("setting_name")->item(0)->nodeValue;
		
		if ($name === "abbrev") {
			
			$abbrev = $setting->getElementsByTagName("setting_value")->item(0)->nodeValue;
			$sectionOldId = $setting->getElementsByTagName("section_id")->item(0)->nodeValue;
			
			if (!array_key_exists($sectionOldId, $sectionMapping["section_id"])) {
				$section = getSectionByAbbrev($conn, $journal["journal_id"], $abbrev); // from xml2db.php function #02.5
				if (is_array($section)) {
					$sectionMapping["section_id"][$sectionOldId] = $section["section_id"];
				}
				
			}// closing the if section_id not in data mappings
		}//closing the if name === abbrev	
	}//end of the foreach setting
	
	
	return $sectionMapping;
	
}


// #08
/**
read a *_sections.xml file and put in the database the ones that are not already there

Function to insert the sections in the database
return values:
	 1 -> everything went ok and imported at least one section
	 0 -> everything went ok but did not import any section
	-1 -> Occurred some problem(s) while inserting the sections
	-2 -> Could not load the .xml file with the sections
	-3 -> Imported everything but did not save the data mapping
	-4 -> The user decided to stop the importation
*/
  
function setSections($xmlFiles, $conn = null, $journal = null) {
	
	//echo "\nSETTING THE SECTIONS\n";
	
	$sectionsFilename = null;
	$mappingsFilename = null;
	
	if (is_array($xmlFiles)) {
		if (array_key_exists("sections", $xmlFiles)) {
			$sectionsFilename = $xmlFiles["sections"];
		}
		if (array_key_exists("data_mappings", $xmlFiles)) {
			$mappingsFilename = $xmlFiles["data_mappings"];
		}
	}
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to import data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	
	$mappingsXml = new DOMDocument("1.0", "UTF-8");
	$sectionsXml = new DOMDocument("1.0", "UTF-8");
	
	if (@$sectionsXml->loadHTMLFile($sectionsFilename)) {
		//use the loadHTMLFile to be able to decode the htmlentities
		//the @ in before the variable name is to suppress the warnings
		//everything ok
	}
	else {
		echo "\nCould not load the file '$sectionsFilename'\n";
		return -2;
	}
	
	$sections_node = $sectionsXml->getElementsByTagName("sections")->item(0);
	
	$dataMapping = getDataMapping($journal["path"], $mappingsFilename); //from this script function #03
	
	if (!is_array($dataMapping)) {
		$dataMapping = mapJournalSections($sectionsFilename, $conn, $journal);
	}
	
	$returnedData = insertSections($sections_node, $conn, $dataMapping, $journal["journal_id"]); //from xml2db.php function #02
	
	if (!is_array($returnedData)) { 
		//ocurred some problem in insertSection
		return -1;
	}
	
	$numErrors = countErrors($returnedData["errors"]); //from helpFunction function #21
	
	if ($numErrors > 0) {
		echo "\nErrors:\n";
		print_r($returnedData["errors"]);
		
		echo "\nNumber of errors: $numErrors\n";
		
		$resp = readline("The changes are not yet committed. Continue with the importation? (y/N): ");
		
		if (strtolower($resp) !== "y" &&  strtolower($resp) !== "yes") {
			return -4;
			//exit("\n\n----------- Haulting the application ----------\n\n");
		}
	}
	else {
		echo "\nThere were no errors while inserting the sections\n";
	}
	
	if ($returnedData["numInsertedSections"] > 0 || $returnedData["numInsertedReviewForms"] > 0) {
	
		//only save the data mapping if at least one section or review_form was actually imported
		
		echo "\nImported " . $returnedData["numInsertedSections"] . " new sections.";
		echo "\nImported " . $returnedData["numInsertedReviewForms"] . " new review_forms.\n";
		
		$oldPath = $sections_node->getAttribute("journal_original_path");
		$newPath = $journal["path"];
		
		$oldId = $sections_node->getAttribute("journal_original_id");
		$newId = $journal["journal_id"];
		
		$journalMapping = array(
			"path" => ["old" => $oldPath, "new" => $newPath], 
			"id" => ["old" => $oldId, "new" => $newId]
		);
		
		if (saveDataMapping($dataMapping, $journal["path"], $journalMapping)) { //from this script function #02
			return 1;
		}
		else {
			return -3;
		}
	}
	else {
		echo "\nDid not import any section or review_form.\n";
	}
	
	return 0;
	
}


// #09)

function getAnnouncements( $conn = null, $journal = null, $collations) {
	if ($conn === null) {
		echo "\n-------- Connection with the database to export data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	$xml = new DOMDocument("1.0", "UTF-8");
	
	$verbose = false;
	$resp = readline("Do you want the system to emit messages of each step? (y/N) : ");
	if ($resp === "y" || $resp === "Y") {
		$verbose = true;
	}
	
	$args["collations"] = $collations;
	$args["verbose"] = $verbose;
	
	$returnedData = fetchAnnouncements($conn, $journal, $args); //from db2xml.php
	
	$numErrors = countErrors($returnedData["errors"]); // from helperFunctions.php function #21
	
	if ($numErrors > 0) {
		echo "\nThere were errors while fetching the announcements:\n";
		print_r($returnedData["errors"]);
		
		$resp = readline("\n\nContinue the execution even with the errors? (y/N): ");
		
		if (strtolower($resp) !== "y" && strtolower($resp) !== "yes") {
			return -2;
		}
		
	}
	
	$announcements = $returnedData["announcements"];
	 
	if (!is_array($announcements)) {
		echo "\nCould not fetch the announcements.\n";
		return -1;
	}
	
	//$announcements_node = $xml->getElementsByTagName("announcements")->item(0);
	
	$dumpArgs = array();
	$dumpArgs["addRootNode"] = true;
	$dumpArgs["rootNode"] = "announcements";
	$dumpArgs["journal"] = $journal;
	$dumpArgs["type"] = "announcement";
	arrayToXml($xml, $xml, $announcements, $dumpArgs); //from helperFunctions.php #13
	//arrayToXml($xml, $announcements_node, $announcements, $dumpArgs); //from helperFunctions.php #13
	
	$announcements_node = $xml->getElementsByTagName("announcements")->item(0);
	$announcements_node->setAttribute("journal_original_id", $journal["journal_id"]);
	$announcements_node->setAttribute("journal_original_path", $journal["path"]);
	
	$filename = $journal["path"] . "_announcements.xml";
	
	if (saveMyXml($xml, $filename, false)) { //from helperFunctions.php #04
		return $filename;
	}
	
	return 0;
}


// #10) 
/**
Function to insert the announcements in the database
return values:
	 1 -> everything went ok and imported at least one announcement
	 0 -> everything went ok but did not import any announcement
	-1 -> Occurred some problem(s) while inserting the announcements
	-2 -> Could not load the .xml file with the announcements
	-3 -> Imported everything but did not save the data mapping
	-4 -> The user decided to stop the importation
*/

function setAnnouncements($xmlFiles, $conn = null, $journal = null) {
	$announcementsFilename = null;
	$mappingsFilename = null;
	
	if (is_array($xmlFiles)) {
		if (array_key_exists("announcements", $xmlFiles)) {
			$announcementsFilename = $xmlFiles["announcements"];
		}
		if (array_key_exists("data_mappings", $xmlFiles)) {
			$mappingsFilename = $xmlFiles["data_mappings"];
		}
	}
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to import data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	
	$mappingsXml = new DOMDocument("1.0", "UTF-8");
	$announcementsXml = new DOMDocument("1.0", "UTF-8");
	
	if (@$announcementsXml->loadHTMLFile($announcementsFilename)) {
		//use the loadHTMLFile to be able to decode the htmlentities
		//the @ before the variable name is to suppress the warnings
		//everything ok
	}
	else {
		echo "\nCould not load the file '$announcementsFilename'\n";
		return -2;
	}
	
	$announcements_node = $announcementsXml->getElementsByTagName("announcements")->item(0);
	
	$dataMapping = getDataMapping($journal["path"], $mappingsFilename); //from this script function #03
	
	$returnedData = insertAnnouncements($announcements_node, $conn, $dataMapping, $journal["journal_id"]); //from xml2db.php function #02
	
	if (!is_array($returnedData)) { 
		//ocurred some problem in insertAnnouncement
		return -1;
	}
	
	$numErrors = countErrors($returnedData["errors"]); //from helpFunction function #21
	
	if ($numErrors > 0) {
		echo "\nErrors:\n";
		print_r($returnedData["errors"]);
		
		echo "\nNumber of errors: $numErrors\n";
		
		$resp = readline("The changes are not yet committed. Continue with the importation? (y/N): ");
		
		if (strtolower($resp) !== "y" &&  strtolower($resp) !== "yes") {
			//exit("\n\n----------- Haulting the application ----------\n\n");
			return -4;
		}
	}
	else {
		echo "\nThere were no errors while inserting the announcements\n";
	}
	
	if ($returnedData["numInsertedAnnouncements"] > 0) {
	
		//only save the data mapping if at least one announcement or review_form was actually imported
		
		echo "\nImported " . $returnedData["numInsertedAnnouncements"] . " new announcements.";
		
		$oldPath = $announcements_node->getAttribute("journal_original_path");
		$newPath = $journal["path"];
		
		$oldId = $announcements_node->getAttribute("journal_original_id");
		$newId = $journal["journal_id"];
		
		$journalMapping = array(
			"path" => ["old" => $oldPath, "new" => $newPath], 
			"id" => ["old" => $oldId, "new" => $newId]
		);
		
		if (saveDataMapping($dataMapping, $journal["path"], $journalMapping)) { //from this script function #02
			return 1;
		}
		else {
			return -3;
		}
	}
	else {
		echo "\nDid not import any announcement.\n";
	}
	
	return 0;
}


// #11)

function getEmailTemplates( $conn = null, $journal = null, $collations) {
	if ($conn === null) {
		echo "\n-------- Connection with the database to export data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	$xml = new DOMDocument("1.0", "UTF-8");
	
	$verbose = false;
	$resp = readline("Do you want the system to emit messages of each step? (y/N) : ");
	if ($resp === "y" || $resp === "Y") {
		$verbose = true;
	}
	
	$args["collations"] = $collations;
	$args["verbose"] = $verbose;
	
	$emailTemplates = fetchEmailTemplates($conn, $journal, $args); //from db2xml.php
	
	if (!is_array($emailTemplates)) {
		echo "\nCould not fetch the emailTemplates.\n";
		return -1;
	}
	
	//$emailTemplates_node = $xml->getElementsByTagName("emailTemplates")->item(0);
	
	$dumpArgs = array();
	$dumpArgs["addRootNode"] = true;
	$dumpArgs["rootNode"] = "email_templates";
	$dumpArgs["journal"] = $journal;
	$dumpArgs["type"] = "email_template";
	arrayToXml($xml, $xml, $emailTemplates, $dumpArgs); //from helperFunctions.php #13
	//arrayToXml($xml, $emailTemplates_node, $emailTemplates, $dumpArgs); //from helperFunctions.php #13
	
	$email_templates_node = $xml->getElementsByTagName("email_templates")->item(0);
	$email_templates_node->setAttribute("journal_original_id", $journal["journal_id"]);
	$email_templates_node->setAttribute("journal_original_path", $journal["path"]);
	
	$filename = $journal["path"] . "_emailTemplates.xml";
	
	if (saveMyXml($xml, $filename, false)) { //from helperFunctions.php #04
		return $filename;
	}
	
	return 0;
}


// #12)

function setEmailTemplates() {
	
}


// #13)

function getGroups($conn = null, $journal = null, $collations) {
	if ($conn === null) {
		echo "\n-------- Connection with the database to export data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	$xml = new DOMDocument("1.0", "UTF-8");
	
	$verbose = false;
	$resp = readline("Do you want the system to emit messages of each step? (y/N) : ");
	if (strtolower($resp) === "y" || strtolower($resp) === "Y") {
		$verbose = true;
	}
	
	$args["collations"] = $collations;
	$args["verbose"] = $verbose;
	
	$returnedData = fetchGroups($conn, $journal, $args); //from db2xml.php
	
	$numErrors = countErrors($returnedData["errors"]); // from helperFunctions.php function #21
	
	if ($numErrors > 0) {
		echo "\nThere were errors while fetching the groups:\n";
		print_r($returnedData["errors"]);
		
		$resp = readline("\n\nContinue the execution even with the errors? (y/N): ");
		
		if (strtolower($resp) !== "y" && strtolower($resp) !== "yes") {
			return -2;
		}
		
	}
	
	$groups = $returnedData["groups"];
	 
	if (!is_array($groups)) {
		echo "\nCould not fetch the groups.\n";
		return -1;
	}
	
	//$groups_node = $xml->getElementsByTagName("groups")->item(0);
	
	$dumpArgs = array();
	$dumpArgs["addRootNode"] = true;
	$dumpArgs["rootNode"] = "groups";
	$dumpArgs["journal"] = $journal;
	$dumpArgs["type"] = "group";
	arrayToXml($xml, $xml, $groups, $dumpArgs); //from helperFunctions.php #13
	//arrayToXml($xml, $groups_node, $groups, $dumpArgs); //from helperFunctions.php #13
	
	$groups_node = $xml->getElementsByTagName("groups")->item(0);
	$groups_node->setAttribute("journal_original_id", $journal["journal_id"]);
	$groups_node->setAttribute("journal_original_path", $journal["path"]);
	
	$filename = $journal["path"] . "_groups.xml";
	
	if (saveMyXml($xml, $filename, false)) { //from helperFunctions.php #04
		return $filename;
	}
	
	return 0;
}

// #14)

function setGroups($xmlFiles, $conn = null, $journal = null) {
	$groupsFilename = null;
	$mappingsFilename = null;
	
	if (is_array($xmlFiles)) {
		if (array_key_exists("groups", $xmlFiles)) {
			$groupsFilename = $xmlFiles["groups"];
		}
		if (array_key_exists("data_mappings", $xmlFiles)) {
			$mappingsFilename = $xmlFiles["data_mappings"];
		}
	}
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to import data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	
	$mappingsXml = new DOMDocument("1.0", "UTF-8");
	$groupsXml = new DOMDocument("1.0", "UTF-8");
	
	if (@$groupsXml->loadHTMLFile($groupsFilename)) {
		//use the loadHTMLFile to be able to decode the htmlentities
		//the @ before the variable name is to suppress the warnings
		//everything ok
	}
	else {
		echo "\nCould not load the file '$groupsFilename'\n";
		return -2;
	}
	
	$groups_node = $groupsXml->getElementsByTagName("groups")->item(0);
	
	$dataMapping = getDataMapping($journal["path"], $mappingsFilename); //from this script function #03
	
	$returnedData = insertGroups($groups_node, $conn, $dataMapping, $journal["journal_id"]); //from xml2db.php function #02
	
	if (!is_array($returnedData)) { 
		//ocurred some problem in insertGroup
		return -1;
	}
	
	$numErrors = countErrors($returnedData["errors"]); //from helpFunction function #21
	
	if ($numErrors > 0) {
		echo "\nErrors:\n";
		print_r($returnedData["errors"]);
		
		echo "\nNumber of errors: $numErrors\n";
		
		$resp = readline("The changes are not yet committed. Continue with the importation? (y/N): ");
		
		if (strtolower($resp) !== "y" &&  strtolower($resp) !== "yes") {
			//exit("\n\n----------- Haulting the application ----------\n\n");
			return -4;
		}
	}
	else {
		echo "\nThere were no errors while inserting the groups\n";
	}
	
	if ($returnedData["numInsertedGroups"] > 0) {
	
		//only save the data mapping if at least one group or review_form was actually imported
		
		echo "\nImported " . $returnedData["numInsertedGroups"] . " new groups.";
		
		$oldPath = $groups_node->getAttribute("journal_original_path");
		$newPath = $journal["path"];
		
		$oldId = $groups_node->getAttribute("journal_original_id");
		$newId = $journal["journal_id"];
		
		$journalMapping = array(
			"path" => ["old" => $oldPath, "new" => $newPath], 
			"id" => ["old" => $oldId, "new" => $newId]
		);
		
		if (saveDataMapping($dataMapping, $journal["path"], $journalMapping)) { //from this script function #02
			return 1;
		}
		else {
			return -3;
		}
	}
	else {
		echo "\nDid not import any group.\n";
	}
	
	return 0;
}

// #15)
/**
function to treat the error genereating in the EXPORTATION process
*/

function treatExportErrors($result, $type = null) {
	
	$stop = false;
	
	switch($result) {
		case -2:{
			echo "\nUser decided to stop the program.\n";
			$stop = true;
		} break;
		
		case -1:
			echo "The application could not fetch the data to export.\n";
			break;
			
		case 0:
			echo "The application could not save the .xml file with the data to export.\n";
			break;
			
		case null:
			echo "The exportation function returned null\n";
			break;
			
		default:
			echo "Unknown return value for the exportation function: '$result'\n";
			$stop = true;
	}
	
	if (!$stop) {
		$resp = readline("\n\nContinue the execution even with the errors? (y/N): ");
		$responseYes = strtolower($resp) === "y" && strtolower($resp) === "yes";
	}
	
	if (!$responseYes || $stop) {
		exit("\n\n-----------  Application halt  ----------------\n\n");
	}
}

// #16)
/**
function to treat the error genereating in the IMPORTATION process
*/

function treatImportErrors($result, &$conn, $type = null) {
	
	$stop = false;
	$responseYes = false;
	
	if ($result < 0) {
		//occurred some problem while setting the data
		
		switch ($result) {
			case -1:
				echo "\nOccurred some problem(s) during the importation.\n";
				break;
				
			case -2: {
				echo "\nDid not load the .xml file.\n";
				$stop = true;
			} break;
				
			case -3:
				echo "\nImported everything but did not save the data mappings (CAN'T RETRIEVE THAT DATA AFTERWARDS).\n";
				break;
				
			case -4: {
				echo "\nUser decided to stop the program.\n";
				$stop = true;
			} break;
			
			case null:
				echo "\nThe importation result was null.\n";
				break;
				
			default:
				echo "\nUnknown return value for the importation result: '$result'\n";
				$stop = true;
		}
		
		if (!$stop) {
			$resp = readline("\n\nContinue the importation even with the errors? (y/N): ");
			$responseYes = strtolower($resp) === "y" || strtolower($resp) === "yes";
		}
		
		if (!$responseYes || $stop) {
			echo "\n\nRolling back the transaction ....... ";
			$conn->rollBack();
			echo "OK\n";
			exit("\n\n-----------  Application halt  ----------------\n\n");
		}
	}
}

// #17)
/**
function that returns an array with the tables collations for the specified db_name
*/
function getCollations(&$conn, $db_name) {
	
	$collations = array();
	
	//get the collations to know which tables need to transform the characters to utf-8
	$stmt = $conn->prepare("SELECT TABLE_SCHEMA, TABLE_NAME, TABLE_COLLATION FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=:db_name");
	
	if ($db_name === null) {
		exit("\n\ndb_name must not be null appFunctions.php function getCollations\n\n");
	}
	
	$stmt->bindParam(":db_name", $db_name, PDO::PARAM_STR);
	
	if ($stmt->execute()) {
		while ($info = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$collations[$info["TABLE_NAME"]] = $info["TABLE_COLLATION"];
		}
	}
	else {
		exit("\n\nCould not retrive the tables collations. (in appFunction.php function myExport)\n\n");
	}
	
	$stmt = null;
	
	return $collations;
}


// #18)
/**
functions that displays the main menu and returns the selected option
*/
function mainMenu($options) {
	
	echo "\n  Main menu:\n\n";
	
	foreach ($options as $key => $value) {
		echo "    $key - $value \n";
	}
	
	echo "\n";
	
	$opt = readline("Enter the desired option: ");
	
	if (array_key_exists($opt, $options)) {
		// confirm the chosen option
		$confirm = strtolower(readline("You chose '" . $options[$opt] . "'. Do you confirm this option? (Y/n) : "));
		if ($confirm === "n" || $confirm === "no") {
			return mainMenu($options); //call again the same menu
		}
	}
	else {
		echo "\nInvalid option!\n\n";
		return mainMenu($options); //call again the same menu
	}
	
	return $opt;
}



// #19)
/**
function to copy the article files from the old ojs to the new installation processing their names
arguments:
	journal -> is used to get the data mapping for the journal
	conn -> is used in order to get the journal, is necessary only if journal is null, 
*/
function migrateFiles($journal = null, $conn = null) {
	
	if ($journal === null) {
		if ($conn === null) {
			echo "\n-------- Connection with the database to migrate the files -----------\n";
			echo "\n";
			$conn = askAndConnect(); //from helperFunctions.php function #03
		}
		
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	echo "\n\nFILES MIGRATION\n\n";
	
	$filesOld = readline("Enter the location of the files_dir for the OLD OJS instalation: ");
	$filesNew = readline("Enter the location of the files_dir for the NEW OJS instalation: ");
	$copiedFiles = 0; // the number of copied files
	$fileErrors = array(); // array to store the errors while copying the files
	
	$dataMapping = getDataMapping($journal["path"]); // from this script function #03
	
	if (is_array($dataMapping)) {
		processFiles($filesOld, $filesNew, $dataMapping, $copiedFiles, $fileErrors); // from this script function #04
	}
	else {
		//error when trying to get the data mapping
		echo "\n\nERROR: Could not copy the files to the new files directory.\n";
		return 1;
	}
	
	
	if (!empty($fileErrors)) {
		echo "\nErrors while copying:\n";
		print_r($fileErrors);
	}
	
	$errorCount = count($fileErrors);
	
	echo "\nNumber of copied files: $copiedFiles\n";
	echo "\nNumber of errors: $errorCount\n";
	
	return $errorCount;
}


// #20)
/**
exports the data and puts the name of the xml file created in the array $arr
$options is an array with the keys sections, unpublished_articles and announcements
set to either true or false, marking which ones to be exported

returns the number of problems encountered during the execution of the function

*/
function myExport($options, &$arr, $conn = null, $journal = null, $args = null) {
	
	$numberOfProblems = 0;
	$db_name = null;
	$collations = null;
	
	if (is_array($args)) {
		if (array_key_exists("db_name", $args)) {
			$db_name = $args["db_name"];
		}
		
		if (array_key_exists("collations", $args)) {
			$collations = $args["collations"];
		}
	}
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to export data -----------\n";
		echo "\n";
		//$conn = askAndConnect(); //from helperFunctions.php function #03
		
		
		$connData = AskConnectData(); // from helperFunctions.php function #02
		$db_name = $connData["db"]; //saving the db_name to use later
		
		$conn = myConnect($connData["host"], $connData["user"], $connData["pass"], $connData["db"]); // from helperFunctions.php function #01
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	if ($collations === null) $collations = getCollations($conn, $db_name);
	
	foreach ($options as $printableType => $export) { 
	if ($export) {
		//////// replace empty spaces (' ') with underlines ('_') /////////////
		$type = str_replace(" ", "_", $printableType);
		
		echo "\nExporting the journal $printableType...\n";
		
		$result = getData($type, $conn, $journal, $collations); // from this script function #07
	
		if (is_string($result)) {
			//echo "Success!!!\n";
			$arr[$type] = $result;
		}
		else {
			$numberOfProblems++; //increment the number of problems indicating that a problem occurred
			
			//treat the error acused by getAnnouncements
			treatExportErrors($result);
		}
	}// end of the if export
	}// end of the foreach options
	
	return $numberOfProblems;
}



// #21)
/**
imports the data from the xml file with the unpublished articles and/or sections
the names of the xml files are passed in the array $xmlFiles
option is an array with the selected items to import as true

returns the number of problems encountered during the execution of the function

uses mysql transaction

*/
function myImport($options, &$xmlFiles, &$conn = null, $journal = null, $args= null) {
	
	$numberOfProblems = 0;
	$db_name = null;
	$tables_info = null;
	$migrate_files = false;
	$saveDataMappingXml = false;
	
	if (is_array($args)) {
		if (array_key_exists("db_name", $args)) {
			$db_name = $args["db_name"];
		}
		
		if (array_key_exists("tables_info", $args)) {
			$tables_info = $args["tables_info"];
		}
	}
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to import data -----------\n\n";
		
		$connData = AskConnectData(); // from helperFunctions.php function #02
		if ($db_name === null) $db_name = $connData["db"]; //saving the db_name to use later
		
		$conn = myConnect($connData["host"], $connData["user"], $connData["pass"], $connData["db"]); // from helperFunctions.php function #01
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	if ($db_name === null) {
		exit("\ndb_name null when in myImport type $type\n");
		$db_name = readline("Enter the name of the database to which the data must be imported: ");
	}
	
	if ($tables_info === null) {
		$tables_info = getTablesInfo($conn, $db_name); // from helperFunctions.php function #26
	}
	
	if (is_array($tables_info)) { 
		foreach ($tables_info as $info) {
			if ($info["ENGINE"] !== "InnoDB") {
				echo "\n\nTable " . $info["TABLE_NAME"] . " is not InnoDB\n\n";
				return 1; //indicates numberOfProblems = 1
			}
		}
	}
	else {
		echo "\nThe tables informations were not passed as an array\n";
		return 1; //indicates numberOfProblems = 1
	}
	
	
	try {
	
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		if (!$conn->inTransaction()) {
			$conn->beginTransaction();
		}
		
		$commit = true;
		
		$journalOldId = null;
		$journalOldPath = null;
		
		//// loop through each option and whether import it or not //////////////
		foreach ($options as $printableType => $import) { 
		if ($import) {
			//////// replace empty spaces (' ') with underlines ('_') /////////////
			$type = str_replace(" ", "_", $printableType);
			
			echo "\nImporting the journal $printableType...\n";
			
			// the xml filename for the announcements must be passed to setSections
			//this piece of code check if the filename is in the xmlFiles array, and sets the name if it's not there already
			if (!array_key_exists($type, $xmlFiles)) {
				$dataFilename = readline("Enter the name of the file where the $printableType are stored: ");
				if (substr($dataFilename, -1, 1) === " ") { // if the last character is an empty space
					$dataFilename = substr($dataFilename, 0, -1); //remove the last character of the string
				}
				
				$xmlFiles[$type] = $dataFilename;
			}
			/////////////  end of the check and set filename  //////////////////////////////////////////////////
			
			$result = setData($type, $xmlFiles, $conn, $journal, $dataMapping);
			// if everything went well result will be 1
			
			if ($result === 1) {
				$saveDataMappingXml = true;
				if ($type === "unpublished_articles") {
					$migrate_files = true;
				}
			}
			
			treatImportErrors($result, $conn);
			
			/////// this data will be used later to map the journal  ///////////
			if ($journalOldId === null || $journalOldPath === null) {
				
				$dataXml = new DOMDocument("1.0", "UTF-8");
				@$dataXml->loadHTMLFile($xmlFiles[$type]);
				
				$data_node = $dataXml->getElementsByTagName($type)->item(0);
				$journalOldPath = $data_node->getAttribute("journal_original_path");
				$journalOldId = $data_node->getAttribute("journal_original_id");
			}
			////////////////////////////////////////////////////////////////////
			
		
		}// end of the if import
		}// end of the foreach options
		
		if ($saveDataMappingXml) {
			$dataMappingSaved = false;
		
			if ($journalOldId !== null && $journalOldPath !== null) {
				
				$journalMapping = array(
					"path" => array("old" => $journalOldPath, "new" => $journal["path"]), 
					"id" => array("old" => $journalOldId, "new" => $journal["journal_id"])
				);
				
				$dataMappingSaved = saveDataMapping($dataMapping, $journal["path"], $journalMapping);  //from this script function #02
			}
			else {
				echo "\n\nCOULD NOT GET THE JOURNAL'S OLD id AND path\n\n";
			}
			
			if (!$dataMappingSaved) {
				treatImportErrors(-3, $conn); // -3 is the code for not saving the data mapping
				// if the user wants the program might stop here and the transaction will be rolled back
			}
			
			if ($commit){
				echo "\nCommitting changes to the database......";
				$conn->commit();
				echo "OK\n";
			}
			
			if ($migrate_files) {
				$numberOfProblems += migrateFiles($journal, $conn); // from this script function #11
			}
			
		}
		else {
			echo "\nNothing was imported\n";
		}
	
	} // end of the try block
	
	catch (PDOException $e) {
		echo "\n\n ########## FAILURE ########### \n";
		echo "\nException reached: " . $e->getMessage();
		
		echo "\n\nRolling back the transaction ........ ";
		
		$conn->rollBack();
		
		echo "Ok\n";
		
		exit("\n\n-----------  Application halt  ----------------\n\n");
	}
	
	return $numberOfProblems;
}
//end of the function myImport


// #22)
function myMigrate($options, &$xmlFiles, $conn = null, $journal = null, $args = null) {
	
	$numProblems = myExport($options, $xmlFiles, $conn, $journal, $args);
	if ($numProblems === 0) {
		// will only import the data if the data exportation ran without problems
		myImport($options, $xmlFiles, $conn, $journal, $args);
	}
	
}



// #23
/**
the function that actually executes the app

like in C it returns 0 if everything went ok
*/
function myMain() {
	echo "\n\n---------------------------------------------------------------\n\n";
	echo "This is an app to help migrate data that the OJS importExport does not.\n";
	
	$actions = array(
		1 => "export",
		2 => "import",
		3 => "migrate",
		4 => "copy files"
	);
	
	$index = mainMenu($actions); // use a the mainMenu function to choose the action
	
	$action = $actions[$index];
	
	$options = array("sections" => false, "unpublished articles" => false, "announcements" => false, "groups" => false);
	
	if ($action !== "copy files") {
	foreach ($options as $type => &$value) {
		$resp = readline("Do you want to $action the $type? (y/N) : ");
		if (strtolower($resp) === "y" || strtolower($resp) === "yes") {
			$value = true;
		}
	}
	}
	
	///////// VARIABLES NEEDED TO PERFORM THE ACTIONS //////////////////
	
	$xmlFiles = array("data_mappings" => "dataMappings.xml"); 
	
	////////////////////////////////////////////////////////////////////
	
	switch($action){
		
		case "export": 
			myExport($options, $xmlFiles);
			break;
			
		case "import": 
			myImport($options, $xmlFiles);
			break;
		
		case "migrate": 
			myMigrate($options, $xmlFiles);
			break;
		
		case "copy files": 
			migrateFiles();
			break;
		
		default: 
			echo "\n\nBye bye!\n\n----------------------------------------------------------------------\n";
	
	}// end of the switch $options
	
	return 0; //classic C return for the main function
	
}// end of the main

///////////////// THESE FUNCTIONS GERENALIZE THE GETTERS AND SETTERS  ///////////////////////////////

/**

return values:
 -3 if the type passed as an argument is not one of the preselected
 -2 if the user decided to stop the program
 -1 if did not fetch the unpublished articles
  0 if did not save the xml with the unpublished articles
  the 'xml filename' if saved the unpublished articles in the xml file successfully

*/
function getData($type, $conn = null, $journal = null, $collations) {
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to export data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	//////// replace underlines ('_') with empty spaces (' ')/////////////
	$printableType = str_replace("_", " ", $type);
	
	//////// replace empty spaces (' ') with underlines ('_') /////////////
	$type = str_replace(" ", "_", $type);
	
	$verbose = false;
	$getKeywords = false;
	$returnedData = null;
	
	$resp = readline("Do you want the system to emit messages of each step? (y/N) : ");
	if ($resp === "y" || $resp === "Y") {
		$verbose = true;
	}
	
	if ($type === "unpublished_articles") {
		$resp = readline("Do you want to export the keywords? (y/N) : ");
		if ($resp === "y" || $resp === "Y") {
			$getKeywords = true;
		}
	}
	
	$args = array();
	$args["collations"] = $collations;
	$args["getKeywords"] = $getKeywords;
	$args["verbose"] = $verbose;
	
	
	
	switch($type) {
		case "sections":
			$returnedData = fetchSections($conn, $journal, $args);
			break;
			
		case "unpublished_articles":
			$returnedData = fetchUnpublishedArticles($conn, $journal, $args);
			break;
			
		case "announcements":
			$returnedData = fetchAnnouncements($conn, $journal, $args);
			break;
			
		case "email_templates":
			$returnedData = fetchEmailTemplates($conn, $journal, $args);
			break;
			
		case "groups":
			$returnedData = fetchGroups($conn, $journal, $args);
			break;
			
		default:
			echo "\nUnknown type '$type'\n";
			return -3;
	}
	
	$numErrors = countErrors($returnedData["errors"]); // from helperFunctions.php function #21
	
	if ($numErrors > 0) {
		echo "\nThere were errors while fetching the $printableType:\n";
		print_r($returnedData["errors"]);
		
		$resp = readline("\n\nContinue the execution even with the errors? (y/N): ");
		
		if (strtolower($resp) !== "y" && strtolower($resp) !== "yes") {
			return -2;
		}
		
	}
	
	$data = $returnedData[$type];
	
	if (!is_array($data)) {
		echo "\nCould not fetch $type.\n";
		return -1;
	}
	
	$xml = new DOMDocument("1.0", "UTF-8");
	
	$data_node = $xml->getElementsByTagName("data")->item(0);
	
	$dumpArgs = array();
	$dumpArgs["addRootNode"] = true;
	$dumpArgs["rootNode"] = $type;
	$dumpArgs["journal"] = $journal;
	$dumpArgs["type"] = $type;
	
	arrayToXml($xml, $xml, $data, $dumpArgs); //from helperFunctions.php function #13
	
	$filename = $journal["path"] . "_$type.xml";
	
	if (saveMyXml($xml, $filename, false)) { //from helperFunctions.php function #04
		return $filename;
	}
	
	return 0;
	
}


/**
read a .xml file and put in the database the data that are not already there

return value:
	 1 -> Everything went ok and imported at least one data record
	 0 -> Everything went ok but did not import any data record
	-1 -> Occurred some problem(s) while inserting the data
	-2 -> Could not load the .xml file with the data
	-3 -> Imported everything but did not save the data mapping NOT USED
	-4 -> The user decided to stop the importation
	-5 -> Unknown type
*/
function setData($type, $xmlFiles, $conn = null, $journal = null, &$dataMapping) {
	
	$dataFilename = null;
	$mappingsFilename = null;
	$returnedData = null;
	$saveDataMappingXml = false;
	
	//////// replace underlines ('_') with empty spaces (' ')/////////////
	$printableType = str_replace("_", " ", $type);
	
	//////// replace empty spaces (' ') with underlines ('_') /////////////
	$type = str_replace(" ", "_", $type);
	
	if (is_array($xmlFiles)) {
		if (array_key_exists($type, $xmlFiles)) {
			$dataFilename = $xmlFiles[$type];
		}
		if (array_key_exists("data_mappings", $xmlFiles)) {
			$mappingsFilename = $xmlFiles["data_mappings"];
		}
	}
	
	if ($conn === null) {
		echo "\n-------- Connection with the database to import data -----------\n";
		echo "\n";
		$conn = askAndConnect(); //from helperFunctions.php function #03
	}
	
	if ($journal === null) {
		$journal = chooseJournal($conn); //from helperFunctions.php function #08
	}
	
	$mappingsXml = new DOMDocument("1.0", "UTF-8");
	$dataXml = new DOMDocument("1.0", "UTF-8");
	
	//if (!$dataXml->load($dataFilename)) {
	if (@$dataXml->loadHTMLFile($dataFilename)) {
		//use the loadHTMLFile to be able to decode the htmlentities
		//the @ in before the variable name is to suppress the warnings
		//everything ok
	}
	else {
		echo "\nCould not load the xml file '$dataFilename' for the $type.\n";
		return false;
	}
	
	if (!is_array($dataMapping)) {
		$dataMapping = getDataMapping($journal["path"], $mappingsFilename); //from this script function #03
		if (!is_array($dataMapping)) {
			$dataMapping = array();
		}
	}
	
	switch($type) {
		//the insert functions are in the script xml2db.php
		
		case "sections":
			$returnedData = insertSections($dataXml, $conn, $dataMapping, $journal["journal_id"]);
			break;
			
		case "unpublished_articles":
			$returnedData = insertUnpublishedArticles($dataXml, $conn, $dataMapping, $journal); // the journal path is used also
			break;
			
		case "announcements":
			$returnedData = insertAnnouncements($dataXml, $conn, $dataMapping, $journal["journal_id"]);
			break;
			
		case "email_templates":
			$returnedData = insertEmailTemplates($dataXml, $conn, $dataMapping, $journal["journal_id"]);
			break;
			
		case "groups":
			$returnedData = insertGroups($dataXml, $conn, $dataMapping, $journal["journal_id"]);
			break;
			
		default:
			echo "\nUnknown type '$type'\n";
			return -5;
	}
	
	
	if (!is_array($returnedData)) { 
		//ocurred some problem the insert function
		return -1; //TRATAR MELHOR
	}
	
	
	$numErrors = countErrors($returnedData["errors"]); //from helperFunction function #21
	
	if ($numErrors > 0) {
		echo "\nErrors:\n";
		print_r($returnedData["errors"]);
		
		echo "\nNumber of errors: $numErrors\n";
		
		$resp = readline("The changes are not yet committed. Continue with the importation? (y/N): ");
		
		if (strtolower($resp) !== "y" &&  strtolower($resp) !== "yes") {
			return -4;
			//exit("\n\n----------- Haulting the application ----------\n\n");
		}
	}
	else {
		echo "\nThere were no errors while inserting the $printableType\n";
	}
	
	if (array_key_exists("insertedUsers", $returnedData)) {
		$insertedUsers = $returnedData["insertedUsers"];
	
		if (count($insertedUsers) > 0) {
			
			echo "\nImported " . count($insertedUsers) . " new users.\n";
			$saveDataMappingXml = true;
			
			$newUsersXml = new DOMDocument("1.0", "UTF-8");
			
			$extraArgs = array();
			$extraArgs["addRootNode"] = true;
			$extraArgs["rootNode"] = "new_users";
			$extraArgs["journal"] = $journal;
			$extraArgs["type"] = "user";
			
			arrayToXml($newUsersXml, $newUsersXml, $insertedUsers, $extraArgs); // from helperFunctions function #13
			
			$usersFilename = $journal["path"] . "_newUsers.xml";
			
			echo "\nSaving the newly imported users in a .xml file:\n";
			saveMyXml($newUsersXml, $usersFilename, false); // from helperFunctions function #04 
		}
		else {
			echo "\nDid not import any new user.\n";
		}
	}
	
	if (is_array($returnedData["numInsertedRecords"])) {
		foreach ($returnedData["numInsertedRecords"] as $item => $number) {
			if ($number > 0) {
				$saveDataMappingXml = true;
				echo "\nImported $number new $item";
			}
		}
	}
	else if ($returnedData["numInsertedRecords"] > 0) {
		
		//only save the dataMappings if at least one article or new user was inserted
		
		echo "\nImported " . $returnedData["numInsertedRecords"] . " new $printableType.";
		$saveDataMappingXml = true;
		
	}
	else {
		echo "\nDid not import any new $PrintableType.\n";
	}
	
	if ($saveDataMappingXml) {
		return 1;
	}
	
	return 0;
	
}