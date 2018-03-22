<?php
/**
This is a library for getting data from .xml files and put in the OJS database

FUNCTIONS IN DEFINED IN THIS SCRIPT:

01) processUsers
02) getSectionById
03) getSetting
04) updateSections
05) insertSections
06) insertUnpublishedArticles
07) insertAnnouncements
08) insertEmailTemplates (better not use yet)

Developed in 2017 by Bernardo Amado

*/

include_once("helperFunctions.php");


// #01)
/**
function description here
*/
function processUser(&$user, $elem, &$dataMapping, &$errors, &$insertedUsers, &$stmts) {
	
	$userOk = false;
	
	$userSTMT = &$stmts["userSTMT"];
	$checkUsernameSTMT = &$stmts["checkUsernameSTMT"];
	$insertUserSTMT = &$stmts["insertUserSTMT"];
	$lastUsersSTMT = &$stmts["lastUsersSTMT"];
	
	$type = $elem["type"];
	$data = $elem["data"];
	
	$error = array();
	
	global $tables; // from the config.php script
	
	$keys = $tables[$type]["primary_keys"];
	if (empty($keys)) {
		$keys = $tables[$type]["foreign_keys"];
	}
	
	foreach ($keys as $key) {
		$error[$key] = $data[$key]; 
	}
	
	$userSTMT->bindParam(":user_email", $user["email"], PDO::PARAM_STR);
	
	if ($userSTMT->execute()) {
		//echo "\nExecuted the userSTMT\n";
		if ($userInfo = $userSTMT->fetch(PDO::FETCH_ASSOC)) {
			$userOk = true;
			$user["user_new_id"] = $userInfo["user_id"];
		}
		else {
			//user is not registered, so need to be inserted in the database
			
			echo "TRYING TO INSERT THE USER \n";
			
			validateData("user", $user); //from helperFunctions.php
			$usernameUsed = true;
			$executed = true;
			$number = 2;
			$user["new_username"] = $user["username"];
			
			//check if need to change the username
			while ($usernameUsed) {
				
				$checkUsernameSTMT->bindParam(":checkUsername", $user["new_username"], PDO::PARAM_STR);
				
				if ($checkUsernameSTMT->execute()) {
					$res = $checkUsernameSTMT->fetch(PDO::FETCH_ASSOC);
					
					if ($res["count"] == 0) {
						$usernameUsed = false;
					}
					else {
						// the username is already in use
						// if the last characters are numbers increment the 2-digit number
						$last2Characters = substr($user["new_username"], -2, 2);
						if (is_numeric($last2Characters)) {
							$number = (int) $last2Characters;
							$number++;
							$str = substr($user["new_username"], 0, -2);
							$user["new_username"] = $str . "$number";
						}
						else {
							// if the last character is a number increment it, otherwise put the number 2 at the final
							$lastCharacter = substr($user["new_username"], -1, 1);
							if (is_numeric($lastCharacter)) {
								$number = (int) $lastCharacter;
								$number++;
								$str = substr($user["new_username"], 0, -1);
								$user["new_username"] = $str . "$number";
							}
							else {
								$user["new_username"] .= "$number";
							}
						}
					}
				}
				else { //Did not execute checkUsernameSTMT
					$error["checkUsernameSTMT"] = array("user" => $user, "error" => $checkUsernameSTMT->errorInfo());
					break;
				}						
			}
			
			
			$arr = array();
			$arr["data"] = $user;
			$arr["params"] = [
				["name" => ":insertUser_username", "attr" => "new_username", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_password", "attr" => "password", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_salutation", "attr" => "salutation", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_firstName", "attr" => "first_name", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_middleName", "attr" => "middle_name", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_lastName", "attr" => "last_name", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_gender", "attr" => "gender", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_initials", "attr" => "initials", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_email", "attr" => "email", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_url", "attr" => "url", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_phone", "attr" => "phone", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_fax", "attr" => "fax", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_mailingAddress", "attr" => "mailing_address", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_country", "attr" => "country", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_locales", "attr" => "locales", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_dateLastEmail", "attr" => "date_last_email", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_dateRegistered", "attr" => "date_registered", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_dateValidated", "attr" => "date_validated", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_dateLastLogin", "attr" => "date_last_login", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_mustChangePassword", "attr" => "must_change_password", "type" => PDO::PARAM_INT],
				["name" => ":insertUser_authId", "attr" => "auth_id", "type" => PDO::PARAM_INT],
				["name" => ":insertUser_disabled", "attr" => "disabled", "type" => PDO::PARAM_INT],
				["name" => ":insertUser_disabledReason", "attr" => "disabled_reason", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_authStr", "attr" => "auth_str", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_suffix", "attr" => "suffix", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_billingAddress", "attr" => "billing_address", "type" => PDO::PARAM_STR],
				["name" => ":insertUser_inlineHelp", "attr" => "inline_help", "type" => PDO::PARAM_STR]
				
			];
			
			echo "\ninserting user " . $user["first_name"] . " " . $user["middle_name"] . " " . $user["last_name"] . " ............ ";
			
			if (myExecute("insert", "user", $arr, $insertUserSTMT, $errors)) { //from helperFunctions.php
				echo "OK\n";
				$args = array();
				$args["compare"] = "almost all";
				$args["notCompare"] = "new_username";
				
				if (getNewId("user", $lastUsersSTMT, $user, $dataMapping, $errors, $args)) { //from helperFunctions.php
					echo "    user new id = " . $user["user_new_id"] . "\n\n";
					$userOk = true;
					// save the user on the insertedUsers array
					array_push($insertedUsers, $user);
				}
			}
			else {
				echo "Failed\n";
			}
		}//end of the else that inserts the user in the database
		
		//put the user new id in the dataMapping
		if (array_key_exists("user_new_id", $user) && !array_key_exists($user["user_id"], $dataMapping["user_id"])) {
			$dataMapping["user_id"][$user["user_id"]] = $user["user_new_id"];
		}
		
	}
	else {
		//Did not execute the userSTMT
		$error["userSTMT"] = ["user" => $user, "error" => $userSTMT->errorInfo()];
	}
	
	unset($userSTMT);
	unset($checkUsernameSTMT);
	unset($insertUserSTMT);
	unset($lastUsersSTMT);
	
	if (array_key_exists("userSTMT", $error) || array_key_exists("checkUsernameSTMT", $error)) {
		array_push($errors, $error);
	}
	
	return $userOk;
}


// #02)
/**
FUNCTION USED ONLY BY setSections or updateSections
you can figure it out
*/
function getSectionById($conn, $sectionId) {
	$getSection = $conn->prepare("SELECT * FROM section WHERE section_id = :sectionId");
	$getSection->bindParam(":sectionId", $sectionId, PDO::PARAM_INT);
	
	$getSettings = $conn->prepare("SELECT * FROM section_settings WHERE section_id = :settings_sectionId");
	$getSettings->bindParam(":settings_sectionId", $sectionId, PDO::PARAM_INT);
	
	if ($getSection->execute()) { // I
	if ($section = $getSection->fetch(PDO::FETCH_ASSOC)) { // II
		if ($getSettings->execute()) { // III
		$settings = array();
		
		while ($setting = $getSettings->fetch(PDO::FETCH_ASSOC)) {
			array_push($settings, $setting);
		}
		
		if (!empty($settings)) {
			$section["settings"] = $settings;
		}
		}// III - closing the if getSettings execute
		
		return $section;
		
	}// II - closing the if getSection fetch
	}// I - closing the if getSection execute
	
	return false;
}


// #02.5)
/**
FUNCTION USED ONLY BY setSections or updateSections
self explains
*/
function getSectionByAbbrev($conn, $journalId, $abbrev, $fetchSettings = false) {
	
	$getSection = $conn->prepare("SELECT * FROM sections WHERE section_id = :sectionId");
	$getSettings = $conn->prepare("SELECT * FROM section_settings WHERE section_id = :settings_sectionId");
	
	
	$abbrevSTMT = $conn->prepare("SELECT * FROM section_settings WHERE setting_name='abbrev' AND setting_value=:abbrev AND section_id IN (
		SELECT section_id FROM sections WHERE journal_id=:journalId)");
	
	$abbrevSTMT->bindParam(":abbrev", $abbrev, PDO::PARAM_STR);
	$abbrevSTMT->bindParam(":journalId", $journalId, PDO::PARAM_STR);
	
	if ($abbrevSTMT->execute()) { // I	
	if ($bruce = $abbrevSTMT->fetch(PDO::FETCH_ASSOC)) { // II 
		// was listening to Bruce Dickinson at the time wrote this 
		
		/*echo "\nthe section fetched in abbrevSTMT: "; print_r($bruce);*/
		   
  		$getSection->bindParam(":sectionId", $bruce["section_id"], PDO::PARAM_INT);
		if ($getSection->execute()) { // III
		if ($section = $getSection->fetch(PDO::FETCH_ASSOC)) { // IV
			
			if ($fetchSettings) {
				$settings = array();
				$getSettings->bindParam(":settings_sectionId", $section["section_id"], PDO::PARAM_INT);
				
				if ($getSettings->execute()) { // V 
				while ($setting = $getSettings->fetch(PDO::FETCH_ASSOC)) {
					array_push($settings, $setting);
				}
				} // V - closing the if getSettings execute
				else {
					echo "\nDid not execute the getSettings\n";
				}
				
				$section["settings"] = $settings;
			}
			
			/*echo "\nsection returned by getSectionAbbrev: "; print_r($section);*/
			
			return $section;
			
		}// IV - closing the if getSection fetch
		/*else {
			echo "\ngetSection did not fetch\n";
		}*/
		}// III - closing the if getSection execute
		/*else {
			echo "\nDid not execute the getSection\n";
			print_r($getSection->errorInfo());
		}*/
		
	}// II - closing the if abbrevSTMT fetch
	/*else {
		echo "\nabbrevSTMT did not fetch\n";
	}*/
	}// I - closing the if abbrevSTMT execute
	/*else {
		echo "\nDid not execute the abbrevSTMT\n";
		print_r($abbrevSTMT->errorInfo());
	}*/
	
	return false;
}


// #03)
/**
function to know wheater or not the element has the specified setting
arguments:
    $element: is an array with the data from e.g a section or an article
    $setting: is an array with the setting data
    
returns setting data from the element or false if the the element does not have it
*/
function getSetting($element, $setting) {
	
	if (array_key_exists("settings", $element)) {
		foreach ($element["settings"] as $elementSetting) {
			if ($setting["setting_name"] === $elementSetting["setting_name"] && $setting["locale"] === $elementSetting["locale"]) {
				return $elementSetting;
			}
		}
	}
	
	return false;
}

// #04)
/**
function to update or insert new data to the section already in the database
*/
function updateSection($conn, &$section, &$dataMapping) {
	
	$sectionId = 0;
	$journalId = 0;
	$reviewFormId = 0;
	$dbSection = null;
	
	if (array_key_exists($section["section_id"], $dataMapping["section_id"])) {
		$sectionId = $dataMapping["section_id"][$section["section_id"]];
	}
	else {
		return 1;
	}
	
	if (!$dbSection = getSectionById($conn, $sectionId)) {
		return 2;
	}
	
	/////////  compare the section data /////////////////////////
	
	if (same2($section, $dbSection) !== 1) { // from helperFunctions.php function #16
		
		//the section data do not match so we have to update them
		
		$updateSectionSTMT = $conn->prepare("UPDATE sections SET journal_id = :updateSection_journalId, review_form_id = :updateSection_reviewFormId, seq = :updateSection_seq, 
		editor_restricted = :updateSection_editorRestricted, meta_indexed = :updateSection_metaIndexed, meta_reviewed = :updateSection_metaReviewed, 
		abstracts_not_required = :updateSection_abstractsNotRequired, hide_title = :updateSection_hideTitle, hide_author = :updateSection_hideAuthor, 
		hide_about = :updateSection_hideAbout, disable_comments = :updateSection_disableComments, abstract_word_count = :updateSection_abstractWordCount
		WHERE section_id = :updateSection_sectionId");
		
		
		if (array_key_exists($section["journal_id"], $dataMapping["journal_id"])) {
			$journalId = $dataMapping["journal_id"][$section["journal_id"]];
		}
		else {
			return 3; //journal_id PROBLEM
		}
		
		
		if ($section["review_form_id"] !== 0 && $section["review_form_id"] !== null) {
			if (array_key_exists($section["review_form_id"], $dataMapping["review_form_id"])) {
				$reviewFormId = $dataMapping["review_form_id"][$section["review_form_id"]];
			}
			else {
				return 4; //review_form_id PROBLEM
			}
		}
		
		$updateSectionSTMT->bindParam(":updateSection_journalId", $journalId, PDO::PARAM_INT);
		$updateSectionSTMT->bindParam(":updateSection_reviewFormId", $reviewFormId, PDO::PARAM_INT);
		$updateSectionSTMT->bindParam(":updateSection_seq", $section["seq"]);
		$updateSectionSTMT->bindParam(":updateSection_editorRestricted", $section["editor_restricted"], PDO::PARAM_INT);
		$updateSectionSTMT->bindParam(":updateSection_metaIndexed", $section["meta_indexed"], PDO::PARAM_INT);
		$updateSectionSTMT->bindParam(":updateSection_metaReviewed", $section["meta_reviewed"], PDO::PARAM_INT);
		$updateSectionSTMT->bindParam(":updateSection_abstractsNotRequired", $section["abstracts_not_required"], PDO::PARAM_INT);
		$updateSectionSTMT->bindParam(":updateSection_hideTitle", $section["hide_title"], PDO::PARAM_INT);
		$updateSectionSTMT->bindParam(":updateSection_hideAuthor", $section["hide_author"], PDO::PARAM_INT);
		$updateSectionSTMT->bindParam(":updateSection_hideAbout", $section["hide_about"], PDO::PARAM_INT);
		$updateSectionSTMT->bindParam(":updateSection_disableComments", $section["disable_comments"], PDO::PARAM_INT);
		$updateSectionSTMT->bindParam(":updateSection_abstractWordCount", $section["abstract_word_count"], PDO::PARAM_INT);
		$updateSectionSTMT->bindParam(":updateSection_sectionId", $sectionId, PDO::PARAM_INT);
		
		if ($updateSectionSTMT->execute()) {
			//TRATAR MELHOR
			echo "\nExecuted updateSectionSTMT\n";
		}
		else {
			//TRATAR MELHOR
			echo "\nDid not execute updateSectionSTMT\n";
		}
		
	}
	
	////////////// end of compare section data ///////////
	
	
	
	$insertSectionSettingSTMT = $conn->prepare("INSERT INTO section_settings (section_id, locale, setting_name, setting_value, setting_type) VALUES (
	:insertSetting_sectionId, :insertSetting_locale, :insertSetting_settingName, :insertSetting_settingValue, :insertSetting_settingType)");
	
	$updateSectionSettingSTMT = $conn->prepare("UPDATE section_settings SET setting_value = :updateSetting_settingValue, setting_type = :updateSetting_settingType
	WHERE section_id = :updateSetting_sectionId AND locale = :updateSetting_locale AND setting_name = :updateSetting_settingName");
	
	if (array_key_exists("settings", $section)) { 
	if (!empty($section["settings"])) {
		
		foreach ($section["settings"] as $setting) {
			
			if ($setting["setting_value"] !== "" && $setting["setting_value"] !== null) {
			
				if ($dbSectionSetting = getSetting($dbSection, $setting)) { //from this script function #04
					if ($setting["setting_value"] !== $dbSectionSetting["setting_value"]) {
						//update the setting
						$updateSectionSettingSTMT->bindParam(":updateSetting_sectionId", $dbSectionSetting["section_id"], PDO::PARAM_INT);
						$updateSectionSettingSTMT->bindParam(":updateSetting_locale", $dbSectionSetting["locale"], PDO::PARAM_STR);
						$updateSectionSettingSTMT->bindParam(":updateSetting_settingName", $dbSectionSetting["setting_name"], PDO::PARAM_STR);
						$updateSectionSettingSTMT->bindParam(":updateSetting_settingValue", $setting["setting_value"], PDO::PARAM_STR);
						$updateSectionSettingSTMT->bindParam(":updateSetting_settingType", $setting["setting_type"], PDO::PARAM_STR);
						
						$updateSectionSettingSTMT->execute();
					}
				}
				else {
					//insert the setting
					$insertSectionSettingSTMT->bindParam(":insertSetting_sectionId", $sectionId, PDO::PARAM_INT);
					$insertSectionSettingSTMT->bindParam(":insertSetting_locale", $setting["locale"], PDO::PARAM_STR);
					$insertSectionSettingSTMT->bindParam(":insertSetting_settingName", $setting["setting_name"], PDO::PARAM_STR);
					$insertSectionSettingSTMT->bindParam(":insertSetting_settingValue", $setting["setting_value"], PDO::PARAM_STR);
					$insertSectionSettingSTMT->bindParam(":insertSetting_settingType", $setting["setting_type"], PDO::PARAM_STR);
					
					$insertSectionSettingSTMT->execute();
				}
			
			}// closing the if setting value not null nor empty string
		}//end fo the foreach section settings
		
	}//closing the if section settings not empty	
	}//closing the if section settings exists
	
	return 0;
	
}


// #05)
function insertSections(&$xml, $conn, &$dataMapping, $journalNewId, $args = null) {
	
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists("limit", $args)) {
			$limit = $args["limit"];
		}
	}
	
	if (!array_key_exists("section_id", $dataMapping)) {
		$dataMapping["section_id"] = array();
	}
	if (!array_key_exists("review_form_id", $dataMapping)) {
		$dataMapping["review_form_id"] = array();
	}
	if (!array_key_exists("review_form_element_id", $dataMapping)) {
		$dataMapping["review_form_element_id"] = array();
	}
	
	///////  THE STATEMENTS  ////////////////////////////////////////////////////////////
	
	$getSectionsSTMT = $conn->prepare("SELECT * FROM sections WHERE journal_id = :getSections_journalId");
	$getSectionSettingsSTMT = $conn->prepare("SELECT * FROM section_settings WHERE section_id = :getSectionSettings_sectionId");
	
	// sections
	$insertSectionSTMT = $conn->prepare("INSERT INTO sections (journal_id, review_form_id, seq, editor_restricted, meta_indexed, meta_reviewed, abstracts_not_required, 
	hide_title, hide_author, hide_about, disable_comments, abstract_word_count) VALUES (:section_journalId, :section_reviewFormId, :section_seq, :section_editorRestricted, 
	:section_metaIndexed, :section_metaReviewed, :section_abstractsNotRequired, :section_hideTitle, :section_hideAuthor, :section_hideAbout, :section_disableComments, 
	:section_abstractWordCount)");
	
	$lastSectionsSTMT = $conn->prepare("SELECT * FROM sections ORDER BY section_id DESC LIMIT $limit");
	
	$insertSectionSettingsSTMT = $conn->prepare("INSERT INTO section_settings (section_id, locale, setting_name, setting_value, setting_type) VALUES (:sectionSettings_sectionId,
	:sectionSettings_locale, :sectionSettings_settingName, :sectionSettings_settingValue, :sectionSettings_settingType)");
	
	/*$updateSectionSTMT = $conn->prepare("UPDATE sections SET journal_id = :updateSection_journalId, review_form_id = :updateSection_reviewFormId, seq = :updateSection_seq, 
	editor_restricted = :updateSection_editorRestricted, meta_indexed = :updateSection_metaIndexed, meta_reviewed = :updateSection_metaReviewed, 
	abstracts_not_required = :updateSection_abstractsNotRequired, hide_title = :updateSection_hideTitle, hide_author = :updateSection_hideAuthor, 
	hide_about = :updateSection_hideAbout, disable_comments = :updateSection_disableComments, abstract_word_count = :updateSection_abstractWordCount");*/
	
	//review_forms
	$insertReviewFormSTMT = $conn->prepare("INSERT INTO review_forms (assoc_id, seq, is_active, assoc_type) 
		VALUES (:revForm_assocId, :revForm_seq, :revForm_isActive, :revForm_assocType)");
	
	$lastReviewFormsSTMT = $conn->prepare("SELECT * FROM review_forms ORDER BY review_form_id DESC LIMIT $limit");
	
	$insertReviewFormSettingsSTMT = $conn->prepare("INSERT INTO review_form_settings (review_form_id, locale, setting_name, setting_value, setting_type) 
	VALUES (:revFormSettings_revFormId, :revFormSettings_locale, :revFormSettings_settingName, :revFormSettings_settingValue, :revFormSettings_settingType)");
	
	//review_form_elements
	$insertReviewFormElementSTMT = $conn->prepare("INSERT INTO review_form_elements (review_form_id, seq, element_type, required, included) VALUES (
	:revFormElement_revFormId, :revFormElement_seq, :revFormElement_elementType, :revFormElement_required, :revFormElement_included)");
	
	$lastReviewFormElementsSTMT = $conn->prepare("SELECT * FROM review_form_elements ORDER BY review_form_element_id DESC LIMIT $limit");
	
	$insertReviewFormElementSettingsSTMT = $conn->prepare("INSERT INTO review_form_element_settings (review_form_element_id, locale, setting_name, setting_value, setting_type) 
	VALUES (:revFormElementSettings_revFormElementId, :revFormElementSettings_locale, :revFormElementSettings_settingName, :revFormElementSettings_settingValue, 
	:revFormElementSettings_settingType)");
	///////////////////////////////////////////////////////////////////////////////////////
	
	$sections_node = null;
	
	if ($xml->nodeName === "sections") {
		$sections_node = $xml;
	}
	else {
		$sections_node = $xml->getElementsByTagName("sections")->item(0);
	}
	
	$errors = [
		"section" => ["insert" => array(), "update" => array()],
		"section_settings" => ["insert" => array(), "update" => array()],
		"review_form" => ["insert" => array(), "update" => array()],
		"review_form_settings" => ["insert" => array(), "update" => array()],
		"review_form_element" => ["insert" => array(), "update" => array()],
		"review_form_element_settings" => ["insert" => array(), "update" => array()]
	];
	
	$sections = xmlToArray($sections_node, true); //from helperFunctions.php
	
	/*echo "\nDataMapping before: \n";
	print_r($dataMapping);*/
	$numInsertedSections = 0;
	$numInsertedRevForms = 0;
	
	foreach ($sections as &$section) {
		
		$section["journal_new_id"] = $journalNewId;
		
		//insert the section if it's not in dataMapping already
		if (!array_key_exists($section["section_id"], $dataMapping["section_id"])) {
		
			////////////////// inserting the review_forms before the insertimg the section /////////////////////////////////////
			if (array_key_exists("review_forms", $section)) { if (!empty($section["review_forms"]) && $section["review_forms"] != null) {
			foreach ($section["review_forms"] as &$reviewForm) {
				
				if (!array_key_exists($reviewForm["review_form_id"], $dataMapping["review_form_id"])) {
				
					$reviewFormOk = false;
					
					validateData("review_form", $reviewForm);
					
					echo "    inserting review form #". $reviewForm["review_form_id"] . " ........."; 
					
					$arr = array();
					$arr["data"] = $reviewForm;
					$arr["params"] = [
						["name" => ":revForm_assocId", "attr" => "assoc_id", "type" => PDO::PARAM_INT],
						["name" => ":revForm_seq", "attr" => "seq"],
						["name" => ":revForm_isActive", "attr" => "is_active", "type" => PDO::PARAM_INT],
						["name" => ":revForm_assocType", "attr" => "assoc_type", "type" => PDO::PARAM_INT]
					];
					
					if (myExecute("insert", "review_form", $arr, $insertReviewFormSTMT, $errors)) { //from helperFunctions.php
						echo "OK\n";
						$numInsertedRevForms++;
						if (getNewId("review_form", $lastReviewFormsSTMT, $reviewForm, $dataMapping, $errors)) { //from helperFunctions.php
							echo "    new id = " . $reviewForm["review_form_new_id"] . "\n\n";
							$reviewFormOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
					
					if ($reviewFormOk) {
						//insert the review_form_settings
						if (array_key_exists("settings", $reviewForm)) { if (!empty($reviewForm["settings"]) && $reviewForm["settings"] != null) {
						foreach ($reviewForm["settings"] as &$setting) {
							
							validateData("review_form_settings", $setting); //from helperFunctions.php
					
							$setting["review_form_new_id"] = $reviewForm["review_form_new_id"];
							echo "    inserting ". $setting["setting_name"] . " with locale " . $setting["locale"] . " ........."; 
							
							$arr = array();
							$arr["data"] = $setting;
							$arr["params"] = [
								["name" => ":revFormSettings_revFormId", "attr" => "review_form_new_id", "type" => PDO::PARAM_INT],
								["name" => ":revFormSettings_locale", "attr" => "locale", "type" => PDO::PARAM_STR],
								["name" => ":revFormSettings_settingName", "attr" => "setting_name", "type" => PDO::PARAM_STR],
								["name" => ":revFormSettings_settingValue", "attr" => "setting_value", "type" => PDO::PARAM_STR],
								["name" => ":revFormSettings_settingType", "attr" => "setting_type", "type" => PDO::PARAM_STR]
							];
							
							if (myExecute("insert", "review_form_settings", $arr, $insertReviewFormSettingsSTMT, $errors)) { //from helperFunctions.php
								echo "OK\n";
							}
							else {
								echo "Failed\n";
							}
						}//end of foreach settings
						unset($setting);
						}// closing the if count review_form settings > 0
						}//closing the if review_form_settings exists
						
						//insert the review_form_elements
						if (array_key_exists("elements", $reviewForm)) { if (!empty($reviewForm["elements"]) && $reviewForm["elements"] != null) {
						foreach ($reviewForm["elements"] as &$revFormElement) {
							
							$revFormElement["review_form_new_id"] = $reviewForm["review_form_new_id"];
							
							$revFormElemIdOk = false;
							
							validateData("review_form_element", $revFormElement);
				
							echo "    inserting review form element #". $revFormElement["review_form_element_id"] . " ........."; 
							
							$arr = array();
							$arr["data"] = $revFormElement;
							$arr["params"] = [
								["name" => ":revFormElement_revFormId", "attr" => "review_form_new_id", "type" => PDO::PARAM_INT],
								["name" => ":revFormElement_seq", "attr" => "seq"],
								["name" => ":revFormElement_elementType", "attr" => "element_type", "type" => PDO::PARAM_INT],
								["name" => ":revFormElement_required", "attr" => "required", "type" => PDO::PARAM_INT],
								["name" => ":revFormElement_included", "attr" => "included", "type" => PDO::PARAM_INT]
							];
							
							if (myExecute("insert", "review_form_element", $arr, $insertReviewFormElementSTMT, $errors)) { //from helperFunctions.php
								echo "OK\n";
								if (getNewId("review_form_element", $lastReviewFormElementsSTMT, $revFormElement, $dataMapping, $errors)) { //from helperFunctions.php
									echo "    new id = " . $revFormElement["review_form_element_new_id"] . "\n\n";
									$revFormElemIdOk = true;
								}
							}
							else {
								echo "Failed\n";
							}
							
							if ($revFormElemIdOk) {
								//insert the review_form_element_settings
								if (array_key_exists("settings", $revFormElement)) { if (!empty($revFormElement["settings"]) && $revFormElement["settings"] != null) {
								foreach ($revFormElement["settings"] as &$setting) {
									validateData("review_form_element_settings", $setting); //from helperFunctions.php
					
									$setting["review_form_element_new_id"] = $revFormElement["review_form_element_new_id"];
									echo "    inserting ". $setting["setting_name"] . " with locale " . $setting["locale"] . " ........."; 
									
									$arr = array();
									$arr["data"] = $setting;
									$arr["params"] = [
										["name" => ":revFormElementSettings_revFormElementId", "attr" => "review_form_element_new_id", "type" => PDO::PARAM_INT],
										["name" => ":revFormElementSettings_locale", "attr" => "locale", "type" => PDO::PARAM_STR],
										["name" => ":revFormElementSettings_settingName", "attr" => "setting_name", "type" => PDO::PARAM_STR],
										["name" => ":revFormElementSettings_settingValue", "attr" => "setting_value", "type" => PDO::PARAM_STR],
										["name" => ":revFormElementSettings_settingType", "attr" => "setting_type", "type" => PDO::PARAM_STR]
									];
									
									if (myExecute("insert", "review_form_element_settings", $arr, $insertReviewFormElementSettingsSTMT, $errors)) { //from helperFunctions.php
										echo "OK\n";
									}
									else {
										echo "Failed\n";
									}
								}//end of the foreach setting
								unset($setting);
								}//closing the if count revFormElement settings > 0
								}//closing the if review_form_element_settings exist
								
							}//closing the if revFormElemIdOk
							
						}//end of the foreach review_form_element
						unset($revFormElement);
						}//closing the if count reviewForm elements > 0
						}//closing the if review_form_elements exists
						
					}//closing the if reviewFormOk
				
				}//closing the if not array_key_exists review_form_id
				
			}//end of the foreach review_forms
			unset($reviewForm);
			}//closing the if count reviewForms > 0
			}//closing the if review_forms exists
			
			///////  end of inserting the review_forms ////////////////////////////////////////////////////////
			
			validateData("section", $section);
		
			$section["review_form_new_id"] = null;
			
			if ($section["review_form_id"] !== null && array_key_exists($section["review_form_id"], $dataMapping["review_form_id"])) {
				$section["review_form_new_id"] = $dataMapping["review_form_id"][$section["review_form_id"]];
			}
			
			$sectionOk = false;
			
			echo "inserting section #". $section["section_id"] . " ........."; 
			
			$arr = array();
			$arr["data"] = $section;
			$arr["params"] = [
				["name" => ":section_journalId", "attr" => "journal_new_id", "type" => PDO::PARAM_INT],
				["name" => ":section_reviewFormId", "attr" => "review_form_new_id", "type" => PDO::PARAM_INT],
				["name" => ":section_seq", "attr" => "seq"],
				["name" => ":section_editorRestricted", "attr" => "editor_restricted", "type" => PDO::PARAM_INT],
				["name" => ":section_metaIndexed", "attr" => "meta_indexed", "type" => PDO::PARAM_INT],
				["name" => ":section_metaReviewed", "attr" => "meta_reviewed", "type" => PDO::PARAM_INT],
				["name" => ":section_abstractsNotRequired", "attr" => "abstracts_not_required", "type" => PDO::PARAM_INT],
				["name" => ":section_hideTitle", "attr" => "hide_title", "type" => PDO::PARAM_INT],
				["name" => ":section_hideAuthor", "attr" => "hide_author", "type" => PDO::PARAM_INT],
				["name" => ":section_hideAbout", "attr" => "hide_about", "type" => PDO::PARAM_INT],
				["name" => ":section_disableComments", "attr" => "disable_comments", "type" => PDO::PARAM_INT],
				["name" => ":section_abstractWordCount", "attr" => "abstract_word_count", "type" => PDO::PARAM_INT]
			];
			
			if (myExecute("insert", "section", $arr, $insertSectionSTMT, $errors)) { //from helperFunctions.php
				echo "OK\n";
				$numInsertedSections++;
				if (getNewId("section", $lastSectionsSTMT, $section, $dataMapping, $errors)) { //from helperFunctions.php
					echo "    new id = " . $section["section_new_id"] . "\n\n";
					$sectionOk = true;
				}
			}
			else {
				echo "Failed\n";
			}
			
			if ($sectionOk) {
				if (array_key_exists("settings", $section)) {
				foreach ($section["settings"] as &$setting) {
					validateData("section_settings", $setting); //from helperFunctions.php
				
					$setting["section_new_id"] = $section["section_new_id"];
					echo "    inserting ". $setting["setting_name"] . " with locale " . $setting["locale"] . " ........."; 
					
					$arr = array();
					$arr["data"] = $setting;
					$arr["params"] = [
						["name" => ":sectionSettings_sectionId", "attr" => "section_new_id", "type" => PDO::PARAM_INT],
						["name" => ":sectionSettings_locale", "attr" => "locale", "type" => PDO::PARAM_STR],
						["name" => ":sectionSettings_settingName", "attr" => "setting_name", "type" => PDO::PARAM_STR],
						["name" => ":sectionSettings_settingValue", "attr" => "setting_value", "type" => PDO::PARAM_STR],
						["name" => ":sectionSettings_settingType", "attr" => "setting_type", "type" => PDO::PARAM_STR]
					];
					
					if (myExecute("insert", "section_settings", $arr, $insertSectionSettingsSTMT, $errors)) { //from helperFunctions.php
						echo "OK\n";
					}
					else {
						echo "Failed\n";
					}
				}//end of foreach section_setting
				unset($setting);
				}//closing the if section_settings exist
			}//closing the if sectionOk
			
		}//closing the if section_id not in dataMapping
		
	}//end of foreach section
	unset($section);
	
	
	$returnData = array();
	$returnData["errors"] = $errors;
	$returnData["numInsertedRecords"] = array("review_forms" => $numInsertedRevForms, "sections" => $numInsertedSections);
	//$returnData["dataMapping"]  = $dataMapping;
	
	return $returnData;
	
}


// #06)
function insertUnpublishedArticles(&$xml, $conn, &$dataMapping, $journal, $args = null) {
	
	$journalNewPath = null;
	$journalNewId = null;
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists("journalNewPath", $args)) {
			$journalNewPath = $args["journalNewPath"];
		}
		if (array_key_exists("journalNewId", $args)) {
			$journalNewId = $args["journalNewId"];
		}
		if (array_key_exists("rowsLimit", $args)) {
			$limit = (int) $args["rowsLimit"];
		}
	}
	
	if (is_array($journal)) {
		if (array_key_exists("path", $journal)) {
			$journalNewPath = $journal["path"];
		}
		
		if(array_key_exists("journal_id", $journal)) {
			$journalNewId = $journal["journal_id"];
		}
	}
	
	if ($journalNewPath === null) {
		echo "\nThe journal path was not in journal:\n";
		print_r($journal);
		return -1;
	}
	if ($journalNewId === null) {
		echo "\nThe journal id was not in journal:\n";
		print_r($journal);
		return -1;
	}
	
	$sectionSTMT = $conn->prepare(
		"SELECT * FROM sections WHERE section_id IN (
			SELECT section_id FROM section_settings WHERE setting_name = 'title' AND setting_value = :section_title AND locale = :section_locale
		) AND journal_id = :section_journalId ");
	
	$userSTMT = $conn->prepare("SELECT * FROM users WHERE email = :user_email");
	
	$checkUsernameSTMT = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = :checkUsername");
	
	$insertUserSTMT = $conn->prepare("INSERT INTO users (username, password, salutation, first_name, middle_name, last_name, gender, initials, email, url, phone, fax, mailing_address,
country, locales, date_last_email, date_registered, date_validated, date_last_login, must_change_password, auth_id, disabled, disabled_reason, auth_str, suffix, billing_address, 
inline_help) VALUES (:insertUser_username, :insertUser_password, :insertUser_salutation, :insertUser_firstName, :insertUser_middleName, :insertUser_lastName, :insertUser_gender, 
:insertUser_initials, :insertUser_email, :insertUser_url, :insertUser_phone, :insertUser_fax, :insertUser_mailingAddress, :insertUser_country, :insertUser_locales, 
:insertUser_dateLastEmail, :insertUser_dateRegistered, :insertUser_dateValidated, :insertUser_dateLastLogin, :insertUser_mustChangePassword, :insertUser_authId, :insertUser_disabled, 
:insertUser_disabledReason, :insertUser_authStr, :insertUser_suffix, :insertUser_billingAddress, :insertUser_inlineHelp)");
	
	$lastUsersSTMT = $conn->prepare("SELECT * FROM users ORDER BY user_id DESC LIMIT $limit");
	
	$userStatements = array("userSTMT" => &$userSTMT, "checkUsernameSTMT" => &$checkUsernameSTMT, "insertUserSTMT" => &$insertUserSTMT, "lastUsersSTMT" => &$lastUsersSTMT);
	
	///// article info////////////////////////////////
	
	$insertArticleSTMT = $conn->prepare("INSERT INTO articles (user_id, journal_id, section_id, language, comments_to_ed, date_submitted, last_modified, date_status_modified,
		status, submission_progress, current_round, pages, fast_tracked, hide_author, comments_status, locale, citations) 
		VALUES (:userId, :journalId, :sectionId, :language, :commentsToEd, :dateSubmitted, :lastModified, :dateStatusModified,
		:status, :submissionProgress, :currentRound, :pages, :fastTracked, :hideAuthor, :commentsStatus, :locale, :citations)");
	
	//get the inserted article_id
	$lastArticlesSTMT = $conn->prepare("SELECT * FROM articles ORDER BY article_id DESC LIMIT $limit"); 
	
	$insertArticleSettingsSTMT = $conn->prepare("INSERT INTO article_settings (article_id, locale, setting_name, setting_value, setting_type) VALUES (:articleSettings_articleId,
		:articleSettings_locale, :articleSettings_settingName, :articleSettings_settingValue, :articleSettings_settingType)");
	
	//////////////  the article authors  ///////////////////////////////////////////////////////////////
	//see if the author is already registered
	$getAuthorSTMT = $conn->prepare("SELECT * FROM authors WHERE email = :getAuthor_email "); 
	
	$insertAuthorSTMT = $conn->prepare("INSERT INTO authors (submission_id, primary_contact, seq, first_name, middle_name, last_name, country,
		email, url, suffix) VALUES (:author_submissionId, :author_primaryContact, :author_seq, :author_firstName, :author_middleName, :author_lastName, :author_country,
		:author_email, :author_url, :author_suffix)");
	
	$insertAuthorSettingsSTMT = $conn->prepare("INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) VALUES (:authorSettings_authorId,
		:authorSettings_locale, :authorSettings_settingName, :authorSettings_settingValue, :authorSettings_settingType)");
	
	$lastAuthorsSTMT = $conn->prepare("SELECT * FROM authors ORDER BY author_id DESC LIMIT $limit"); 
	////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$insertArticleFileSTMT = $conn->prepare("INSERT INTO article_files (revision, source_revision, article_id, file_name, file_type, file_size, original_file_name, file_stage, 
		viewable, date_uploaded, date_modified, round, assoc_id) VALUES (:file_revision, :file_sourceRevision, :file_articleId, :file_fileName, :file_fileType, :file_fileSize, 
		:file_originalFileName, :file_fileStage, :file_viewable, :file_dateUploaded, :file_dateModified, :file_round, :file_assocId)");
	
	$lastArticleFilesSTMT = $conn->prepare("SELECT * FROM article_files ORDER BY file_id DESC LIMIT $limit");
	
	//article_supplementary_file  ///////
	$insertArticleSuppFileSTMT = $conn->prepare("INSERT INTO article_supplementary_files (file_id, article_id, type, language, date_created, show_reviewers, date_submitted, seq, 
		remote_url) VALUES (:supp_fileId, :supp_articleId, :supp_type, :supp_language, :supp_dateCreated, :supp_showReviewers, :supp_dateSubmitted, :supp_seq, :supp_remoteUrl)");
	
	$lastArticleSuppFilesSTMT = $conn->prepare("SELECT * FROM article_supplementary_files ORDER BY supp_id DESC LIMIT $limit");
	
	$insertArticleSuppFileSettingSTMT = $conn->prepare("INSERT INTO article_supp_file_settings (supp_id, locale, setting_name, setting_value, setting_type) VALUES (:suppSettings_suppId,
		:suppSettings_locale, :suppSettings_settingName, :suppSettings_settingValue, :suppSettings_settingType)");
	
	$insertArticleNoteSTMT = $conn->prepare("INSERT INTO article_notes (article_id, user_id, date_created, date_modified, title, note, file_id) VALUES (:note_articleId, :note_userId,
		:note_dateCreated, :note_dateModified, :note_title, :note_note, :note_fileId)");
	
	$lastArticleNotesSTMT = $conn->prepare("SELECT * FROM article_notes ORDER BY note_id DESC LIMIT $limit");
	
	$insertArticleCommentSTMT = $conn->prepare("INSERT INTO article_comments (comment_type, role_id, article_id, assoc_id, author_id, comment_title, comments, date_posted, date_modified,
		viewable) VALUES (:comment_commentType, :comment_roleId, :comment_articleId, :comment_assocId, :comment_authorId, :comment_commentTitle, :comment_comments, :comment_datePosted,
		:comment_dateModified, :comment_viewable)");
	
	$lastArticleCommentsSTMT = $conn->prepare("SELECT * FROM article_comments ORDER BY comment_id DESC LIMIT $limit");
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	//////////  galleys /////////////////////////////////////////////////////////////////////////
	$insertArticleGalleySTMT = $conn->prepare("INSERT INTO article_galleys (locale, article_id, file_id, label, html_galley, style_file_id, seq, remote_url) VALUES (:articleGalley_locale,
		:articleGalley_articleId, :articleGalley_fileId, :articleGalley_label, :articleGalley_htmlGalley, :articleGalley_styleFileId, :articleGalley_seq, :articleGalley_remoteUrl)");
	
	$lastArticleGalleysSTMT = $conn->prepare("SELECT * FROM article_galleys ORDER BY galley_id DESC LIMIT $limit");
	
	$insertArticleGalleySettingSTMT = $conn->prepare("INSERT INTO article_galley_settings (galley_id, locale, setting_name, setting_value, setting_type) VALUES (:galleySetting_galleyId,
		:galleySetting_locale, :galleySetting_settingName, :galleySetting_settingValue, :galleySetting_settingType)");
	
	$insertArticleXmlGalleySTMT = $conn->prepare("INSERT INTO article_xml_galleys (galley_id, article_id, label, galley_type, views) VALUES (:xmlGalley_galleyId, :xmlGalley_articleId,
		:xmlGalley_label, :xmlGalley_galleyType, :xmlGalley_views)");
	
	$lastArticleXmlGalleysSTMT = $conn->prepare("SELECT * FROM article_xml_galleys ORDER BY xml_galley_id DESC LIMIT $limit");
	
	$insertArticleHtmlGalleyImageSTMT = $conn->prepare("INSERT INTO article_html_galley_images (galley_id, file_id) VALUES (:galleyImage_galleyId, :galleyImage_fileId)");
	/////////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	/////////////////  the article search keywords  ////////////////////////////////////////
	$getKeywordSTMT = $conn->prepare("SELECT * FROM article_search_keyword_list WHERE keyword_text = :getKeyword_keywordText");
	$insertArticleSearchKeywordListSTMT = $conn->prepare("INSERT INTO article_search_keyword_list (keyword_text) VALUES (:keywordList_keywordText)");
	
	$lastArticleSearchKeywordListsSTMT = $conn->prepare("SELECT * FROM article_search_keyword_list ORDER BY keyword_id DESC LIMIT $limit");
	
	$insertArticleSearchObjectKeywordSTMT = $conn->prepare("INSERT INTO article_search_object_keywords (object_id, keyword_id, pos) VALUES (:objectKeyword_objectId,
		:objectKeyword_keywordId, :objectKeyword_pos)");
	$insertArticleSearchObjectSTMT = $conn->prepare("INSERT INTO article_search_objects (article_id, type, assoc_id) VALUES (:searchObj_articleId, :searchObj_type, :searchObj_assocId)");
	
	$lastArticleSearchObjectsSTMT = $conn->prepare("SELECT * FROM article_search_objects ORDER BY object_id DESC LIMIT $limit");
	/////////////////////////////////////////////////////////////////////////////////////////
	
	//////////////// the edit decisions and assignments  ///////////////////////////////////
	$insertEditDecisionSTMT = $conn->prepare("INSERT INTO edit_decisions (article_id, round, editor_id, decision, date_decided) VALUES (:editDecision_articleId, :editDecision_round,
		:editDecision_editorId, :editDecision_decision, :editDecision_dateDecided)");
	
	$lastEditDecisionsSTMT = $conn->prepare("SELECT * FROM edit_decisions ORDER BY edit_decision_id DESC LIMIT $limit");
	
	$insertEditAssignmentSTMT = $conn->prepare("INSERT INTO edit_assignments (article_id, editor_id, can_edit, can_review, date_assigned, date_notified, date_underway) VALUES (
		:editAssign_articleId, :editAssign_editorId, :editAssign_canEdit, :editAssign_canReview, :editAssign_dateAssigned, :editAssign_dateNotified, :editAssign_dateUnderway)");
	
	$lastEditAssignmentsSTMT = $conn->prepare("SELECT * FROM edit_assignments ORDER BY edit_id DESC LIMIT $limit");
	////////////////////////////////////////////////////////////////////////////////////////
	
	///////////////  the review rounds, assignments and responses  //////////////////////////////////////
	
	$insertReviewRoundSTMT = $conn->prepare("INSERT INTO review_rounds (submission_id, stage_id, round, review_revision, status) VALUES (:revRound_submissionId,
		:revRound_stageId, :revRound_round, :revRound_reviewRevision, :revRound_status)");
	
	$lastReviewRoundsSTMT = $conn->prepare("SELECT * FROM review_rounds ORDER BY review_round_id DESC LIMIT $limit");
	
	$insertReviewAssignmentSTMT = $conn->prepare("INSERT INTO review_assignments (submission_id, reviewer_id, competing_interests, regret_message, recommendation, date_assigned,
	date_notified, date_confirmed, date_completed, date_acknowledged, date_due, last_modified, reminder_was_automatic, declined, replaced, cancelled, reviewer_file_id, date_rated,
	date_reminded, quality, review_round_id, stage_id, review_method, round, step, review_form_id, unconsidered) VALUES (:revAssign_submissionId, :revAssign_reviewerId,
	:revAssign_competingInterests, :revAssign_regretMessage, :revAssign_recommendation, :revAssign_dateAssigned, :revAssign_dateNotified, :revAssign_dateConfirmed, 
	:revAssign_dateCompleted, :revAssign_dateAcknowledged, :revAssign_dateDue, :revAssign_lastModified, :revAssign_reminderAuto, :revAssign_declined, :revAssign_replaced,
	:revAssign_cancelled, :revAssign_reviewerFileId, :revAssign_dateRated, :revAssign_dateReminded, :revAssign_quality, :revAssign_reviewRoundId, :revAssign_stageId, 
	:revAssign_reviewMethod, :revAssign_round, :revAssign_step, :revAssign_reviewFormId, :revAssign_unconsidered)");
	
	$lastReviewAssignmentsSTMT = $conn->prepare("SELECT * FROM review_assignments ORDER BY review_id DESC LIMIT $limit");
	
	$insertReviewFormResponseSTMT = $conn->prepare("INSERT INTO review_form_responses (review_form_element_id, review_id, response_type, response_value) 
	VALUES (:response_reviewFormElementId, :reponse_reviewId, :response_responseType, :response_reponseValue)");
	
	////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$unpublished_articles = null;
	
	if ($xml->nodeName === "unpublished_articles") {
		$unpublished_articles = $xml;
	}
	else {
		$unpublished_articles = $xml->getElementsByTagName("unpublished_articles")->item(0);
	}
	
	$unpubArticles = xmlToArray($unpublished_articles, true); //array with the unpublished articles
	
	$insertedUsers = array();
	
	if ($dataMapping === null) {
		$dataMapping = array();
	}
	
	if (!array_key_exists("journal_id", $dataMapping)) {
		$journalOldId = $unpublished_articles->getAttribute("journal_original_id");
		$dataMapping["journal_id"] = array($journalOldId => $journalNewId);
	}
	
	if (!array_key_exists("article_id", $dataMapping))  $dataMapping["article_id"] = array();
	if (!array_key_exists("author_id", $dataMapping)) $dataMapping["author_id"] = array();
	if (!array_key_exists("comment_id", $dataMapping)) $dataMapping["comment_id"] = array();
	if (!array_key_exists("comment_author_id", $dataMapping)) $dataMapping["comment_author_id"] = array();
	if (!array_key_exists("editor_id", $dataMapping)) $dataMapping["editor_id"] = array();
	if (!array_key_exists("edit_id", $dataMapping)) $dataMapping["edit_id"] = array();
	if (!array_key_exists("edit_decision_id", $dataMapping)) $dataMapping["edit_decision_id"] = array();
	if (!array_key_exists("file_id", $dataMapping)) $dataMapping["file_id"] = array();
	if (!array_key_exists("galley_id", $dataMapping)) $dataMapping["galley_id"] = array();
	if (!array_key_exists("keyword_id", $dataMapping)) $dataMapping["keyword_id"] = array();
	if (!array_key_exists("object_id", $dataMapping)) $dataMapping["object_id"] = array();
	if (!array_key_exists("review_id", $dataMapping)) $dataMapping["review_id"] = array();
	if (!array_key_exists("reviewer_id", $dataMapping)) $dataMapping["reviewer_id"] = array();
	if (!array_key_exists("review_form_id", $dataMapping)) $dataMapping["review_form_id"] = array();
	if (!array_key_exists("review_form_element_id", $dataMapping)) $dataMapping["review_form_element_id"] = array();
	if (!array_key_exists("review_round_id", $dataMapping)) $dataMapping["review_round_id"] = array();
	if (!array_key_exists("section_id", $dataMapping)) $dataMapping["section_id"] = array();
	if (!array_key_exists("supp_id", $dataMapping)) $dataMapping["supp_id"] = array();
	if (!array_key_exists("user_id", $dataMapping)) $dataMapping["user_id"] = array();
	if (!array_key_exists("xml_galley_id", $dataMapping)) $dataMapping["xml_galley_id"] = array();
	if (!array_key_exists("file_name", $dataMapping)) $dataMapping["file_name"] = array();
	if (!array_key_exists("original_file_name", $dataMapping)) $dataMapping["original_file_name"] = array();
		
	$errors = array(
		"article" => array("insert" => array(), "update" => array()),
		"article_settings" => array("insert" => array(), "update" => array()),
		"article_file" => array("insert" => array(), "update" => array()),
		"article_supplementary_file" => array("insert" => array(), "update" => array()),
		"article_supp_file_settings" => array("insert" => array(), "update" => array()),
		"article_note" => array("insert" => array(), "update" => array()),
		"article_comment" => array("insert" => array(), "update" => array()),
		"article_galley" => array("insert" => array(), "update" => array()),
		"article_galley_settings" => array("insert" => array(), "update" => array()),
		"article_xml_galley" => array("insert" => array(), "update" => array()),
		"article_html_galley_image" => array("insert" => array(), "update" => array()),
		"article_search_object" => array("insert" => array(), "update" => array()),
		"article_search_object_keyword" => array("insert" => array(), "update" => array()),
		"article_search_keyword_list" => array("insert" => array(), "update" => array()),
		"edit_assignment" => array("insert" => array(), "update" => array()),
		"edit_decision" => array("insert" => array(), "update" => array()),
		"author" => array("insert" => array(), "update" => array()),
		"author_settings" => array("insert" => array(), "update" => array()),
		"review_assignment" => array("insert" => array(), "update" => array()),
		"review_form_response" => array("insert" => array(), "update" => array()),
		"review_round" => array("insert" => array(), "update" => array()),
		"user" => array("insert" => array(), "update" => array())
	);	
	
	$insertedArticles = 0;
	
	///////////////////  BEGINNING OF THE INSERT STAGE  //////////////////////////////////////////////////////////////////////////
	
	//loop through every article to insert the preliminary data in the database
	//and map the id's in the old database to the id's in the new one
	foreach($unpubArticles as &$article) {
		
		if (array_key_exists($article["article_id"], $dataMapping["article_id"])) {
			echo "\narticle #" . $article["article_id"] . " was already imported.\n";
			continue; // go to the next article
		}
		
		$article["journal_new_id"] = $journalNewId;
		
		$userOk = false;
		$sectionOk = false;
		
		$articleOk = false;
		$articleFileOk = false;
		$articleSuppFileOk = false;
		
		$articleSettings = null;
		$articleFile = null;
		$articleSuppFile = null;
		
		$error = array("article_id" => $article["article_id"]);
		
		// get the user that owns the article to identify on the new journal  ///////////////////
		$userOk = processUser($article["user"], array("type" => "article", "data" => $article), $dataMapping, $errors, $insertedUsers, $userStatements);
		
		
		// get the section new id //////////////////////////////////////
		
		if (array_key_exists($article["section_id"], $dataMapping["section_id"])) {
			$article["section_new_id"] = $dataMapping["section_id"][$article["section_id"]];
			$sectionOk = true;
		}
		else {
			//the section_id is not yet on dataMapping
			$section = $article["section"];
			
			$locales = array_keys($section);
			
			// if original_id is one one the keys remove it from the array $locales
			$pos = array_search("original_id", $locales);
			
			if ($pos !== false) {
				array_splice($locales, $pos, 1); //remove the element at position $pos
			}
			///////////////////////////////////////////////////////////////////////
			
			foreach ($locales as $locale) {
				if (array_key_exists("title", $section[$locale])) {
					$sectionSTMT->bindParam(":section_title", $section[$locale]["title"], PDO::PARAM_STR);
					$sectionSTMT->bindParam(":section_locale", $locale, PDO::PARAM_STR);
					break;
				}
			}
			
			$sectionSTMT->bindParam(":section_journalId", $journalNewId, PDO::PARAM_INT);
			
			if ($sectionSTMT->execute()) {
				$sectionInfo = $sectionSTMT->fetch(PDO::FETCH_ASSOC);
				$article["section_new_id"] = $sectionInfo["section_id"];
				$sectionOk = true;
				
				$section["new_id"] = $sectionInfo["section_id"];
				
				if (!array_key_exists($article["section_id"], $dataMapping["section_id"])) {
					$dataMapping["section_id"][$article["section_id"]] = $article["section_new_id"];
				}
				
			}
			else {
				//sectionSTMT did not execute
				$error["sectionSTMT"] = ["section" => $section, "error" => $sectionSTMT->errorInfo()];
				$sectionOk = false;
			}
		}
		
		// end of get the section new id  ///////////////////////////////////
		
		if ($sectionOk && $userOk) {
			
			$article["user_new_id"] = $article["user"]["user_new_id"];
			
			validateData("article", $article); //from helperFunctions.php
			 
			$arr = array();
			$arr["data"] = $article;
			$arr["params"] = [
				["name" => ":userId", "attr" => "user_new_id", "type" => PDO::PARAM_INT],
				["name" => ":journalId", "attr" => "journal_new_id", "type" => PDO::PARAM_INT],
				["name" => ":sectionId", "attr" => "section_new_id", "type" => PDO::PARAM_INT],
				["name" => ":language", "attr" => "language", "type" => PDO::PARAM_STR],
				["name" => ":commentsToEd", "attr" => "comments_to_ed", "type" => PDO::PARAM_STR],
				["name" => ":dateSubmitted", "attr" => "date_submitted", "type" => PDO::PARAM_STR],
				["name" => ":lastModified", "attr" => "last_modified", "type" => PDO::PARAM_STR],
				["name" => ":dateStatusModified", "attr" => "date_status_modified", "type" => PDO::PARAM_STR],
				["name" => ":status", "attr" => "status", "type" => PDO::PARAM_INT],
				["name" => ":submissionProgress", "attr" => "submission_progress", "type" => PDO::PARAM_INT],
				["name" => ":currentRound", "attr" => "current_round", "type" => PDO::PARAM_INT],
				["name" => ":pages", "attr" => "pages", "type" => PDO::PARAM_STR],
				["name" => ":fastTracked", "attr" => "fast_tracked", "type" => PDO::PARAM_INT],
				["name" => ":hideAuthor", "attr" => "hide_author", "type" => PDO::PARAM_INT],
				["name" => ":commentsStatus", "attr" => "comments_status", "type" => PDO::PARAM_INT],
				["name" => ":locale", "attr" => "locale", "type" => PDO::PARAM_STR],
				["name" => ":citations", "attr" => "citations", "type" => PDO::PARAM_STR]
			];
			
			
			echo "\ninserting article #" . $article["article_id"] . " ......... "; 
			
			if (myExecute("insert", "article", $arr, $insertArticleSTMT, $errors)) { //from helperFunctions.php
				echo "OK\n";
				$insertedArticles++;
				
				$args = array();
				$args["compare"] = "almost all";
				$args["notCompare"] = ["submission_file_id", "revised_file_id", "review_file_id", "editor_file_id"];
				
				if (getNewId("article", $lastArticlesSTMT, $article, $dataMapping, $errors, $args)) { //from helperFunctions.php
					echo "article new id = " . $article["article_new_id"] . "\n";
					$articleOk = true;
				}
				
			}
			else {
				echo "Failed\n";
			}
			
		}
		else {
			array_push($errors["article"]["insert"], $error);
		}
		
		//if the article has been correctly inserted
		if ($articleOk) {
			
			//insert the article_settings /////////////////////////
			if (array_key_exists("settings", $article)) { if (!empty($article["settings"]) && $article["settings"] != null) {
			echo "\ninserting article_settings:\n";
			
			//insert each article setting
			foreach($article["settings"] as &$articleSetting) { 
				
				validateData("article_settings", $articleSetting); //from helperFunctions.php
				
				$articleSetting["article_new_id"] = $article["article_new_id"];
				echo "    inserting ". $articleSetting["setting_name"] . " with locale " . $articleSetting["locale"] . " ........."; 
				
				$arr = array();
				$arr["data"] = $articleSetting;
				$arr["params"] = [
					["name" => ":articleSettings_articleId", "attr" => "article_new_id", "type" => PDO::PARAM_INT],
					["name" => ":articleSettings_locale", "attr" => "locale", "type" => PDO::PARAM_STR],
					["name" => ":articleSettings_settingName", "attr" => "setting_name", "type" => PDO::PARAM_STR],
					["name" => ":articleSettings_settingValue", "attr" => "setting_value", "type" => PDO::PARAM_STR],
					["name" => ":articleSettings_settingType", "attr" => "setting_type", "type" => PDO::PARAM_STR]
				];
				
				if (myExecute("insert", "article_settings", $arr, $insertArticleSettingsSTMT, $errors)) { //from helperFunctions.php
					echo "OK\n";
				}
				else {
					echo "Failed\n";
				}
			}//end of foreach article settings
			unset($articleSetting);
			}// closing the if count settings > 0
			}//closing the if article settings exists
			
			////////////// end of insert article_settings  //////////////////////
			
			
			/////////////// insert author ///////////////////////////////////////
			echo "\ninserting authors:\n";
			foreach ($article["authors"] as $author) {
				$authorOk = false;
				$author["submission_new_id"] = $article["article_new_id"];
				
				validateData("author", $author); //from helperFunctions.php
				
				$arr = array();
				$arr["data"] = $author;
				$arr["params"] = [
					["name" => ":author_submissionId", "attr" => "submission_new_id", "type" => PDO::PARAM_INT],
					["name" => ":author_primaryContact", "attr" => "primary_contact", "type" => PDO::PARAM_INT],
					["name" => ":author_seq", "attr" => "seq"],
					["name" => ":author_firstName", "attr" => "first_name", "type" => PDO::PARAM_STR],
					["name" => ":author_middleName", "attr" => "middle_name", "type" => PDO::PARAM_STR],
					["name" => ":author_lastName", "attr" => "last_name", "type" => PDO::PARAM_STR],
					["name" => ":author_country", "attr" => "country", "type" => PDO::PARAM_STR],
					["name" => ":author_email", "attr" => "email", "type" => PDO::PARAM_STR],
					["name" => ":author_url", "attr" => "url", "type" => PDO::PARAM_STR],
					["name" => ":author_suffix", "attr" => "suffix", "type" => PDO::PARAM_STR],
				];
				
				echo "\ninserting author #" . $author["author_id"] . " ......... "; 
			
				if (myExecute("insert", "author", $arr, $insertAuthorSTMT, $errors)) { //from helperFunctions.php
					echo "OK\n";
					
					$args = array();
					$args["compare"] = ["email" => "email"];
					
					if (getNewId("author", $lastAuthorsSTMT, $author, $dataMapping, $errors, $args)) { //from helperFunctions.php
						echo "author new id = " . $author["author_new_id"] . "\n";
						$authorOk = true;
					}
					
				}
				else {
					echo "Failed\n";
				}
				
			}//end of foreach author
			
			//// end of the insert author ///////////////////////////
			
			
			// insert article_files ////////////////////////////////
			if (array_key_exists("files", $article)) {  if (!empty($article["files"]) && $article["files"] != null) {
			echo "\ninserting article_files:\n";
			
			//insert each article_file
			foreach ($article["files"] as &$articleFile) {
				
				validateData("article_file", $articleFile); //from helperFunctions.php
				
				$articleIdOk = false;
				
				if (array_key_exists($articleFile["article_id"], $dataMapping["article_id"])) {
					$articleFile["article_new_id"] = $dataMapping["article_id"][$articleFile["article_id"]];
					$articleIdOk = true;
				}
				
				if ($articleIdOk) {
					
					$arr = array();
					$arr["data"] = $articleFile;
					$arr["params"] = [
						["name" => ":file_revision", "attr" => "revision", "type" => PDO::PARAM_INT],
						["name" => ":file_sourceRevision", "attr" => "source_revision", "type" => PDO::PARAM_INT],
						["name" => ":file_articleId", "attr" => "article_new_id", "type" => PDO::PARAM_INT],
						["name" => ":file_fileName", "attr" => "file_name", "type" => PDO::PARAM_STR],
						["name" => ":file_fileType", "attr" => "file_type", "type" => PDO::PARAM_STR],
						["name" => ":file_originalFileName", "attr" => "original_file_name", "type" => PDO::PARAM_STR],
						["name" => ":file_fileSize", "attr" => "file_size", "type" => PDO::PARAM_INT],
						["name" => ":file_fileStage", "attr" => "file_stage", "type" => PDO::PARAM_INT],
						["name" => ":file_viewable", "attr" => "viewable", "type" => PDO::PARAM_INT],
						["name" => ":file_dateUploaded", "attr" => "date_uploaded", "type" => PDO::PARAM_STR],
						["name" => ":file_dateModified", "attr" => "date_modified", "type" => PDO::PARAM_STR],
						["name" => ":file_round", "attr" => "round", "type" => PDO::PARAM_INT],
						["name" => ":file_assocId", "attr" => "assoc_id", "type" => PDO::PARAM_INT]
					];
					
					echo "    inserting article file #" . $articleFile["file_id"]. "............ ";
					
					if (myExecute("insert", "article_file", $arr, $insertArticleFileSTMT, $errors)) { //from helperFunctions.php
						echo "OK\n";
						$args = array();
						$args["compare"] = "almost all";
						$args["notCompare"] = ["file_name","original_file_name", "source_file_id"];
						
						if (getNewId("article_file", $lastArticleFilesSTMT, $articleFile, $dataMapping, $errors, $args)) { //from helperFunctions.php
							echo "    file new id = " . $articleFile["file_new_id"] . "\n\n";
							$articleFileOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
				}
				else {
					$error = ["file_id" => $articleFile["file_id"], "error" => "article_id ". $articleFile["article_id"] . " not found on dataMappings."];
					array_push($errors["article_file"]["insert"], $error);
				}
				
			}//end of foreach article_files
			unset($articleFile);
			}//closing the if article files is empty
			}//closing the if article files exist
			
			/////////////////// end of insert article_files /////////////////////////
			
			
			/////////////// insert article_supplementary_files ///////////////////////
			if (array_key_exists("supplementary_files", $article)) {  if (!empty($article["supplementary_files"]) && $article["supplementary_files"] != null) {
			echo "inserting article_supplementary_files:\n";
			foreach ($article["supplementary_files"] as &$articleSuppFile) {
				
				validateData("article_supplementary_file", $articleSuppFile); //from helperFunctions.php
				
				$articleSuppFileOk = false;
				$fileIdOk = false;
				$articleIdOk = false;
				if (array_key_exists($articleSuppFile["file_id"], $dataMapping["file_id"])) {
					$articleSuppFile["file_new_id"] = $dataMapping["file_id"][$articleSuppFile["file_id"]];
					$fileIdOk = true;
				}
				if (array_key_exists($articleSuppFile["article_id"], $dataMapping["article_id"])) {
					$articleSuppFile["article_new_id"] = $dataMapping["article_id"][$articleSuppFile["article_id"]];
					$articleIdOk = true;
				}
				
				if ($fileIdOk && $articleIdOk) {
					
					$arr = array();
					$arr["data"] = $articleSuppFile;
					$arr["params"] = [
						["name" => ":supp_fileId", "attr" => "file_new_id", "type" => PDO::PARAM_INT],
						["name" => ":supp_articleId", "attr" => "article_new_id", "type" => PDO::PARAM_INT],
						["name" => ":supp_type", "attr" => "type", "type" => PDO::PARAM_STR],
						["name" => ":supp_language", "attr" => "language", "type" => PDO::PARAM_STR],
						["name" => ":supp_showReviewers", "attr" => "show_reviewers", "type" => PDO::PARAM_INT],
						["name" => ":supp_dateCreated", "attr" => "date_created", "type" => PDO::PARAM_STR],
						["name" => ":supp_dateSubmitted", "attr" => "date_submitted", "type" => PDO::PARAM_STR],
						["name" => ":supp_seq", "attr" => "seq"],
						["name" => ":supp_remoteUrl", "attr" => "remote_url", "type" => PDO::PARAM_STR]
					];
					
					echo "    inserting article supplemetary file #" . $articleSuppFile["supp_id"]. "............ ";
					
					if (myExecute("insert", "article_supplementary_file", $arr, $insertArticleSuppFileSTMT, $errors)) { //from helperFunctions.php
						echo "OK\n";
						if (getNewId("article_supplementary_file", $lastArticleSuppFilesSTMT, $articleSuppFile, $dataMapping, $errors)) { //from helperFunctions.php
							echo "    new id = " . $articleSuppFile["supp_new_id"] . "\n\n";
							$articleSuppFileOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
				}
				else {
					if (!$articleIdOk) {
						$error = ["supp_id" => $articleSuppFile["supp_id"], "error" => "article_id ". $articleSuppFile["article_id"] . " not found on dataMappings."];
						array_push($errors["article_supplementary_file"]["insert"], $error);
					}
					if (!$fileIdOk) {
						$error = ["file_id" => $articleSuppFile["file_id"], "error" => "file_id ". $articleSuppFile["file_id"] . " not found on dataMappings."];
						array_push($errors["article_supplementary_file"]["insert"], $error);
					}
				}
				
				
				if ($articleSuppFileOk) {
				
				/////////////// insert the article_supplementary_file_settings /////////////////////////
					if (array_key_exists("settings", $articleSuppFile)) { if (!empty($articleSuppFile["settings"])) {
					echo "\ninserting article_settings:\n";
					
					//insert each article setting
					foreach($articleSuppFile["settings"] as &$setting) {
						
						validateData("article_supp_file_settings", $setting); //from helperFunctions.php
						
						$setting["supp_new_id"] = $articleSuppFile["supp_new_id"];
						echo "    inserting ". $setting["setting_name"] . " with locale " . $setting["locale"] . " ........."; 
						
						$arr = array();
						$arr["data"] = $setting;
						$arr["params"] = [
							["name" => ":suppSettings_suppId", "attr" => "supp_new_id", "type" => PDO::PARAM_INT],
							["name" => ":suppSettings_locale", "attr" => "locale", "type" => PDO::PARAM_STR],
							["name" => ":suppSettings_settingName", "attr" => "setting_name", "type" => PDO::PARAM_STR],
							["name" => ":suppSettings_settingValue", "attr" => "setting_value", "type" => PDO::PARAM_STR],
							["name" => ":suppSettings_settingType", "attr" => "setting_type", "type" => PDO::PARAM_STR]
						];
						
						if (myExecute("insert", "article_supp_file_settings", $arr, $insertArticleSuppFileSettingSTMT, $errors)) { //from helperFunctions.php
							echo "OK\n";
						}
						else {
							echo "Failed\n";
						}
					}//end of the foreach setting
					unset($setting);
					}//closing the if articleSuppFile["settings"] is empty
					}//closing the if articleSuppFile settings exist
					
				///////////////////end of insert article_settings  //////////////////////
				
				}// end of the if articleSuppFileOk
				
			} //end of foreach article supplementary file
			unset($articleSuppFile);
			}//closing the if supplementary files is empty
			}//closing the if supplementary_files exist
			
			///////////////// end of insert article_supplementary_files ////////////////////////
		
		
			/////////////////////// insert article comments  ///////////////////////////////////
			if (array_key_exists("comments", $article)) { if (!empty($article["comments"])) {
			echo "\ninserting article comments...\n";
			
			foreach ($article["comments"] as &$articleComment) {
				
				validateData("article_comment", $articleComment); //from helperFunctions.php
				
				$articleIdOk = false;
				$authorIdOk = false;
				$fileIdOk = false;
				$articleCommentOk = false;
				
				if (array_key_exists($articleComment["article_id"], $dataMapping["article_id"])) {
					$articleComment["article_new_id"] = $dataMapping["article_id"][$articleComment["article_id"]];
					$articleIdOk = true;
				}
				
				if (array_key_exists($articleComment["author_id"], $dataMapping["comment_author_id"])) {
					$articleComment["author_new_id"] = $dataMapping["comment_author_id"][$articleComment["author_id"]];
					$authorIdOk = true;
				}
				else if (array_key_exists($articleComment["author_id"], $dataMapping["user_id"])) {
					$dataMapping["comment_author_id"][$articleComment["author_id"]] = $dataMapping["user_id"][$articleComment["author_id"]];
					$articleComment["author_new_id"] = $dataMapping["user_id"][$articleComment["author_id"]];
					$authorIdOk = true;
				}
				else {
					
					$authorIdOk = processUser($articleComment["author"], array("type" => "article_comment", "data" => $articleComment), $dataMapping, $errors, $insertedUsers, $userStatements);
					
					if ($authorIdOk) {
						
						$articleComment["author_new_id"] = $articleComment["author"]["user_new_id"];
						
						if (!array_key_exists($articleComment["author_id"], $dataMapping["comment_author_id"])) {
							$dataMapping["comment_author_id"][$articleComment["author_id"]] = $articleComment["author_new_id"];
						}
						
					}
					
				}
				
				if ($articleIdOk && $authorIdOk) {
					
					$arr = array();
					$arr["data"] = $articleComment;
					$arr["params"] = [
						["name" => ":comment_commentType", "attr" => "comment_type", "type" => PDO::PARAM_INT],
						["name" => ":comment_roleId", "attr" => "role_id", "type" => PDO::PARAM_INT],
						["name" => ":comment_articleId", "attr" => "article_new_id", "type" => PDO::PARAM_INT],
						["name" => ":comment_assocId", "attr" => "assoc_id", "type" => PDO::PARAM_INT],
						["name" => ":comment_authorId", "attr" => "author_new_id", "type" => PDO::PARAM_INT],
						["name" => ":comment_commentTitle", "attr" => "comment_title", "type" => PDO::PARAM_STR],
						["name" => ":comment_comments", "attr" => "comments", "type" => PDO::PARAM_STR],
						["name" => ":comment_datePosted", "attr" => "date_posted", "type" => PDO::PARAM_STR],
						["name" => ":comment_dateModified", "attr" => "date_modified", "type" => PDO::PARAM_STR],
						["name" => ":comment_viewable", "attr" => "viewable", "type" => PDO::PARAM_INT]
					];
					
					echo "    inserting article comment #" . $articleComment["comment_id"]. "............ ";
					
					if (myExecute("insert", "article_comment", $arr, $insertArticleCommentSTMT, $errors)) { //from helperFunctions.php
						echo "OK\n";
						if (getNewId("article_comment", $lastArticleCommentsSTMT, $articleComment, $dataMapping, $errors)) { //from helperFunctions.php
							echo "    new id = " . $articleComment["comment_new_id"] . "\n\n";
							$articleCommentOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
					
				}
				else {
					if (!$articleIdOk) {
						$error = ["comment_id" => $articleComment["comment_id"], "error" => "article_id ". $articleComment["article_id"] . " not found on dataMappings."];
						array_push($errors["article_comment"]["insert"], $error);
					}
					if (!$authorIdOk) {
						$error = ["comment_id" => $articleComment["comment_id"], "error" => "author_id ". $articleComment["author_id"] . " not found on dataMappings."];
						array_push($errors["article_comment"]["insert"], $error);
					}
				}
			}//end of the foreach article comments
			unset($articleComment);
			}//closing the if article comment is empty
			}//closing the if article comments exists
			
			//////////////////  end of insert article comments  ////////////////////////////////////////
			
			
			///////////////////// insert article galleys /////////////////////////////////////
			if (array_key_exists("galleys", $article)) { if (!empty($article["galleys"])) {
			echo "\ninserting article galleys...\n";
			
			foreach ($article["galleys"] as &$articleGalley) {
				
				validateData("article_galley", $articleGalley); //from helperFunctions.php
				
				$articleGalleyOk = false;
				
				$articleIdOk = false;
				$fileIdOk = false;
				$styleFileIdOk = false;
				
				$error = ["galley_id" => $articleGalley["galley_id"]];
				
				if (array_key_exists($articleGalley["article_id"], $dataMapping["article_id"])) {
					$articleGalley["article_new_id"] = $dataMapping["article_id"][$articleGalley["article_id"]];
					$articleIdOk = true;
				}
				else {
					$error["article_id"] = ["article_id" => $articleGalley["article_id"], "error" => "article_id not in dataMapping"];
				}
				
				if (array_key_exists($articleGalley["file_id"], $dataMapping["file_id"])) {
					$articleGalley["file_new_id"] = $dataMapping["file_id"][$articleGalley["file_id"]];
					$fileIdOk = true;
				}
				else {
					$error["file_id"] = ["file_id" => $articleGalley["file_id"], "error" => "file_id not in dataMapping"];
				}
				
				if ($articleGalley["style_file_id"] === null) {
					$articleGalley["style_file_new_id"] = null;
					$styleFileIdOk = true;
				}
				else if (array_key_exists($articleGalley["style_file_id"], $dataMapping["file_id"])) {
					$dataMapping["style_file_id"][$articleGalley["style_file_id"]] = $dataMapping["file_id"];
				}
				else {
					$error["style_file_id"] = ["style_file_id" => $articleGalley["style_file_id"], "error" => "style_file_id not in dataMapping"];
				}
				
				
				
				if ($articleIdOk && $fileIdOk && $styleFileIdOk) {
					
					$arr = array();
					$arr["data"] = $articleGalley;
					$arr["params"] = [
						["name" => ":articleGalley_locale", "attr" => "locale", "type" => PDO::PARAM_STR],
						["name" => ":articleGalley_articleId", "attr" => "article_new_id", "type" => PDO::PARAM_INT],
						["name" => ":articleGalley_fileId", "attr" => "file_new_id", "type" => PDO::PARAM_INT],
						["name" => ":articleGalley_label", "attr" => "label", "type" => PDO::PARAM_STR],
						["name" => ":articleGalley_htmlGalley", "attr" => "html_galley", "type" => PDO::PARAM_INT],
						["name" => ":articleGalley_styleFileId", "attr" => "style_file_new_id", "type" => PDO::PARAM_INT],
						["name" => ":articleGalley_seq", "attr" => "seq"],
						["name" => ":articleGalley_remoteUrl", "attr" => "remote_url", "type" => PDO::PARAM_STR]
					];
					
					echo "    inserting article galley #" . $articleGalley["galley_id"]. "............ ";
					
					if (myExecute("insert", "article_galley", $arr, $insertArticleGalleySTMT, $errors)) { //from helperFunctions.php
						echo "OK\n";
						if (getNewId("article_galley", $lastArticleGalleysSTMT, $articleGalley, $dataMapping, $errors)) { //from helperFunctions.php
							echo "    new id = " . $articleGalley["galley_new_id"] . "\n\n";
							$articleGalleyOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
				}
				else {
					array_push($errors["article_galley"]["insert"], $error);
				}
				
				if ($articleGalleyOk) {
					
					//////////////// insert the article_galley_settings /////////////////////////
					if (array_key_exists("settings", $articleGalley)) { if (!empty($articleGalley["settings"])) {
					echo "\ninserting article_galley_settings:\n";
					
					//insert each article galley setting
					foreach($articleGalley["settings"] as &$setting) {
						
						validateData("article_galley_settings", $setting); //from helperFunctions.php 
						
						$setting["galley_new_id"] = $articleGalley["galley_new_id"];
						
						echo "    inserting ". $setting["setting_name"] . " with locale " . $setting["locale"] . " ........."; 
						
						
						$arr = array();
						$arr["data"] = $setting;
						$arr["params"] = [
							["name" => ":galleySetting_galleyId", "attr" => "galley_new_id", "type" => PDO::PARAM_INT],
							["name" => ":galleySetting_locale", "attr" => "locale", "type" => PDO::PARAM_STR],
							["name" => ":galleySetting_settingName", "attr" => "setting_name", "type" => PDO::PARAM_STR],
							["name" => ":galleySetting_settingValue", "attr" => "setting_value", "type" => PDO::PARAM_STR],
							["name" => ":galleySetting_settingType", "attr" => "setting_type", "type" => PDO::PARAM_STR]
						];
						
						if (myExecute("insert", "article_galley_settings", $arr, $insertArticleGalleySettingSTMT, $errors)) { //from helperFunctions.php
							echo "OK\n";
						}
						else {
							echo "Failed\n";
						}
					}
					unset($setting);
					}//closing the if articleGalley settings is empty
					}//closing the if articleGalley settings exists
					
				//////////// end of insert article_galley_settings  //////////////////////
				
				///////////// insert article_xml_galleys /////////////////////////
					if (array_key_exists("xml_galley", $articleGalley)) { if (!empty($articleGalley["xml_galley"])) {
					echo "\ninserting article_xml_galleys:\n";
					
					//insert each article xml galley
					foreach($articleGalley["xml_galley"] as &$xmlGalley) {
						
						validateData("article_xml_galley", $xmlGalley); //from helperFunctions.php
						
						$xmlGalley["galley_new_id"] = $articleGalley["galley_new_id"];
						
						$articleIdOk = false;
						$xmlGalleyOk = false;
						
						if (array_key_exists($xmlGalley["article_id"], $dataMapping["article_id"])) {
							$xmlGalley["article_new_id"] = $dataMapping["article_id"][$xmlGalley["article_id"]];
							$articleIdOk = true;
						}
						
						if ($articleIdOk) {
						
							echo "    inserting xml_galley #". $xmlGalley["xml_galley_id"] . " ........."; 
							
							$arr = array();
							$arr["data"] = $xmlGalley;
							$arr["params"] = [
								["name" => ":xmlGalley_galleyId", "attr" => "galley_new_id", "type" => PDO::PARAM_INT],
								["name" => ":xmlGalley_articleId", "attr" => "article_new_id", "type" => PDO::PARAM_INT],
								["name" => ":xmlGalley_label", "attr" => "label", "type" => PDO::PARAM_STR],
								["name" => ":xmlGalley_galleyType", "attr" => "galley_type", "type" => PDO::PARAM_STR],
								["name" => ":xmlGalley_views", "attr" => "views", "type" => PDO::PARAM_INT]
							];
							
							if (myExecute("insert", "article_xml_galley", $arr, $insertArticleXmlGalleySTMT, $errors)) { //from helperFunctions.php
								echo "OK\n";
								if (getNewId("article_xml_galley", $lastArticleXmlGalleysSTMT, $xmlGalley, $dataMapping, $errors)) { //from helperFunctions.php
									echo "    new id = " . $articleGalley["xml_galley_new_id"] . "\n\n";
									$xmlGalleyOk = true;
								}
							}
							else {
								echo "Failed\n";
							}
						}
						else {
							$error = ["xml_galley_id" => $xmlGalley["xml_galley_id"], "error" => "article_id ". $xmlGalley["article_id"] . " not found on dataMappings."];
							array_push($errors["article_xml_galley"]["insert"], $error);
						}
					}//end of the foreach xml_galley
					unset($xmlGalley);
					}//closing the if xml galley is empty
					}//closing the if xml galley exist
					
				//////////////// end of insert article_xml_galleys  //////////////////////
				
				/////////////// insert the article_html_galley_images /////////////////////////
			
					if (array_key_exists("html_galley_images", $articleGalley)) { if (!empty($articleGalley["html_galley_images"]) && $articleGalley["html_galley_images"] != null) {
				
					echo "\ninserting article_html_galley_images:\n";
					
					//insert each article_html_galley_image
					foreach($articleGalley["html_galley_images"] as &$galleyImage) {
						
						validateData("article_html_galley_image", $galleyImage); //from helperFunctions.php
						
						$galleyImage["galley_new_id"] = $articleGalley["galley_new_id"];
						
						$fileIdOk = false;
						
						if (array_key_exists($galleyImage["file_id"], $dataMapping["file_id"])) {
							$galleyImage["file_new_id"] = $dataMapping["file_id"][$galleyImage["file_id"]];
							$fileIdOk = true;
						}
						
						if ($fileIdOk) {
							echo "    inserting galley #". $galleyImage["galley_id"] . " file #" . $galleyImage["file_id"] . " ........."; 
							
							$arr = array();
							$arr["data"] = $setting;
							$arr["params"] = [
								["name" => ":galleyImage_galleyId", "attr" => "galley_new_id", "type" => PDO::PARAM_INT],
								["name" => ":galleyImage_fileId", "attr" => "file_new_id", "type" => PDO::PARAM_INT]
							];
							
							if (myExecute("insert", "article_html_galley_image", $arr, $insertArticleHtmlGalleyImageSTMT, $errors)) { //from helperFunctions.php
								echo "OK\n";
							}
							else {
								echo "Failed\n";
							}
						}
						else {
							$error = ["galley_id" => $galleyImage["galley_id"], "file_id" => $galleyImage["file_id"], 
							"error" => "file_id ". $galleyImage["file_id"] . " not found on dataMappings."];
							array_push($errors["article_html_galley_image"]["insert"], $error);
						}
						
					}
					unset($galleyImage);
					}//closing the if html galley images is empty
					}//closing the if article html galley image exists
					
				/////////////// end of insert article_html_galley_images  //////////////////////
				
				}//closing the if articleGalleyOk
				
			}//end of the foreach article galleys
			unset($articleGalley);
			}//closing the if article galley is empty
			}//closing the if article galley exists
			
			
			//////////// end of insert article galleys /////////////////
			
			
			///////////////  insert edit decisions  //////////////////
			if (array_key_exists("edit_decisions", $article)) { if (!empty($article["edit_decisions"]) && $article["edit_decisions"] != null) {
			echo "\ninserting edit_decisions:\n";
			foreach ($article["edit_decisions"] as &$editDecision) {
						
				$articleIdOk = false;
				$editorIdOk = false;
				$editDecisionOk = false;
				
				validateData("edit_decision", $editDecision); //from helperFunctions.php
				
				$error = ["edit_decision_id" => $editDecision["edit_decision_id"]];
				
				if (array_key_exists($editDecision["article_id"], $dataMapping["article_id"])) {
					$editDecision["article_new_id"] = $dataMapping["article_id"][$editDecision["article_id"]];
					$articleIdOk = true;
				}
				else {
					$error["article_id"] = ["article_id" => $editDecision["article_id"], "error" => "article_id not found on dataMappings."];
				}
				
				if (array_key_exists($editDecision["editor_id"], $dataMapping["editor_id"])) {
					$editDecision["editor_new_id"] = $dataMapping["editor_id"][$editDecision["editor_id"]];
					$editorIdOk = true;
				}
				else if (array_key_exists($editDecision["editor_id"], $dataMapping["user_id"])) {
					$dataMapping["editor_id"][$editDecision["editor_id"]] = $dataMapping["user_id"][$editDecision["editor_id"]];
					$editDecision["editor_new_id"] = $dataMapping["editor_id"][$editDecision["editor_id"]];
					$editorIdOk = true;
				}
				else {
					
					$editorIdOk = processUser($editDecision["editor"], array("type" => "edit_decision", "data" => $editDecision), $dataMapping, $errors, $insertedUsers, $userStatements);
					
					if ($editorIdOk) {
						
						$editDecision["editor_new_id"] = $editDecision["editor"]["user_new_id"];
						
						if (!array_key_exists($editDecision["editor_id"], $dataMapping["editor_id"])) {
							$dataMapping["editor_id"][$editDecision["editor_id"]] = $editDecision["editor_new_id"];
						}
						
					}
					else {
						$error["editor_id"] = array("editor_id" => $editDecision["editor_id"], "error" => "processUser returned false.");
					}
				}
				
				if ($editorIdOk && $articleIdOk) {
					echo "    inserting edit decision #". $editDecision["edit_decision_id"] . " ........."; 
					
					$arr = array();
					$arr["data"] = $editDecision;
					$arr["params"] = [
						["name" => ":editDecision_articleId", "attr" => "article_new_id", "type" => PDO::PARAM_INT],
						["name" => ":editDecision_round", "attr" => "round", "type" => PDO::PARAM_INT],
						["name" => ":editDecision_editorId", "attr" => "editor_new_id", "type" => PDO::PARAM_INT],
						["name" => ":editDecision_decision", "attr" => "decision", "type" => PDO::PARAM_INT],
						["name" => ":editDecision_dateDecided", "attr" => "date_decided", "type" => PDO::PARAM_STR]
					];
					
					if (myExecute("insert", "edit_decision", $arr, $insertEditDecisionSTMT, $errors)) { //from helperFunctions.php
						echo "OK\n";
						if (getNewId("edit_decision", $lastEditDecisionsSTMT, $editDecision, $dataMapping, $errors)) { //from helperFunctions.php
							echo "    new id = " . $editDecision["edit_decision_new_id"] . "\n\n";
							$editDecisionOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
				}
				else {
					array_push($errors["edit_decision"]["insert"], $error);
				}
			}//end of the foreach edit_decision
			unset($editDecision);
			}//closing the if edit_decision is empty
			}//closing the if edit_decision exist
			
			//////////////// end of insert edit decisions  /////////////
			
			
			////////////////// insert edit_assignments  //////////////////
			if (array_key_exists("edit_assignments", $article)) { if (!empty($article["edit_assignments"]) && $article["edit_assignments"] != null) {
			echo "\ninserting edit_assignments:\n";
			foreach ($article["edit_assignments"] as &$editAssignment) {
				
				validateData("edit_assignment", $editAssignment); //from helperFunctions.php
						
				$articleIdOk = false;
				$editorIdOk = false;
				$editAssignmentOk = false;
				
				$error = ["edit_id" => $editAssignment["edit_id"]];
				
				if (array_key_exists($editAssignment["article_id"], $dataMapping["article_id"])) {
					$editAssignment["article_new_id"] = $dataMapping["article_id"][$editAssignment["article_id"]];
					$articleIdOk = true;
				}
				else {
					$error["article_id"] = ["article_id" => $editAssignment["article_id"], "error" => "article_id not found on dataMappings."];
				}
				if (array_key_exists($editAssignment["editor_id"], $dataMapping["editor_id"])) {
					$editAssignment["editor_new_id"] = $dataMapping["editor_id"][$editAssignment["editor_id"]];
					$editorIdOk = true;
				}
				else if (array_key_exists($editAssignment["editor_id"], $dataMapping["user_id"])) {
					$dataMapping["editor_id"][$editAssignment["editor_id"]] = $dataMapping["user_id"][$editAssignment["editor_id"]];
					$editAssignment["editor_new_id"] = $dataMapping["editor_id"][$editAssignment["editor_id"]];
					$editorIdOk = true;
				}	
				else {
					
					$editorIdOk = processUser($editAssignment["editor"], array("type" => "edit_assignment", "data" => $editAssignment), $dataMapping, $errors, $insertedUsers, $userStatements);
					
					if ($editorIdOk) {
						
						$editAssignment["editor_new_id"] = $editAssignment["editor"]["user_new_id"];
						
						if (!array_key_exists($editAssignment["editor_id"], $dataMapping["editor_id"])) {
							$dataMapping["editor_id"][$editAssignment["editor_id"]] = $editAssignment["editor_new_id"];
						}
						
					}
					else {
						$error["editor_id"] = array("editor_id" => $editAssignment["editor_id"], "error" => "processUser returned false.");
					}
				}
				
				if ($editorIdOk && $articleIdOk) {
					echo "    inserting edit assignment #". $editAssignment["edit_id"] . " ........."; 
					
					$arr = array();
					$arr["data"] = $editAssignment;
					$arr["params"] = [
						["name" => ":editAssign_articleId", "attr" => "article_new_id", "type" => PDO::PARAM_INT],
						["name" => ":editAssign_canEdit", "attr" => "can_edit", "type" => PDO::PARAM_INT],
						["name" => ":editAssign_editorId", "attr" => "editor_new_id", "type" => PDO::PARAM_INT],
						["name" => ":editAssign_canReview", "attr" => "can_review", "type" => PDO::PARAM_INT],
						["name" => ":editAssign_dateAssigned", "attr" => "date_assigned", "type" => PDO::PARAM_STR],
						["name" => ":editAssign_dateNotified", "attr" => "date_notified", "type" => PDO::PARAM_STR],
						["name" => ":editAssign_dateUnderway", "attr" => "date_underway", "type" => PDO::PARAM_STR]
					];
					
					if (myExecute("insert", "edit_assignment", $arr, $insertEditAssignmentSTMT, $errors)) { //from helperFunctions.php
						echo "OK\n";
						if (getNewId("edit_assignment", $lastEditAssignmentsSTMT, $editAssignment, $dataMapping, $errors)) { //from helperFunctions.php
							echo "    new id = " . $editAssignment["edit_new_id"] . "\n\n";
							$editAssignmentOk = true;
						}
					}
					else {
						echo "Failed\n";
					}
				}
				else {
					array_push($errors["edit_assignment"]["insert"], $error);
				}
			}//end of the foreach edit_assignments
			unset($editAssignment);
			}//closing the if edit_assignments is empty
			}//closing the if edit_assignments exist
			
			/////////// end of insert edit assignments  //////////
			
			////////  INSERT THE KEYWORDS  /////////////////////////////
			
			if (array_key_exists("search_objects", $article)) { if (!empty($article["search_objects"])) {
			
			echo "\ninserting article_search_objects:\n";
				
			foreach ($article["search_objects"] as &$searchObj) {
				$searchObjOk = false;
				
				$searchObj["article_new_id"] = $article["article_new_id"];
				
				//validating the data 
				validateData("article_search_object", $searchObj); //from helperFunctions.php
				
				echo "    inserting article_search_object #". $searchObj["object_id"] . " ........."; 
					
				$arr = array();
				$arr["data"] = $searchObj;
				$arr["params"] = [
					["name" => ":searchObj_articleId", "attr" => "article_new_id", "type" => PDO::PARAM_INT],
					["name" => ":searchObj_type", "attr" => "type", "type" => PDO::PARAM_INT],
					["name" => ":searchObj_assocId", "attr" => "assoc_id", "type" => PDO::PARAM_INT]
				];
				
				if (myExecute("insert", "article_search_object", $arr, $insertArticleSearchObjectSTMT, $errors)) { //from helperFunctions.php
					echo "OK\n";
					if (getNewId("article_search_object", $lastArticleSearchObjectsSTMT, $searchObj, $dataMapping, $errors)) { //from helperFunctions.php
						echo "    new id = " . $searchObj["object_new_id"] . "\n\n";
						$searchObjOk = true;
					}
				}
				else {
					echo "Failed\n";
				}
				
				if ($searchObjOk) {
					
					if (array_key_exists("keywords", $searchObj)) { if (!empty($searchObj["keywords"])) {
					foreach ($searchObj["keywords"] as &$searchObjKeyword) {
						
						$objectIdOk = false;
						$keywordIdOk = false;
						
						$error = ["object_id" => $searchObj["object_id"]];
						
						if (array_key_exists("keyword_list", $searchObjKeyword)) { if (!empty($searchObjKeyword["keyword_list"])) {
							$keyword = &$searchObjKeyword["keyword_list"];
							//check if the keyword is already in dataMapping
							if (array_key_exists($keyword["keyword_id"], $dataMapping["keyword_id"])) {
								$keyword["keyword_new_id"] = $dataMapping["keyword_id"][$keyword["keyword_id"]];
								//$keywordIdOk = true;
							}
							else {
								//see if the keyword already exists in the database
								$getKeywordSTMT->bindParam(":getKeyword_keywordText", $keyword["keyword_text"], PDO::PARAM_STR);
								if ($getKeywordSTMT->execute()) {
									if ($kw = $getKeywordSTMT->fetch(PDO::FETCH_ASSOC)) {
										$keyword["keyword_new_id"] = $kw["keyword_id"];
									}
									else {
										//the keyword doesn't exist in the database, must insert it then
										echo "\n        inserting keyword '". $keyword["keyword_text"] . "' ........."; 
										
										$arr = array();
										$arr["data"] = $keyword;
										$arr["params"] = [
											["name" => ":keywordList_keywordText", "attr" => "keyword_text", "type" => PDO::PARAM_STR]
										];
										
										if (myExecute("insert", "article_search_keyword_list", $arr, $insertArticleSearchKeywordListSTMT, $errors)) { //from helperFunctions.php
											echo "OK\n";
											if (getNewId("article_search_keyword_list", $lastArticleSearchKeywordListsSTMT, $keyword, $dataMapping, $errors)) { //from helperFunctions.php
												echo "        new id = " . $keyword["keyword_new_id"] . "\n\n";
												//$keywordIdOk = true;
											}
										}
										else {
											echo "Failed\n";
										}
									}
								}
								else {
									//DIDN'T EXECUTE THE GET KEYWORD STATEMENT
									
									$error["keyword"] = ["keyword_id" => $keyword["keyword_id"], "error" => $getKeywordSTMT->errorInfo()];
								}
							}//end of the else keyword in dataMapping
							
							//NOW THE KEYWORD HAS A NEW ID SO WE'RE READY TO INSERT THE article_search_object_keyword
							
							if (array_key_exists("object_new_id", $searchObj)) {
								$searchObjKeyword["object_new_id"] = $searchObj["object_new_id"];
								$objectIdOk = true;
							}
							else {
								$error["object_new_id"] = ["error" => "search_object has no object_new_id"];
							}
							
							if (array_key_exists("keyword_new_id", $keyword)) {
								$searchObjKeyword["keyword_new_id"] = $keyword["keyword_new_id"];
								$keywordIdOk = true;
							}
							
							unset($keyword);
								
							if ($objectIdOk && $keywordIdOk) {
								echo "        inserting object_id #" . $searchObjKeyword["object_new_id"] . ", keyword_id #" . 
								$searchObjKeyword["keyword_new_id"] . " at position " . $searchObjKeyword["pos"] . ".................";
									
								$arr = array();
								$arr["data"] = $searchObjKeyword;
								$arr["params"] = [
									["name" => ":objectKeyword_objectId", "attr" => "object_new_id", "type" => PDO::PARAM_INT],
									["name" => ":objectKeyword_keywordId", "attr" => "keyword_new_id", "type" => PDO::PARAM_INT],
									["name" => ":objectKeyword_pos", "attr" => "pos", "type" => PDO::PARAM_INT]
								];
								
								if (myExecute("insert", "article_search_object_keyword", $arr, $insertArticleSearchObjectKeywordSTMT, $errors)) {  //from helperFunctions.php
									echo "OK\n";
								}
								else {
									echo "Failed\n";
								}
							}
							else {
								array_push($errors["article_search_object_keyword"]["insert"], $error);
							}
						
						}//closing the if keyword_list not empty
						}//closing the if keyword_list exists
						
					}
					//end of foreach keyword
					unset($searchObjKeyword);
					}// closing the if keywords is not empty
					}//closing the if keywords exists
					
				}//closing the if searchObjOk
				
			}
			//end of foreach search_objects
			unset($searchObj);
			}//closing the if search_objects not empty
			}//closing the if search_objects exist
			
			//////  END OF INSERT THE KEYWORDS  ////////////////////////
			
			
			//////  INSERT THE REVIEWS  ////////////////////////////////
			
			////////////// insert the review_assignments /////////////////
			if (array_key_exists("review_assignments", $article)) { if (!empty($article["review_assignments"]) && $article["review_assignments"] != null) { 
			//echo "\nInsert the review assignments:\n";
			
			foreach ($article["review_assignments"] as &$revAssign) {
				
				$submissionIdOk = false;
				$reviewerIdOk = false;
				$reviewFormIdOk = false;
				$reviewerFileIdOk = false;
				
				$reviewIdOk = false; // variable to control if the review_form_response can be inserted
				
				validateData("review_assignment", $revAssign); //from helperFunctions.php
				
				$error = ["review_id" => $revAssign["review_id"]];
				
				$revAssign["submission_new_id"] = $article["article_new_id"];
				
				if ($revAssign["reviewer_file_id"] === null) {
					$reviewerFileIdOk = true;
				}
				else {
					if (array_key_exists($revAssign["reviewer_file_id"], $dataMapping["file_id"])) {
						$revAssign["reviewer_file_new_id"] = $dataMapping["file_id"][$revAssign["reviewer_file_id"]];
						$reviewerFileIdOk = true;
					}
					else {
						$error["reviewer_file_id"] = ["reviewer_file_id" => $revAssign["reviewer_file_id"], "error" => "reviewer_file_id not in dataMapping."];
						//echo "\nreviewer_file_id not ok\n";
					}
				}
				
				
				if (array_key_exists($revAssign["reviewer_id"], $dataMapping["reviewer_id"])) {
					$revAssign["reviewer_new_id"] = $dataMapping["reviewer_id"][$revAssign["reviewer_id"]];
					$reviewerIdOk = true;
				}
				else if (array_key_exists($revAssign["reviewer_id"], $dataMapping["user_id"])) {
					$dataMapping["reviewer_id"][$revAssign["reviewer_id"]] = $dataMapping["user_id"][$revAssign["reviewer_id"]];
					$revAssign["reviewer_new_id"] = $dataMapping["reviewer_id"][$revAssign["reviewer_id"]];
					$reviewerIdOk = true;
				}
				else {
					$reviewerIdOk = processUser($revAssign["reviewer"], array("type" => "review_assignment", "data" => $revAssign), $dataMapping, $errors, $insertedUsers, $userStatements);
					
					if ($reviewerIdOk) {
						$revAssign["reviewer_new_id"] = $revAssign["reviewer"]["user_new_id"];
						if (!array_key_exists($revAssign["reviewer_id"], $dataMapping["reviewer_id"])) {
							$dataMapping["reviewer_id"][$revAssign["reviewer_id"]] = $revAssign["reviewer_new_id"];
						}
					}
					else {
						$error["reviewer_id"] = array("reviewer_id" => $revAssign["reviewer_id"], "error" => "processUser returned false");
						//echo "\nreviewer_id not ok\n";
					}
				}
				
				if ($revAssign["review_form_id"] === null) {
					$revAssign["review_form_new_id"] = null;
					$reviewFormIdOk = true;
				}
				else if (array_key_exists($revAssign["review_form_id"], $dataMapping["review_form_id"])) {
					$revAssign["review_form_new_id"] = $dataMapping["review_form_id"][$revAssign["review_form_id"]];
					$reviewFormIdOk = true;
				}
				else {
					$error["review_form_id"] = array("review_form_id" => $revAssign["review_form_id"], "error" => "review_form_id not in dataMapping.");
					//echo "\nreview_form_id not ok\n";
				}
				
				if ($reviewerIdOk && $reviewFormIdOk && $reviewerFileIdOk) {
					
					$arr = array();
					$arr["data"] = $revAssign;
					$arr["params"] = [
						["name" => ":revAssign_submissionId", "attr" => "submission_new_id", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_reviewerId", "attr" => "reviewer_new_id", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_competingInterests", "attr" => "competing_interests", "type" => PDO::PARAM_STR],
						["name" => ":revAssign_regretMessage", "attr" => "regret_message", "type" => PDO::PARAM_STR],
						["name" => ":revAssign_recommendation", "attr" => "recommendation", "type" => PDO::PARAM_STR],
						["name" => ":revAssign_dateAssigned", "attr" => "date_assigned", "type" => PDO::PARAM_STR],
						["name" => ":revAssign_dateNotified", "attr" => "date_notified", "type" => PDO::PARAM_STR],
						["name" => ":revAssign_dateConfirmed", "attr" => "date_confirmed", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_dateCompleted", "attr" => "date_completed", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_dateAcknowledged", "attr" => "date_acknowledged", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_dateDue", "attr" => "date_due", "type" => PDO::PARAM_STR],
						["name" => ":revAssign_lastModified", "attr" => "last_modified", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_reminderAuto", "attr" => "reminder_was_automatic", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_declined", "attr" => "declined", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_replaced", "attr" => "replaced", "type" => PDO::PARAM_STR],
						["name" => ":revAssign_cancelled", "attr" => "cancelled", "type" => PDO::PARAM_STR],
						["name" => ":revAssign_reviewerFileId", "attr" => "reviewer_file_new_id", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_dateRated", "attr" => "date_rated", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_dateReminded", "attr" => "date_reminded", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_quality", "attr" => "quality", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_reviewRoundId", "attr" => "review_round_id", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_stageId", "attr" => "stage_id", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_reviewMethod", "attr" => "review_method", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_round", "attr" => "round", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_step", "attr" => "step", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_reviewFormId", "attr" => "review_form_new_id", "type" => PDO::PARAM_INT],
						["name" => ":revAssign_unconsidered", "attr" => "unconsidered", "type" => PDO::PARAM_INT]
					];
					
					
					echo "\ninserting review_assignment #" . $revAssign["review_id"] . " ......... "; 
					
					if (myExecute("insert", "review_assignment", $arr, $insertReviewAssignmentSTMT, $errors)) { //from helperFunctions.php
						echo "OK\n";
						
						if (getNewId("review_assignment", $lastReviewAssignmentsSTMT, $revAssign, $dataMapping, $errors)) { //from helperFunctions.php
							echo "review new id = " . $revAssign["review_new_id"] . "\n";
							$reviewIdOk = true;
						}
						
					}
					else {
						echo "Failed\n";
					}
					
				}
				else {
					//some of the ids are not ok
					array_push($errors["review_assignment"]["insert"], $error); //put the error in the review_assignments insert part
				}
				
				if ($reviewIdOk) {
					//insert the review_form_responses //////////////////////
					if (array_key_exists("review_form_responses", $revAssign)) { if (!empty($revAssign["review_form_responses"]) && $revAssign["review_form_responses"] != null) {
						
					echo "    inserting review_form_responses:\n";
						
					foreach ($revAssign["review_form_responses"] as &$revFormResponse) {
						
						$revFormResponse["review_new_id"] = $revAssign["review_new_id"];
						
						$reviewFormElementIdOk = false;
						
						if (array_key_exists($revFormResponse["review_form_element_id"], $dataMapping["review_form_element_id"])) {
							$revFormResponse["review_form_element_new_id"] = $dataMapping["review_form_element_id"][$revFormResponse["review_form_element_id"]];
							$reviewFormElementIdOk = true;
						}
						else {
							//put it in the errors array
						}
						
						if ($reviewFormElementIdOk) {
							
							echo "        inserting review_form_response with review_form_element_id #" . $revFormResponse["review_form_element_new_id"] . " and review_id #" . 
							$revFormResponse["review_new_id"] . " .................";
							
							validateData("review_form_response", $revFormResponse); //from helperFunctions.php
							
							$arr = array();
							$arr["data"] = $revFormResponse;
							$arr["params"] = [
								["name" => ":response_reviewFormElementId", "attr" => "review_form_element_new_id", "type" => PDO::PARAM_INT],
								["name" => ":reponse_reviewId", "attr" => "review_new_id", "type" => PDO::PARAM_INT],
								["name" => ":response_responseType", "attr" => "response_type", "type" => PDO::PARAM_STR],
								["name" => ":response_reponseValue", "attr" => "response_value", "type" => PDO::PARAM_STR]
							];
							
							if (myExecute("insert", "review_form_response", $arr, $insertReviewFormResponseSTMT, $errors)) {  //from helperFunctions.php
								echo "OK\n";
							}
							else {
								echo "Failed\n";
							}
							
						}
						
					}//end of the foreach review_form_response
					unset($revFormResponse);
					}//closing the if review_form_responses is empty
					}//closing the if review_form_responses exist
					
					//////// end of insert review_form_responses ///////////////
				}//closing the if reviewIdOk
				
			}//end of the foreach review_assignments
			unset($revAssign);
			}// closing the if review_assignments is empty
			}//closing the if review_assignments exists
				
			//////////////// end of insert the review_assignments ///////////////
			
			
			//////////////// insert the review_rounds //////////////////////
			
			if (array_key_exists("review_rounds", $article)) { if (!empty($article["review_rounds"]) && $article["review_rounds"] != null) {
			
			echo "inserting the review_rounds:\n";
			foreach ($article["review_rounds"] as &$revRound) {
				
				$revRound["submission_new_id"] = $article["article_new_id"];
				
				validateData("review_round", $revRound); //from helperFunctions.php
				
				echo "    inserting review_round #" . $revRound["review_round_id"] . " .................";
				
				$arr = array();
				$arr["data"] = $revRound;
				$arr["params"] = [
					["name" => ":revRound_submissionId", "attr" => "submission_new_id", "type" => PDO::PARAM_INT],
					["name" => ":revRound_stageId", "attr" => "stage_id", "type" => PDO::PARAM_INT],
					["name" => ":revRound_round", "attr" => "round", "type" => PDO::PARAM_INT],
					["name" => ":revRound_reviewRevision", "attr" => "review_revision", "type" => PDO::PARAM_INT],
					["name" => ":revRound_status", "attr" => "status", "type" => PDO::PARAM_INT]
				];
				
				if (myExecute("insert", "review_round", $arr, $insertReviewRoundSTMT, $errors)) {  //from helperFunctions.php
					echo "OK\n";
					if (getNewId("review_round", $lastReviewRoundsSTMT, $revRound, $dataMapping, $errors)) { //from helperFunctions.php
						echo "    review round new id = " . $revRound["review_round_new_id"] . "\n";
						//$reviewIdOk = true;
					}
				}
				else {
					echo "Failed\n";
				}
				
				
				
			}//end of the foreach review_round
			unset($revRound);
			}//closing the if review_rounds is empty
			}//closing the if review_rounds exist
			
			///////////////// end of insert the review_rounds  //////////////
			
			
			/////  END OF INSERT THE REVIEWS  //////////////////////////
			
		}//end of the if articleOk
		
		else {
			//article not ok
		}
		//exit();
	}//end of foreach articles
	unset($article);
	
	
	//////////////////////  END OF THE INSERT STAGE  ////////////////////////////////////////////////////////////////////////////////
	
	if ($insertedArticles > 0) {
	///////////////////// BEGINNING OF THE UPDATE STAGE  ///////////////////////////////////////////////////////////////////////////
	
	$updateArticleSTMT = $conn->prepare("UPDATE articles SET submission_file_id = :updateArticle_submissionFileId, revised_file_id = :updateArticle_revisedFileId, 
		review_file_id = :updateArticle_reviewFileId, editor_file_id = :updateArticle_editorFileId WHERE article_id = :updateArticle_articleId");
	
	$updateArticleFileSTMT = $conn->prepare("UPDATE article_files SET source_file_id = :updateFile_sourceFileId, file_name = :updateFile_fileName, 
		original_file_name = :updateFile_originalFileName WHERE file_id = :updateFile_fileId AND revision = :updateFile_revision");
	
	$updateRevAssignSTMT = $conn->prepare("UPDATE review_assignments SET reviewer_file_id = :updateRevAssign_reviewerFileId WHERE review_id = :updateRevAssign_reviewId");
	
	//loop through every article to update data to the correct ones in the database
	foreach($unpubArticles as &$article) {
		
		//updatting article
		$article["submission_file_new_id"] = null;
		$article["revised_file_new_id"] = null;
		$article["review_file_new_id"] = null;
		$article["editor_file_new_id"] = null;
		
		$updateArticle = true;
		
		if ($article["submission_file_id"] !== null && $article["submission_file_id"] !== "" && array_key_exists($article["submission_file_id"], $dataMapping["file_id"])) {
			$article["submission_file_new_id"] = $dataMapping["file_id"][$article["submission_file_id"]];
		}
		if ($article["revised_file_id"] !== null && $article["revised_file_id"] !== "" && array_key_exists($article["revised_file_id"], $dataMapping["file_id"])) {
			$article["revised_file_new_id"] = $dataMapping["file_id"][$article["revised_file_id"]];
		}
		if ($article["review_file_id"] !== null && $article["review_file_id"] !== "" && array_key_exists($article["review_file_id"], $dataMapping["file_id"])) {
			$article["review_file_new_id"] = $dataMapping["file_id"][$article["review_file_id"]];
		}
		if ($article["editor_file_id"] !== null && $article["editor_file_id"] !== "" && array_key_exists($article["editor_file_id"], $dataMapping["file_id"])) {
			$article["editor_file_new_id"] = $dataMapping["file_id"][$article["editor_file_id"]];
		}
		
		if ($article["submission_file_new_id"] === null && $article["revised_file_new_id"] === null && $article["review_file_new_id"] === null && $article["editor_file_new_id"] === null) {
			$updateArticle = false;
		}
		
		if (!array_key_exists("article_new_id", $article)) {
			$updateArticle = false;
		}
		
		if ($updateArticle) {
			$arr = array();
			$arr["data"] = $article;
			$arr["params"] = [
				["name" => ":updateArticle_submissionFileId", "attr" => "submission_file_new_id", "type" => PDO::PARAM_INT],
				["name" => ":updateArticle_revisedFileId", "attr" => "revised_file_new_id", "type" => PDO::PARAM_INT],
				["name" => ":updateArticle_reviewFileId", "attr" => "review_file_new_id", "type" => PDO::PARAM_INT],
				["name" => ":updateArticle_editorFileId", "attr" => "editor_file_new_id", "type" => PDO::PARAM_INT],
				["name" => ":updateArticle_articleId", "attr" => "article_new_id", "type" => PDO::PARAM_INT]
			];
			
			echo "\nupdating article #" . $article["article_new_id"] . " ......... "; 
			
			if (myExecute("update", "article", $arr, $updateArticleSTMT, $errors)) { //from helperFunctions.php
				echo "OK\n";
			}
			else {
				echo "Failed\n";
			}
		}// closing the if updateArticle
		
		//updating article_files
		if (array_key_exists("files", $article)) { if (!empty($article["files"]) && $article["files"] != null) {
		foreach ($article["files"] as &$articleFile) {
			
			$articleFile["source_file_new_id"] = null;
			$articleFile["file_new_name"] = null;
			$articleFile["original_file_new_name"] = null;
			$updateFile = true;
			
			if ($articleFile["source_file_id"] !== null && $articleFile["source_file_id"] !== "" && array_key_exists($articleFile["source_file_id"], $dataMapping["file_id"])) {
				$articleFile["source_file_new_id"] = $dataMapping["file_id"][$articleFile["source_file_id"]];
			}
			if (isStandardName($articleFile["file_name"])) {
				$msg = "";
				$articleFile["file_new_name"] = setNewName($articleFile["file_name"], $dataMapping, $msg);
				if ($articleFile["file_new_name"] !== null) {
					$dataMapping["file_name"][$articleFile["file_name"]] = $articleFile["file_new_name"];
				}
				else {
					$updateFile = false;
					if (array_key_exists($articleFile["article_new_id"], $errors["article_file"]["update"])) {
						$errors["article_file"]["update"][$articleFile["article_new_id"]]["file_name"] = $msg;
					}
					else {
						$errors["article_file"]["update"][$articleFile["article_new_id"]] = array();
						$errors["article_file"]["update"][$articleFile["article_new_id"]]["file_name"] = $msg;
					}
				}
			}
			else {
				$updateFile = false;
				$articleFile["file_new_name"] = $articleFile["file_name"];
				if (array_key_exists($articleFile["article_new_id"], $errors["article_file"]["update"])) {
					$errors["article_file"]["update"][$articleFile["article_new_id"]]["file_name"] = "file_name " . $articleFile["file_name"] . " is not standard.";
				}
				else {
					$errors["article_file"]["update"][$articleFile["article_new_id"]] = array();
					$errors["article_file"]["update"][$articleFile["article_new_id"]]["file_name"] = "file_name " . $articleFile["file_name"] . " is not standard.";
				}
			}
			
			if (isStandardName($articleFile["original_file_name"])) {
				$msg = "";
				$articleFile["original_file_new_name"] = setNewName($articleFile["original_file_name"], $dataMapping, $msg);
				if ($articleFile["original_file_new_name"] !== null) {
					$dataMapping["original_file_name"][$articleFile["original_file_name"]] = $articleFile["original_file_new_name"];
				}
				else {
					$updateFile = false;
					if (array_key_exists($articleFile["article_new_id"], $errors["article_file"]["update"])) {
						$errors["article_file"]["update"][$articleFile["article_new_id"]]["original_file_name"] = $msg;
					}
					else {
						$errors["article_file"]["update"][$articleFile["article_new_id"]] = array();
						$errors["article_file"]["update"][$articleFile["article_new_id"]]["original_file_name"] = $msg;
					}
				}
			}
			
			if (!array_key_exists("file_new_id", $articleFile)) {
				$updateFile = false;
			}
			
			if ($updateFile) {
				$arr = array();
				$arr["data"] = $articleFile;
				$arr["params"] = [
					["name" => ":updateFile_sourceFileId", "attr" => "source_file_new_id", "type" => PDO::PARAM_INT],
					["name" => ":updateFile_fileName", "attr" => "file_new_name", "type" => PDO::PARAM_STR],
					["name" => ":updateFile_originalFileName", "attr" => "original_file_new_name", "type" => PDO::PARAM_STR],
					["name" => ":updateFile_fileId", "attr" => "file_new_id", "type" => PDO::PARAM_INT],
					["name" => ":updateFile_revision", "attr" => "revision", "type" => PDO::PARAM_INT]
				];
				
				echo "\nupdating file #" . $articleFile["file_new_id"] . " ......... "; 
				
				if (myExecute("update", "article_file", $arr, $updateArticleFileSTMT, $errors)) { //from helperFunctions.php
					echo "OK\n";
				}
				else {
					echo "Failed\n";
				}
			}//closing the if updateFile
		}//end of foreach article file
		unset($articleFile);
		}//closing the if article_files is empty
		}//closing the if article_files exist
		
		//update reviewer file id
		if (array_key_exists("review_assignments", $article)) { if (!empty($article["review_assignments"]) && $article["review_assignments"] != null) {
		foreach($article["review_assignments"] as &$revAssign) { 
			//updatting article
			$revAssignOk = true;
			
			$revAssign["reviewer_file_new_id"] = null;
			
			if ($revAssign["reviewer_file_id"] !== null && $revAssign["reviewer_file_id"] !== "" && array_key_exists($revAssign["reviewer_file_id"], $dataMapping["file_id"])) {
				$revAssign["reviewer_file_new_id"] = $dataMapping["file_id"][$revAssign["reviewer_file_id"]];
			}
			else {
				$revAssignOk = false;
			}
			
			if (!array_key_exists("review_new_id", $revAssign)) {
				$revAssignOk = false;
			}
			
			if ($revAssignOk) {
				$arr = array();
				$arr["data"] = $revAssign;
				$arr["params"] = [
					["name" => ":updateRevAssign_reviewerFileId", "attr" => "reviewer_file_new_id", "type" => PDO::PARAM_INT],
					["name" => ":updateRevAssign_reviewId", "attr" => "review_new_id", "type" => PDO::PARAM_INT]
				];
				
				echo "\nupdating review_assignment #" . $revAssign["review_new_id"] . " ......... "; 
				
				if (myExecute("update", "review_assignment", $arr, $updateRevAssignSTMT, $errors)) { //from helperFunctions.php
					echo "OK\n";
				}
				else {
					echo "Failed\n";
				}
			}// closing the if revAssignOk
			
		}
		//end of the foreach review_assignment
		unset($revAssign);
		}//closing the if review_assignments is empty
		}//closing the if review_assignments exist
	}//end of the foreach unpubArticles
	unset($article);
	
	/*$updateFields = ["author_id", "comment_id", "editor_id", "edit_id", "edit_decision_id", "galley_id", "supp_id", "xml_galley_id",
	"article_id", "section_id", "journal_id", "user_id", "submission_file_id", "revised_file_id", "review_file_id", "editor_file_id",
	"file_id", "source_file_id", "style_file_id", "file_name", "original_file_name", "reviewer_file_id"];
	
	echo "\n\n-------------- updating the xml -------------------------\n\n";
	
	foreach ($updateFields as $field) {
		$tags = $unpublished_articles->getElementsByTagName($field);
		
		echo "    updating $field............";
		foreach ($tags as $tag) {
			
			$value = null;
			
			if (substr($field, -7) === "file_id") {
				if (array_key_exists($tag->nodeValue, $dataMapping["file_id"])) {
					$value = $dataMapping["file_id"][$tag->nodeValue];
				}
			}
			else {
				if (array_key_exists($tag->nodeValue, $dataMapping[$field])) {
					$value = $dataMapping[$field][$tag->nodeValue];
				}
			}
			
			if ($value !== null) {
				$new_tag = $xml->createElement(newIdField($field), $value); // xml has to be xml and not unpublished_articles
				$tag->parentNode->insertBefore($new_tag, $tag);
			}
		}
		
		echo "OK\n";
	}*/
	
	
	////////////////////  END OF THE UPDATE STAGE  /////////////////////////////////////////////////////////////////////////////////
	
	}// closing the if insertedArticles > 0
	
	$returnData = array();
	$returnData["errors"] = $errors;
	//$returnData["dataMapping"]  = $dataMapping;
	$returnData["insertedUsers"] = $insertedUsers;
	$returnData["numInsertedRecords"] = $insertedArticles;
	
	return $returnData;
}
//////// END OF insertUnpublishedArticles  ////////////////////////////////////////////////////////


// #07)

function insertAnnouncements(&$xml, $conn, &$dataMapping, $journalNewId, $args = null) {
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists("limit", $args)) {
			$limit = $args["limit"];
		}
	}
	
	if (!array_key_exists("announcement_id", $dataMapping)) {
		$dataMapping["announcement_id"] = array();
	}
	
	
	///////  THE STATEMENTS  ////////////////////////////////////////////////////////////
	
	//$getAnnouncementsSTMT = $conn->prepare("SELECT * FROM announcements WHERE journal_id = :getAnnouncements_journalId");
	//$getAnnouncementSettingsSTMT = $conn->prepare("SELECT * FROM announcement_settings WHERE announcement_id = :getAnnouncementSettings_announcementId");
	
	// announcements
	$insertAnnouncementSTMT = $conn->prepare(
		"INSERT INTO announcements (assoc_id, type_id, date_expire, date_posted, assoc_type) 
		 VALUES (:assocId, :typeId, :dateExpire, :datePosted, :assocType)"
	);
	
	$lastAnnouncementsSTMT = $conn->prepare("SELECT * FROM announcements ORDER BY announcement_id DESC LIMIT $limit");
	
	$insertAnnouncementSettingsSTMT = $conn->prepare(
		"INSERT INTO announcement_settings (announcement_id, locale, setting_name, setting_value, setting_type) 
		 VALUES (:announcementId, :locale, :settingName, :settingValue, :settingType)"
	);
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	$announcements_node = null;
	
	if ($xml->nodeName === "announcements") {
		$announcements_node = $xml;
	}
	else {
		$announcements_node = $xml->getElementsByTagName("announcements")->item(0);
	}
	
	$errors = array(
		"announcement" => array(
			"insert" => array(), 
			"update" => array()
		),
		"announcement_settings" => array(
			"insert" => array(), 
			"update" => array()
		)
	);
	
	$announcements = xmlToArray($announcements_node, true); //from helperFunctions.php
	
	/*echo "\nDataMapping before: \n";
	print_r($dataMapping);*/
	$numInsertedAnnouncements = 0;
	
	foreach ($announcements as &$ann) {
		
		if (array_key_exists($ann["announcement_id"], $dataMapping["announcement_id"])) {
			echo "\nannouncement #" . $ann["announcement_id"] . " was already imported.\n";
			continue; // go to the next announcement
		}
		
		$announcementOk = false;
		$ann["assoc_new_id"] = $journalNewId;
		
		validateData("announcement", $ann); //from helperFunctions.php
		
		$arr = array();
		$arr["data"] = $ann;
		$arr["params"] = array(
			array("name" => ":assocId", "attr" => "assoc_new_id", "type" => PDO::PARAM_INT),
			array("name" => ":typeId", "attr" => "type_id", "type" => PDO::PARAM_INT),
			array("name" => ":dateExpire", "attr" => "date_expire", "type" => PDO::PARAM_STR),
			array("name" => ":datePosted", "attr" => "date_posted", "type" => PDO::PARAM_STR),
			array("name" => ":assocType", "attr" => "assoc_type", "type" => PDO::PARAM_INT)
		);
		
		echo "    inserting announcement #" . $ann["announcement_id"]. "............ ";
		
		if (myExecute("insert", "announcement", $arr, $insertAnnouncementSTMT, $errors)) { //from helperFunctions.php
			echo "OK\n";
			if (getNewId("announcement", $lastAnnouncementsSTMT, $ann, $dataMapping, $errors)) { //from helperFunctions.php
				echo "    new id = " . $ann["announcement_new_id"] . "\n\n";
				$announcementOk = true;
				$numInsertedAnnouncements++;
			}
		}
		else {
			echo "Failed\n";
		}
		
		if ($announcementOk) {
			//insert the announcement settings
			if (array_key_exists("settings", $ann)) { if (!empty($ann["settings"]) && $ann["settings"] != null) {
			
				foreach ($ann["settings"] as $setting) {
					validateData("announcement_settings", $setting); 
					
					$setting["announcement_new_id"] = $ann["announcement_new_id"];
					echo "    inserting ". $setting["setting_name"] . " with locale " . $setting["locale"] . " .........";
					
					$arr = array();
					$arr["data"] = $setting;
					$arr["params"] = array(
						array("name" => ":announcementId", "attr" => "announcement_new_id", "type" => PDO::PARAM_INT),
						array("name" => ":locale", "attr" => "locale", "type" => PDO::PARAM_STR),
						array("name" => ":settingName", "attr" => "setting_name", "type" => PDO::PARAM_STR),
						array("name" => ":settingValue", "attr" => "setting_value", "type" => PDO::PARAM_STR),
						array("name" => ":settingType", "attr" => "setting_type", "type" => PDO::PARAM_STR)
					);
					
					if (myExecute("insert", "announcement_settings", $arr, $insertAnnouncementSettingsSTMT, $errors)) { //from helperFunctions.php
						echo "OK\n";
					}
					else {
						echo "Failed\n";
					}
				}//end of the foreach setting
				
			}}// end of the 2 ifs to see if announcement_settings exist
			
			///////////// end of insert announcement settings  //////////////////////
			
		}//end of the if announcementOk
	}//end of the foreach announcement	
	unset($ann);
	
	return array("errors" => $errors, "numInsertedRecords" => $numInsertedAnnouncements);
	
}
/////////////////// end of innsertAnnouncements  ////////////////////////////////

// #08) 

function insertEmailTemplates() {
	echo "\n\nTHE FUNCTION insertEmailTemplates DOES NOT DO ANYTHING\n\n";
}
/////////////////// end of insertEmailTemplates  /////////////////////////////////



// #09)

function insertGroups(&$xml, $conn, &$dataMapping, $journalNewId, $args = null) {
	$limit = 10;
	
	if (is_array($args)) {
		if (array_key_exists("limit", $args)) {
			$limit = $args["limit"];
		}
	}
	
	if (!array_key_exists("group_id", $dataMapping)) {
		$dataMapping["group_id"] = array();
	}
	
	
	///////  THE STATEMENTS  ////////////////////////////////////////////////////////////
	
	// user statements ////////////////
	
	$userSTMT = $conn->prepare("SELECT * FROM users WHERE email = :user_email");
	
	$checkUsernameSTMT = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = :checkUsername");
	
	$insertUserSTMT = $conn->prepare("INSERT INTO users (username, password, salutation, first_name, middle_name, last_name, gender, initials, email, url, phone, fax, mailing_address,
country, locales, date_last_email, date_registered, date_validated, date_last_login, must_change_password, auth_id, disabled, disabled_reason, auth_str, suffix, billing_address, 
inline_help) VALUES (:insertUser_username, :insertUser_password, :insertUser_salutation, :insertUser_firstName, :insertUser_middleName, :insertUser_lastName, :insertUser_gender, 
:insertUser_initials, :insertUser_email, :insertUser_url, :insertUser_phone, :insertUser_fax, :insertUser_mailingAddress, :insertUser_country, :insertUser_locales, 
:insertUser_dateLastEmail, :insertUser_dateRegistered, :insertUser_dateValidated, :insertUser_dateLastLogin, :insertUser_mustChangePassword, :insertUser_authId, :insertUser_disabled, 
:insertUser_disabledReason, :insertUser_authStr, :insertUser_suffix, :insertUser_billingAddress, :insertUser_inlineHelp)");
	
	$lastUsersSTMT = $conn->prepare("SELECT * FROM users ORDER BY user_id DESC LIMIT $limit");
	
	$userStatements = array("userSTMT" => &$userSTMT, "checkUsernameSTMT" => &$checkUsernameSTMT, "insertUserSTMT" => &$insertUserSTMT, "lastUsersSTMT" => &$lastUsersSTMT);
	
	///////////////////////////////////
	
	//$getGroupsSTMT = $conn->prepare("SELECT * FROM groups WHERE journal_id = :getGroups_journalId");
	//$getGroupSettingsSTMT = $conn->prepare("SELECT * FROM group_settings WHERE group_id = :getGroupSettings_groupId");
	
	// groups
	$insertGroupSTMT = $conn->prepare(
		"INSERT INTO groups (context, assoc_id, assoc_type, about_displayed, seq, publish_email) 
		 VALUES (:context, :assocId, :assocType, :groups_aboutDisplayed, :groups_seq, :publishEmail)"
	);
	
	$lastGroupsSTMT = $conn->prepare("SELECT * FROM groups ORDER BY group_id DESC LIMIT $limit");
	
	$insertGroupSettingsSTMT = $conn->prepare(
		"INSERT INTO group_settings (group_id, locale, setting_name, setting_value, setting_type) 
		 VALUES (:settings_groupId, :locale, :settingName, :settingValue, :settingType)"
	);
	
	$insertGroupMembershipSTMT = $conn->prepare(
		"INSERT INTO group_memberships (group_id, user_id, about_displayed, seq) 
		 VALUES (:memberships_groupId, :userId, :memberships_aboutDisplayed, :memberships_seq)"
	);
	
	//////////////////////////////////////////////////////////////////////////////////////
	
	$groups_node = null;
	
	if ($xml->nodeName === "groups") {
		$groups_node = $xml;
	}
	else {
		$groups_node = $xml->getElementsByTagName("groups")->item(0);
	}
	
	$errors = array(
		"group" => array(
			"insert" => array(), 
			"update" => array()
		),
		"group_settings" => array(
			"insert" => array(), 
			"update" => array()
		),
		"group_membership" => array(
			"insert" => array(), 
			"update" => array()
		),
		"user" => array(
			"insert" => array(),
			"update" => array()
		)
	);
	
	$groups = xmlToArray($groups_node, true); //from helperFunctions.php
	
	$numInsertedGroups = 0;
	$insertedUsers = array();
	
	foreach ($groups as &$grp) {
		
		if (array_key_exists($grp["group_id"], $dataMapping["group_id"])) {
			echo "\ngroup #" . $grp["group_id"] . " was already imported.\n";
			continue; // go to the next group
		}
		
		$groupOk = false;
		$grp["assoc_new_id"] = $journalNewId;
		
		validateData("group", $grp); //from helperFunctions.php
		
		
		
		$arr = array();
		$arr["data"] = $grp;
		$arr["params"] = array(
			array("name" => ":context", "attr" => "context", "type" => PDO::PARAM_INT),
			array("name" => ":assocId", "attr" => "assoc_new_id", "type" => PDO::PARAM_INT),
			array("name" => ":assocType", "attr" => "assoc_type", "type" => PDO::PARAM_INT),
			array("name" => ":groups_aboutDisplayed", "attr" => "about_displayed", "type" => PDO::PARAM_INT),
			array("name" => ":groups_seq", "attr" => "seq"),
			array("name" => ":publishEmail", "attr" => "publish_email", "type" => PDO::PARAM_INT),
			
		);
		
		echo "    inserting group #" . $grp["group_id"]. "............ ";
		
		if (myExecute("insert", "group", $arr, $insertGroupSTMT, $errors)) { //from helperFunctions.php
			echo "OK\n";
			if (getNewId("group", $lastGroupsSTMT, $grp, $dataMapping, $errors)) { //from helperFunctions.php
				echo "    new id = " . $grp["group_new_id"] . "\n\n";
				$groupOk = true;
				$numInsertedGroups++;
			}
		}
		else {
			echo "Failed\n";
		}
		
		if ($groupOk) {
			
			////// insert the group settings //////////////////////////////////
			
			if (array_key_exists("settings", $grp)) { if (!empty($grp["settings"]) && $grp["settings"] != null) {
			
				foreach ($grp["settings"] as $setting) {
					validateData("group_settings", $setting); 
					
					$setting["group_new_id"] = $grp["group_new_id"];
					echo "    inserting ". $setting["setting_name"] . " with locale " . $setting["locale"] . " .........";
					
					$arr = array();
					$arr["data"] = $setting;
					$arr["params"] = array(
						array("name" => ":settings_groupId", "attr" => "group_new_id", "type" => PDO::PARAM_INT),
						array("name" => ":locale", "attr" => "locale", "type" => PDO::PARAM_STR),
						array("name" => ":settingName", "attr" => "setting_name", "type" => PDO::PARAM_STR),
						array("name" => ":settingValue", "attr" => "setting_value", "type" => PDO::PARAM_STR),
						array("name" => ":settingType", "attr" => "setting_type", "type" => PDO::PARAM_STR)
					);
					
					if (myExecute("insert", "group_settings", $arr, $insertGroupSettingsSTMT, $errors)) { //from helperFunctions.php
						echo "OK\n";
					}
					else {
						echo "Failed\n";
					}
				}//end of the foreach setting
				
			}}// end of the 2 ifs to see if group_settings exist
			
			///////////// end of insert group settings  //////////////////////
			
			
			////// insert the group memberships //////////////////////////////////
			
			if (array_key_exists("memberships", $grp)) { if (!empty($grp["memberships"]) && $grp["memberships"] != null) {
			
				foreach ($grp["memberships"] as $membership) {
					
					$userOk = false;
					
					//////////////////// process user data ///////////////////////
				
					// check if the user is registered in the new journal
					if (array_key_exists($membership["user_id"], $dataMapping["user_id"])) {
						$membership["user_new_id"] = $dataMapping["user_id"][$membership["user_id"]];
					}
					else {
						$userOk = processUser($membership["user"], array("type" => "group_membership", "data" => $membership), $dataMapping, $errors, $insertedUsers, $userStatements);
					}
					//////////////////////////////////////////////////////////////
					
					if ($userOk) {
						validateData("group_membership", $membership); 
					
						$membership["group_new_id"] = $grp["group_new_id"];
						$membership["user_new_id"] = $membership["user"]["user_new_id"];
						//echo "    inserting ". $membership["membership_name"] . " with locale " . $membership["locale"] . " .........";
						
						$arr = array();
						$arr["data"] = $membership;
						$arr["params"] = array(
							array("name" => ":memberships_groupId", "attr" => "group_new_id", "type" => PDO::PARAM_INT),
							array("name" => ":userId", "attr" => "user_new_id", "type" => PDO::PARAM_INT),
							array("name" => ":memberships_aboutDisplayed", "attr" => "about_displayed", "type" => PDO::PARAM_STR),
							array("name" => ":memberships_seq", "attr" => "seq")
						);
						
						if (myExecute("insert", "group_membership", $arr, $insertGroupMembershipSTMT, $errors)) { //from helperFunctions.php
							echo "OK\n";
						}
						else {
							echo "Failed\n";
						}
					}// end of the if userOk
					else {
						$error = array("group_id" => $grp["group_id"], "user" => $membership["user"], "error" => "error while processing the user");
						array_push($errors["group_membership"], $error);
					}
					
				}//end of the foreach membership
				
			}}// end of the 2 ifs to see if group_memberships exist
			
			///////////// end of insert group memberships  //////////////////////
			
		}//end of the if groupOk
	}//end of the foreach group	
	unset($grp);
	
	return array("errors" => $errors, "numInsertedRecords" => $numInsertedGroups, "insertedUsers" => $insertedUsers);
	
}

