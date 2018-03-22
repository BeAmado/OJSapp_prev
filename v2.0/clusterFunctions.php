<?php

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
			$returnedData = insertSections($dataXml, $conn, $dataMapping, $journal);
			break;
			
		case "unpublished_articles":
			$returnedData = insertUnpublishedArticles($dataXml, $conn, $dataMapping, $journal);
			break;
			
		case "announcements":
			$returnedData = insertAnnouncements($dataXml, $conn, $dataMapping, $journal);
			break;
			
		case "email_templates":
			$returnedData = insertEmailTemplates($dataXml, $conn, $dataMapping, $journal);
			break;
			
		case "groups":
			$returnedData = insertGroups($dataXml, $conn, $dataMapping, $journal);
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
	
	return 0;
	
}