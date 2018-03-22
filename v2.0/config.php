<?php

/**
FUNCTIONS DEFINED IN THIS SCRIPT
/*
01) isSettings
02) isValidId
03) isFileId


THIS SCRIPT DEFINES THE VARIABLE $tables FOR USE IN THE ENTIRE APP

Developed in 2017 by Bernardo Amado

*/

// #01)
/**
returns true is $type is something like *settings
*/
function isSettings($type) {
	if (strlen($type) > 8) {
		if (substr($type, -8) === "settings") {
			return true;
		}
	}
	return false;
}

// #02)
/**
returns true is $type is something like *_id
*/
function isValidId($type) {
	if (strlen($type) > 3) {
		if (substr($type, -3) === "_id") {
			return true;
		}
	}
	return false;
}


// #03)
/**
returns true is $type is something like *file_id
*/
function isFileId($type) {
	if (strlen($type) >= 7) {
		if (substr($type, -7) === "file_id") {
			return true;
		}
	}
	return false;
}


//the variable tables stores the properties of the OJS database tables that might be used in the app
$tables = array(
	"article" => array(),
	"article_comment" => array(),
	"article_file" => array(),
	"article_galley" => array(),
	"article_galley_settings" => array(),
	"article_html_galley_image" => array(),
	"article_settings" => array(),
	"article_supplementary_file" => array(),
	"article_supp_file_settings" => array(),
	"article_search_object" => array(),
	"article_search_object_keyword" => array(),
	"article_search_keyword_list" => array(),
	"article_xml_galley" => array(),
	"edit_assignment" => array(),
	"edit_decision" => array(),
	"review_assignment" => array(),
	"review_round" => array(),
	"review_form" => array(),
	"review_form_settings" => array(),
	"review_form_element" => array(),
	"review_form_element_settings" => array(),
	"review_form_response" => array(),
	"role" => array(),
	"user" => array(), 
	"user_settings" => array(),
	"section" => array(),
	//"section_editor" => array(),
	"section_settings" => array(),
	"announcement_settings" => array(),
	"announcement_type_settings" => array(),
	"announcement_type" => array(),
	"announcement" => array(),
	"group_membership" => array(),
	"group_settings" => array(),
	"group" => array(),
	"email_templates" => array(),
	"email_templates_data" => array()
);


