<?php
require_once (APPPATH.'libraries/PHPPOSSpreadsheet.php');
function array_to_spreadsheet($arr,$filename,$is_report=FALSE,$force_text_columns=array(),$force_numeric_columns=array())
{	
	$spreadsheet = PHPPOSSpreadsheet::getSpreadsheetClass();
	$spreadsheet->arrayToSpreadsheet($arr,$filename, $is_report, $force_text_columns, $force_numeric_columns);
}

function file_to_spreadsheet($inputFileName,$type = 'xlsx')
{
	$spreadsheet = PHPPOSSpreadsheet::getSpreadsheetClass($inputFileName,$type);
	return $spreadsheet;
}

function get_spreadsheet_first_row($inputFileName,$type = 'xlsx')
{
	return PHPPOSSpreadsheet::getFirstRow($inputFileName, $type);
}