$tables["article"]["attributes"] = array("article_id", "user_id", "journal_id", "section_id", "language", "comments_to_ed", "date_submitted", "last_modified", 
"date_status_modified", "status", "submission_progress", "current_round", "submission_file_id", "revised_file_id", "review_file_id", "editor_file_id", 
"pages", "fast_tracked", "hide_author","comments_status", "locale", "citations");
$tables["article"]["primary_keys"] = array("article_id");
$tables["article"]["foreign_keys"] = array("user_id", "journal_id", "section_id");
$tables["article"]["properties"] = array();
$tables["article"]["properties"]["article_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["article"]["properties"]["user_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["article"]["properties"]["journal_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["article"]["properties"]["section_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["article"]["properties"]["language"] = array("type" => "varchar(10)", "null" => "yes", "key" => "", "default" => "en", "extra" => "");
$tables["article"]["properties"]["comments_to_ed"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article"]["properties"]["date_submitted"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article"]["properties"]["last_modified"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article"]["properties"]["date_status_modified"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article"]["properties"]["status"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["article"]["properties"]["submission_progress"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["article"]["properties"]["current_round"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["article"]["properties"]["submission_file_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article"]["properties"]["revised_file_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article"]["properties"]["review_file_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article"]["properties"]["editor_file_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article"]["properties"]["pages"] = array("type" => "varchar(255)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article"]["properties"]["fast_tracked"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["article"]["properties"]["hide_author"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["article"]["properties"]["comments_status"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["article"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article"]["properties"]["citations"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");

$tables["article_settings"]["attributes"] = array("article_id", "locale", "setting_name", "setting_value", "setting_type");
$tables["article_settings"]["primary_keys"] = array("article_id", "locale", "setting_name");
$tables["article_settings"]["foreign_keys"] = array();
$tables["article_settings"]["properties"] = array();
$tables["article_settings"]["properties"]["article_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["article_settings"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "no", "key" => "pri", "default" => "", "extra" => "");
$tables["article_settings"]["properties"]["setting_name"] = array("type" => "varchar(255)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["article_settings"]["properties"]["setting_value"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_settings"]["properties"]["setting_type"] = array("type" => "varchar(6)", "null" => "no", "key" => "", "default" => null, "extra" => "");


$tables["article_comment"]["attributes"] = array("comment_id", "comment_type", "role_id", "article_id", 
"assoc_id", "author_id", "commment_title", "comments", "date_posted", "date_modified", "viewable");
$tables["article_comment"]["primary_keys"] = array("comment_id");
$tables["article_comment"]["foreign_keys"] = array("article_id");
$tables["article_comment"]["properties"] = array();
$tables["article_comment"]["properties"]["comment_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["article_comment"]["properties"]["comment_type"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_comment"]["properties"]["role_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["article_comment"]["properties"]["article_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["article_comment"]["properties"]["assoc_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["article_comment"]["properties"]["author_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["article_comment"]["properties"]["comment_title"] = array("type" => "varchar(255)", "null" => "no", "key" => "", "default" => "Titulo do comentario", "extra" => "");
$tables["article_comment"]["properties"]["comments"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_comment"]["properties"]["date_posted"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_comment"]["properties"]["date_modified"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_comment"]["properties"]["viewable"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => null, "extra" => "");


$tables["article_file"]["attributes"] = array("file_id", "revision", "source_file_id", "source_revision", "article_id", "file_name", "file_type", 
"file_size", "original_file_name","file_stage", "viewable", "date_uploaded", "date_modified", "round", "assoc_id");
$tables["article_file"]["primary_keys"] = array("file_id", "revision");
$tables["article_file"]["foreign_keys"] = array("article_id");
$tables["article_file"]["properties"] = array();
$tables["article_file"]["properties"]["file_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["article_file"]["properties"]["revision"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["source_file_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["source_revision"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["article_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["file_name"] = array("type" => "varchar(90)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["file_type"] = array("type" => "varchar(255)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["file_size"] = array("type" => "bigint(20)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["original_file_name"] = array("type" => "varchar(127)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["file_stage"] = array("type" => "bigint(20)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["viewable"] = array("type" => "tinyint(4)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["date_uploaded"] = array("type" => "datetime", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["date_modified"] = array("type" => "datetime", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["round"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["article_file"]["properties"]["assoc_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");


$tables["article_supplementary_file"]["attributes"] = array("supp_id", "file_id", "article_id", "type", "language", "date_created", "show_reviewers", "date_submitted", "seq", "remote_url");
$tables["article_supplementary_file"]["primary_keys"] = array("supp_id");
$tables["article_supplementary_file"]["foreign_keys"] = array("file_id", "article_id");
$tables["article_supplementary_file"]["properties"] = array();
$tables["article_supplementary_file"]["properties"]["supp_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["article_supplementary_file"]["properties"]["file_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["article_supplementary_file"]["properties"]["article_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["article_supplementary_file"]["properties"]["type"] = array("type" => "varchar(255)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_supplementary_file"]["properties"]["language"] = array("type" => "varchar(10)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_supplementary_file"]["properties"]["date_created"] = array("type" => "date", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_supplementary_file"]["properties"]["show_reviewers"] = array("type" => "tinyint(4)", "null" => "yes", "key" => "", "default" => 0, "extra" => "");
$tables["article_supplementary_file"]["properties"]["date_submitted"] = array("type" => "datetime", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["article_supplementary_file"]["properties"]["seq"] = array("type" => "double", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["article_supplementary_file"]["properties"]["remote_url"] = array("type" => "varchar(255)", "null" => "yes", "key" => "", "default" => null, "extra" => "");

$tables["article_supp_file_settings"]["attributes"] = array("supp_id", "locale", "setting_name", "setting_value", "setting_type");
$tables["article_supp_file_settings"]["primary_keys"] = array("supp_id", "locale", "setting_name");
$tables["article_supp_file_settings"]["foreign_keys"] = array();
$tables["article_supp_file_settings"]["properties"] = array();
$tables["article_supp_file_settings"]["properties"]["supp_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["article_supp_file_settings"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "no", "key" => "pri", "default" => "", "extra" => "");
$tables["article_supp_file_settings"]["properties"]["setting_name"] = array("type" => "varchar(255)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["article_supp_file_settings"]["properties"]["setting_value"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_supp_file_settings"]["properties"]["setting_type"] = array("type" => "varchar(6)", "null" => "no", "key" => "", "default" => null, "extra" => "");


$tables["article_galley"]["attributes"] = array("galley_id", "locale", "article_id", "file_id", "label", "html_galley", "style_file_id", "seq", "remote_url");
$tables["article_galley"]["primary_keys"] = array("galley_id");
$tables["article_galley"]["foreign_keys"] = array("article_id", "file_id", "style_file_id");
$tables["article_galley"]["properties"] = array();
$tables["article_galley"]["properties"]["galley_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["article_galley"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_galley"]["properties"]["article_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["article_galley"]["properties"]["file_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["article_galley"]["properties"]["label"] = array("type" => "varchar(32)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_galley"]["properties"]["html_galley"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["article_galley"]["properties"]["style_file_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_galley"]["properties"]["seq"] = array("type" => "double", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["article_galley"]["properties"]["remote_url"] = array("type" => "varchar(255)", "null" => "yes", "key" => "", "default" => null, "extra" => "");

$tables["article_galley_settings"]["attributes"] = array("galley_id", "locale", "setting_name", "setting_value", "setting_type");
$tables["article_galley_settings"]["primary_keys"] = array("galley_id", "locale", "setting_name");
$tables["article_galley_settings"]["foreign_keys"] = array();
$tables["article_galley_settings"]["properties"] = array();
$tables["article_galley_settings"]["properties"]["galley_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["article_galley_settings"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "no", "key" => "pri", "default" => "", "extra" => "");
$tables["article_galley_settings"]["properties"]["setting_name"] = array("type" => "varchar(255)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["article_galley_settings"]["properties"]["setting_value"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["article_galley_settings"]["properties"]["setting_type"] = array("type" => "varchar(6)", "null" => "no", "key" => "", "default" => "-type-", "extra" => "");


$tables["article_html_galley_image"]["attributes"] = array("galley_id", "file_id");
$tables["article_html_galley_image"]["primary_keys"] = array("galley_id", "file_id");
$tables["article_html_galley_image"]["foreign_keys"] = array();
$tables["article_html_galley_image"]["properties"] = array();
$tables["article_html_galley_image"]["properties"]["galley_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["article_html_galley_image"]["properties"]["file_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");


$tables["article_xml_galley"]["attributes"] = array("xml_galley_id", "galley_id", "article_id", "label", "galley_type", "views");
$tables["article_xml_galley"]["primary_keys"] = array("xml_galley_id");
$tables["article_xml_galley"]["foreign_keys"] = array("galley_id", "article_id");
$tables["article_xml_galley"]["properties"] = array();
$tables["article_xml_galley"]["properties"]["xml_galley_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["article_xml_galley"]["properties"]["galley_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["article_xml_galley"]["properties"]["article_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["article_xml_galley"]["properties"]["label"] = array("type" => "varchar(32)", "null" => "no", "key" => "", "default" => "--label--", "extra" => "");
$tables["article_xml_galley"]["properties"]["galley_type"] = array("type" => "varchar(255)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["article_xml_galley"]["properties"]["views"] = array("type" => "int(11)", "null" => "no", "key" => "", "default" => 0, "extra" => "");

	
$tables["article_search_object"]["attributes"] = array("object_id", "article_id", "type", "assoc_id");
$tables["article_search_object"]["primary_keys"] = array("object_id");
$tables["article_search_object"]["foreign_keys"] = array("article_id");
$tables["article_search_object"]["properties"] = array();
$tables["article_search_object"]["properties"]["object_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["article_search_object"]["properties"]["article_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["article_search_object"]["properties"]["type"] = array("type" => "int(11)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["article_search_object"]["properties"]["assoc_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");


$tables["article_search_object_keyword"]["attributes"] = array("object_id", "keyword_id", "pos");
$tables["article_search_object_keyword"]["primary_keys"] = array("object_id", "pos");
$tables["article_search_object_keyword"]["foreign_keys"] = array("keyword_id");
$tables["article_search_object_keyword"]["properties"] = array();
$tables["article_search_object_keyword"]["properties"]["object_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["article_search_object_keyword"]["properties"]["keyword_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["article_search_object_keyword"]["properties"]["pos"] = array("type" => "int(11)", "null" => "no", "key" => "pri", "default" => 10, "extra" => "");


$tables["article_search_keyword_list"]["attributes"] = array("keyword_id", "keyword_text");
$tables["article_search_keyword_list"]["primary_keys"] = array("keyword_id");
$tables["article_search_keyword_list"]["foreign_keys"] = array();
$tables["article_search_keyword_list"]["properties"] = array();
$tables["article_search_keyword_list"]["properties"]["keyword_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["article_search_keyword_list"]["properties"]["keyword_text"] = array("type" => "varchar(60)", "null" => "no", "key" => "uni", "default" => null, "extra" => "");


$tables["author"]["attributes"] = array("author_id", "submission_id", "primary_contact", "seq", "first_name", "middle_name", "last_name", "country", "email", "url", "user_group_id", "suffix");
$tables["author"]["primary_keys"] = array("author_id");
$tables["author"]["foreign_keys"] = array("submission_id");
$tables["author"]["properties"] = array();
$tables["author"]["properties"]["author_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["author"]["properties"]["submission_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["author"]["properties"]["primary_contact"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["author"]["properties"]["seq"] = array("type" => "double", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["author"]["properties"]["first_name"] = array("type" => "varchar(40)", "null" => "no", "key" => "", "default" => "--first_name--", "extra" => "");
$tables["author"]["properties"]["middle_name"] = array("type" => "varchar(40)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["author"]["properties"]["last_name"] = array("type" => "varchar(90)", "null" => "no", "key" => "", "default" => "--last_name--", "extra" => "");
$tables["author"]["properties"]["country"] = array("type" => "varchar(90)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["author"]["properties"]["email"] = array("type" => "varchar(90)", "null" => "no", "key" => "", "default" => "--email--", "extra" => "");
$tables["author"]["properties"]["url"] = array("type" => "varchar(255)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["author"]["properties"]["user_group_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["author"]["properties"]["suffix"] = array("type" => "varchar(40)", "null" => "yes", "key" => "", "default" => null, "extra" => "");

$tables["author_settings"]["attributes"] = array("author_id", "locale", "setting_name", "setting_value", "setting_type");
$tables["author_settings"]["primary_keys"] = array("author_id", "locale", "setting_name");
$tables["author_settings"]["foreign_keys"] = array();
$tables["author_settings"]["properties"] = array();
$tables["author_settings"]["properties"]["author_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["author_settings"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "no", "key" => "pri", "default" => "", "extra" => "");
$tables["author_settings"]["properties"]["setting_name"] = array("type" => "varchar(255)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["author_settings"]["properties"]["setting_value"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["author_settings"]["properties"]["setting_type"] = array("type" => "varchar(6)", "null" => "no", "key" => "", "default" => null, "extra" => "");


$tables["edit_assignment"]["attributes"] = array("edit_id", "article_id", "editor_id", "can_edit", "can_review", "date_assigned", "date_notified", "date_underway");
$tables["edit_assignment"]["primary_keys"] = array("edit_id");
$tables["edit_assignment"]["foreign_keys"] = array("article_id", "editor_id");
$tables["edit_assignment"]["properties"] = array();
$tables["edit_assignment"]["properties"]["edit_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["edit_assignment"]["properties"]["article_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["edit_assignment"]["properties"]["editor_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["edit_assignment"]["properties"]["can_edit"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["edit_assignment"]["properties"]["can_review"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["edit_assignment"]["properties"]["date_assigned"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["edit_assignment"]["properties"]["date_notified"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["edit_assignment"]["properties"]["date_underway"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");


$tables["edit_decision"]["attributes"] = array("edit_decision_id", "article_id", "round", "editor_id", "decision", "date_decided");
$tables["edit_decision"]["primary_keys"] = array("edit_decision_id");
$tables["edit_decision"]["foreign_keys"] = array("article_id", "editor_id");
$tables["edit_decision"]["properties"] = array();
$tables["edit_decision"]["properties"]["edit_decision_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["edit_decision"]["properties"]["article_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["edit_decision"]["properties"]["editor_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["edit_decision"]["properties"]["round"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["edit_decision"]["properties"]["decision"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["edit_decision"]["properties"]["date_decided"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");


$tables["review_assignment"]["attributes"] = array("review_id", "submission_id", "reviewer_id", "competing_interests", "recommendation", "date_assigned", "date_notified", "date_confirmed",
"date_completed", "date_acknowledged", "date_due", "last_modified", "reminder_was_automatic", "declined", "replaced", "cancelled", "reviewer_file_id", "date_rated", "date_reminded",
"quality", "round", "review_form_id", "regret_message", "date_response_due", "review_method", "step", "review_round_id", "stage_id", "unconsidered");
$tables["review_assignment"]["primary_keys"] = array("review_id");
$tables["review_assignment"]["foreign_keys"] = array("submission_id", "reviewer_id", "review_form_id", "reviewer_file_id");
$tables["review_assignment"]["properties"] = array();
$tables["review_assignment"]["properties"]["review_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["review_assignment"]["properties"]["submission_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["reviewer_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["competing_interests"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["recommendation"] = array("type" => "tinyint(4)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["date_assigned"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["date_notified"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["date_confirmed"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["date_completed"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["date_acknowledged"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["date_due"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["last_modified"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["reminder_was_automatic"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["review_assignment"]["properties"]["declined"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["review_assignment"]["properties"]["replaced"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["review_assignment"]["properties"]["cancelled"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["review_assignment"]["properties"]["reviewer_file_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["date_rated"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["date_reminded"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["quality"] = array("type" => "tinyint(4)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["round"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["review_assignment"]["properties"]["review_form_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "mul", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["regret_message"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["date_response_due"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["review_method"] = array("type" => "tinyint(20)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["review_assignment"]["properties"]["step"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["review_assignment"]["properties"]["review_round_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_assignment"]["properties"]["stage_id"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["review_assignment"]["properties"]["unconsidered"] = array("type" => "tinyint(4)", "null" => "yes", "key" => "", "default" => null, "extra" => "");


$tables["review_round"]["attributes"] = array("submission_id", "round", "review_revision", "status", "review_round_id", "stage_id");
$tables["review_round"]["primary_keys"] = array("review_round_id");
$tables["review_round"]["foreign_keys"] = array("submission_id");
$tables["review_round"]["properties"] = array();
$tables["review_round"]["properties"]["review_round_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["review_round"]["properties"]["submission_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["review_round"]["properties"]["round"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["review_round"]["properties"]["review_revision"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_round"]["properties"]["status"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_round"]["properties"]["stage_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");


$tables["review_form"]["attributes"] = array("review_form_id", "assoc_id", "seq", "is_active", "assoc_type");
$tables["review_form"]["primary_keys"] = array("review_form_id");
$tables["review_form"]["foreign_keys"] = array();
$tables["review_form"]["properties"] = array();
$tables["review_form"]["properties"]["review_form_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["review_form"]["properties"]["assoc_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_form"]["properties"]["seq"] = array("type" => "double", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_form"]["properties"]["is_active"] = array("type" => "tinyint(4)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_form"]["properties"]["assoc_type"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_form"]["properties"]["stage_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");

$tables["review_form_settings"]["attributes"] = array("review_form_id", "locale", "setting_name", "setting_value", "setting_type");
$tables["review_form_settings"]["primary_keys"] = array("review_form_id", "locale", "setting_name");
$tables["review_form_settings"]["foreign_keys"] = array();
$tables["review_form_settings"]["properties"] = array();
$tables["review_form_settings"]["properties"]["review_form_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["review_form_settings"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "no", "key" => "pri", "default" => "", "extra" => "");
$tables["review_form_settings"]["properties"]["setting_name"] = array("type" => "varchar(255)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["review_form_settings"]["properties"]["setting_value"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_form_settings"]["properties"]["setting_type"] = array("type" => "varchar(6)", "null" => "no", "key" => "", "default" => null, "extra" => "");


$tables["review_form_element"]["attributes"] = array("review_form_element_id", "review_form_id", "seq", "element_type", "required", "included");
$tables["review_form_element"]["primary_keys"] = array("review_form_element_id");
$tables["review_form_element"]["foreign_keys"] = array("review_form_id");
$tables["review_form_element"]["properties"] = array();
$tables["review_form_element"]["properties"]["review_form_element_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["review_form_element"]["properties"]["review_form_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["review_form_element"]["properties"]["seq"] = array("type" => "double", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_form_element"]["properties"]["element_type"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_form_element"]["properties"]["required"] = array("type" => "tinyint(4)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_form_element"]["properties"]["included"] = array("type" => "tinyint(4)", "null" => "yes", "key" => "", "default" => null, "extra" => "");

$tables["review_form_element_settings"]["attributes"] = array("review_form_element_id", "locale", "setting_name", "setting_value", "setting_type");
$tables["review_form_element_settings"]["primary_keys"] = array("review_form_element_id", "locale", "setting_name");
$tables["review_form_element_settings"]["foreign_keys"] = array();
$tables["review_form_element_settings"]["properties"] = array();
$tables["review_form_element_settings"]["properties"]["review_form_element_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["review_form_element_settings"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "no", "key" => "pri", "default" => "", "extra" => "");
$tables["review_form_element_settings"]["properties"]["setting_name"] = array("type" => "varchar(255)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["review_form_element_settings"]["properties"]["setting_value"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_form_element_settings"]["properties"]["setting_type"] = array("type" => "varchar(6)", "null" => "no", "key" => "", "default" => null, "extra" => "");


$tables["review_form_response"]["attributes"] = array("review_form_element_id", "review_id", "response_type", "response_value");
$tables["review_form_response"]["primary_keys"] = array();
$tables["review_form_response"]["foreign_keys"] = array("review_form_element_id", "review_id");
$tables["review_form_response"]["properties"] = array();
$tables["review_form_response"]["properties"]["review_form_element_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "auto_increment");
$tables["review_form_response"]["properties"]["review_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["review_form_response"]["properties"]["response_type"] = array("type" => "varchar(6)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["review_form_response"]["properties"]["response_value"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");


$tables["role"]["attributes"] = array("journal_id", "user_id", "role_id");
$tables["role"]["primary_keys"] = array("journal_id", "user_id", "role_id");
$tables["role"]["foreign_keys"] = array("journal_id", "user_id");
$tables["role"]["properties"] = array();
$tables["role"]["properties"]["article_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["role"]["properties"]["user_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["role"]["properties"]["role_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => 65536, "extra" => "");

	
$tables["user"]["attributes"] = array("user_id", "username", "password", "salutation", "first_name", "middle_name", "last_name", "gender", "initials", "email", "url", "phone", "fax", "mailing_address",
"country", "locales", "date_last_email", "date_registered", "date_validated", "date_last_login", "must_change_password", "auth_id", "disabled", "disabled_reason", "auth_str", 
"suffix", "billing_address", "inline_help");
$tables["user"]["primary_keys"] = array("user_id");
$tables["user"]["foreign_keys"] = array();
$tables["user"]["properties"] = array();
$tables["user"]["properties"]["user_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["user"]["properties"]["username"] = array("type" => "varchar(32)", "null" => "no", "key" => "uni", "default" => null, "extra" => "");
$tables["user"]["properties"]["password"] = array("type" => "varchar(255)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["salutation"] = array("type" => "varchar(40)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["first_name"] = array("type" => "varchar(40)", "null" => "no", "key" => "", "default" => "", "extra" => "");
$tables["user"]["properties"]["middle_name"] = array("type" => "varchar(40)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["last_name"] = array("type" => "varchar(90)", "null" => "no", "key" => "", "default" => "", "extra" => "");
$tables["user"]["properties"]["gender"] = array("type" => "varchar(1)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["initials"] = array("type" => "varchar(5)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["email"] = array("type" => "varchar(90)", "null" => "no", "key" => "uni", "default" => null, "extra" => "");
$tables["user"]["properties"]["url"] = array("type" => "varchar(255)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["phone"] = array("type" => "varchar(24)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["fax"] = array("type" => "varchar(24)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["mailing_address"] = array("type" => "varchar(255)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["country"] = array("type" => "varchar(90)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["locales"] = array("type" => "varchar(255)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["date_last_email"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["date_registered"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["date_validated"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["date_last_login"] = array("type" => "datetime", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["must_change_password"] = array("type" => "tinyint(4)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["auth_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["disabled"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["user"]["properties"]["disabled_reason"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["auth_str"] = array("type" => "varchar(255)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["suffix"] = array("type" => "varchar(40)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["billing_address"] = array("type" => "varchar(255)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user"]["properties"]["inline_help"] = array("type" => "tinyint(4)", "null" => "yes", "key" => "", "default" => null, "extra" => "");

$tables["user_settings"]["attributes"] = array("user_id", "locale", "setting_name", "setting_value", "setting_type", "assoc_id", "assoc_type");
$tables["user_settings"]["primary_keys"] = array();
$tables["user_settings"]["foreign_keys"] = array("user_id");
$tables["user_settings"]["properties"] = array();
$tables["user_settings"]["properties"]["user_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["user_settings"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "no", "key" => "", "default" => "", "extra" => "");
$tables["user_settings"]["properties"]["setting_name"] = array("type" => "varchar(255)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["user_settings"]["properties"]["setting_value"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["user_settings"]["properties"]["setting_type"] = array("type" => "varchar(6)", "null" => "no", "key" => "", "default" => null, "extra" => "");
$tables["user_settings"]["properties"]["assoc_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => 0, "extra" => "");
$tables["user_settings"]["properties"]["assoc_type"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => 0, "extra" => "");


$tables["section"]["attributes"] = array("section_id", "journal_id", "review_form_id", "seq", "editor_restricted", "meta_indexed", "meta_reviewed", "abstracts_not_required",
"hide_title", "hide_author", "hide_about", "disable_comments", "abstract_word_count");
$tables["section"]["primary_keys"] = array("section_id");
$tables["section"]["foreign_keys"] = array("journal_id", "review_form_id");
$tables["section"]["properties"] = array();
$tables["section"]["properties"]["section_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "auto_increment");
$tables["section"]["properties"]["journal_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "mul", "default" => null, "extra" => "");
$tables["section"]["properties"]["review_form_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["section"]["properties"]["seq"] = array("type" => "double", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["section"]["properties"]["editor_restricted"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["section"]["properties"]["meta_indexed"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["section"]["properties"]["meta_reviewed"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["section"]["properties"]["abstracts_not_required"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["section"]["properties"]["hide_title"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["section"]["properties"]["hide_author"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["section"]["properties"]["hide_about"] = array("type" => "tinyint(4)", "null" => "yes", "key" => "", "default" => 0, "extra" => "");
$tables["section"]["properties"]["disable_comments"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["section"]["properties"]["abstract_word_count"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");

$tables["section_settings"]["attributes"] = array("section_id", "locale", "setting_name", "setting_value", "setting_type");
$tables["section_settings"]["primary_keys"] = array("section_id", "locale", "setting_name");
$tables["section_settings"]["foreign_keys"] = array();
$tables["section_settings"]["properties"] = array();
$tables["section_settings"]["properties"]["section_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["section_settings"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "no", "key" => "pri", "default" => "", "extra" => "");
$tables["section_settings"]["properties"]["setting_name"] = array("type" => "varchar(255)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["section_settings"]["properties"]["setting_value"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["section_settings"]["properties"]["setting_type"] = array("type" => "varchar(6)", "null" => "no", "key" => "", "default" => null, "extra" => "");

$tables["announcement"]["attributes"] = array("announcement_id", "assoc_id", "type_id", "date_expire", "date_posted", "assoc_type");
$tables["announcement"]["primary_keys"] = array("announcement_id");
$tables["announcement"]["foreign_keys"] = array("assoc_id"); // the assoc_id is the id of the journal the announcement is from
// the ojs system does not mark it as a foreign key but it should logically be, since id should be the same as the primary key of the journal from the table journals
$tables["announcement"]["properties"] = array();
$tables["announcement"]["properties"]["announcement_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => 0, "extra" => "auto_increment");
$tables["announcement"]["properties"]["assoc_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["announcement"]["properties"]["assoc_type"] = array("type" => "smallint(6)", "null" => "yes", "key" => "mul", "default" => null, "extra" => "");
$tables["announcement"]["properties"]["type_id"] = array("type" => "bigint(20)", "null" => "yes" , "key" => "", "default" => null, "extra" => "");
$tables["announcement"]["properties"]["date_posted"] = array("type" => "datetime", "null" => "no" , "key" => "", "default" => null, "extra" => "");
$tables["announcement"]["properties"]["date_expire"] = array("type" => "datetime", "null" => "yes" , "key" => "", "default" => null, "extra" => "");

$tables["announcement_settings"]["attributes"] = array("announcement_id", "locale", "setting_name", "setting_value", "setting_type");
$tables["announcement_settings"]["primary_keys"] = array("announcement_id", "locale", "setting_name");
$tables["announcement_settings"]["foreign_keys"] = array();
$tables["announcement_settings"]["properties"] = array();
$tables["announcement_settings"]["properties"]["announcement_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["announcement_settings"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "no", "key" => "pri", "default" => "", "extra" => "");
$tables["announcement_settings"]["properties"]["setting_name"] = array("type" => "varchar(255)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["announcement_settings"]["properties"]["setting_value"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["announcement_settings"]["properties"]["setting_type"] = array("type" => "varchar(6)", "null" => "no", "key" => "", "default" => null, "extra" => "");

$tables["announcement_type"]["attributes"] = array("type_id", "assoc_id", "assoc_type");
$tables["announcement_type"]["primary_keys"] = array("type_id");
$tables["announcement_type"]["foreign_keys"] = array(); 
$tables["announcement_type"]["properties"] = array();
$tables["announcement_type"]["properties"]["type_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => 0, "extra" => "auto_increment");
$tables["announcement_type"]["properties"]["assoc_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["announcement_type"]["properties"]["assoc_type"] = array("type" => "smallint(6)", "null" => "yes", "key" => "mul", "default" => null, "extra" => "");

$tables["announcement_type_settings"]["attributes"] = array("type_id", "locale", "setting_name", "setting_value", "setting_type");
$tables["announcement_type_settings"]["primary_keys"] = array("type_id", "locale", "setting_name");
$tables["announcement_type_settings"]["foreign_keys"] = array();
$tables["announcement_type_settings"]["properties"] = array();
$tables["announcement_type_settings"]["properties"]["type_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["announcement_type_settings"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "no", "key" => "pri", "default" => "", "extra" => "");
$tables["announcement_type_settings"]["properties"]["setting_name"] = array("type" => "varchar(255)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["announcement_type_settings"]["properties"]["setting_value"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["announcement_type_settings"]["properties"]["setting_type"] = array("type" => "varchar(6)", "null" => "no", "key" => "", "default" => null, "extra" => "");


$tables["group"]["attributes"] = array("group_id", "context", "assoc_id", "assoc_type", "about_displayed", "seq", "publish_email");
$tables["group"]["primary_keys"] = array("group_id");
$tables["group"]["foreign_keys"] = array();
$tables["group"]["properties"] = array();
$tables["group"]["properties"]["group_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => 0, "extra" => "auto_increment");
$tables["group"]["properties"]["context"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["group"]["properties"]["assoc_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => 0, "extra" => "");
// assoc_type is key mul because it is an index with assoc_id
$tables["group"]["properties"]["assoc_type"] = array("type" => "smallint(6)", "null" => "yes", "key" => "mul", "default" => null, "extra" => ""); 
$tables["group"]["properties"]["about_displayed"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["group"]["properties"]["seq"] = array("type" => "double", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["group"]["properties"]["publish_email"] = array("type" => "smallint(6)", "null" => "yes", "key" => "", "default" => null, "extra" => "");

$tables["group_settings"]["attributes"] = array("group_id", "locale", "setting_name", "setting_value", "setting_type");
$tables["group_settings"]["primary_keys"] = array("group_id", "locale", "setting_name");
$tables["group_settings"]["foreign_keys"] = array();
$tables["group_settings"]["properties"] = array();
$tables["group_settings"]["properties"]["group_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["group_settings"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "no", "key" => "pri", "default" => "", "extra" => "");
$tables["group_settings"]["properties"]["setting_name"] = array("type" => "varchar(255)", "null" => "no", "key" => "pri", "default" => null, "extra" => "");
$tables["group_settings"]["properties"]["setting_value"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");
$tables["group_settings"]["properties"]["setting_type"] = array("type" => "varchar(6)", "null" => "no", "key" => "", "default" => null, "extra" => "");

$tables["group_membership"]["attributes"] = array("group_id", "user_id", "about_displayed", "seq");
$tables["group_membership"]["primary_keys"] = array("group_id", "user_id");
$tables["group_membership"]["foreign_keys"] = array();
$tables["group_membership"]["properties"] = array();
$tables["group_membership"]["properties"]["group_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => 0, "extra" => "auto_increment");
$tables["group_membership"]["properties"]["user_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => 0, "extra" => ""); 
$tables["group_membership"]["properties"]["about_displayed"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 0, "extra" => "");
$tables["group_membership"]["properties"]["seq"] = array("type" => "double", "null" => "no", "key" => "", "default" => 0, "extra" => "");


$tables["email_templates"]["attributes"] = array("email_id", "email_key", "assoc_id", "enabled", "assoc_type");
$tables["email_templates"]["primary_keys"] = array("email_id");
$tables["email_templates"]["foreign_keys"] = array("assoc_id"); // the id of the journal
$tables["email_templates"]["properties"] = array();
$tables["email_templates"]["properties"]["email_id"] = array("type" => "bigint(20)", "null" => "no", "key" => "pri", "default" => 0, "extra" => "auto_increment");
$tables["email_templates"]["properties"]["email_key"] = array("type" => "varchar(64)", "null" => "no", "key" => "mull", "default" => "not_the_mama", "extra" => "");
$tables["email_templates"]["properties"]["assoc_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => 0, "extra" => "");
$tables["email_templates"]["properties"]["enabled"] = array("type" => "tinyint(4)", "null" => "no", "key" => "", "default" => 1, "extra" => "");
$tables["email_templates"]["properties"]["assoc_type"] = array("type" => "bigint(20)", "null" => "yes", "key" => "mul", "default" => 0, "extra" => "");

$tables["email_templates_data"]["attributes"] = array("email_key", "locale", "assoc_id", "assoc_type", "subject", "body");
$tables["email_templates_data"]["primary_keys"] = array();
$tables["email_templates_data"]["foreign_keys"] = array("email_id", "assoc_id"); 
$tables["email_templates_data"]["properties"] = array();
$tables["email_templates_data"]["properties"]["locale"] = array("type" => "varchar(5)", "null" => "no", "key" => "", "default" => "en_US", "extra" => "");
$tables["email_templates_data"]["properties"]["email_key"] = array("type" => "varchar(64)", "null" => "no", "key" => "mull", "default" => "not_the_mama", "extra" => "");
$tables["email_templates_data"]["properties"]["assoc_id"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => 0, "extra" => "");
$tables["email_templates_data"]["properties"]["assoc_type"] = array("type" => "bigint(20)", "null" => "yes", "key" => "", "default" => 0, "extra" => "");
$tables["email_templates_data"]["properties"]["subject"] = array("type" => "varchar(120)", "null" => "no", "key" => "", "default" => "not_the_mama", "extra" => "");
$tables["email_templates_data"]["properties"]["body"] = array("type" => "text", "null" => "yes", "key" => "", "default" => null, "extra" => "");


//print_r($tables); exit();

$idFields = array();
foreach ($tables as $type => $arr) {
	
	if (!array_key_exists("primary_keys", $arr)) {
		print_r($arr);
		exit("$type does not have the array primary_keys");
	}
	foreach ($arr["primary_keys"] as $pk) {
		if (!in_array($pk, $idFields) && isValidId($pk) && !isSettings($type)) {
			array_push($idFields, $pk);
		}
	}
	
	
	if (!array_key_exists("foreign_keys", $arr)) {
		print_r($arr);
		exit("$type does not have the array foreign_keys");
	}
	foreach ($arr["foreign_keys"] as $fk) {
		if (!in_array($fk, $idFields) && isValidId($fk) && !isSettings($type)) {
			array_push($idFields, $fk);
		}
	}
	
	
	if (!array_key_exists("attributes", $arr)) {
		print_r($arr);
		exit("$type does not have the array attributes");
	}
	foreach ($arr["attributes"] as $attr) {
		if (!in_array($attr, $idFields) && isFileId($attr) && !isSettings($type)) {
			array_push($idFields, $attr);
		}
	}
	
}


$updateFields = $idFields;

//array_push($updateFields, "comment_author_id");
array_push($updateFields, "file_name");
array_push($updateFields, "original_file_name");



