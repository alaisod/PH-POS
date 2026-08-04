<?php
abstract class PHPPOSSpreadsheet
{
	public static function getSpreadsheetClass($inputFileName = null, $type='xlsx')
	{
		require_once (APPPATH.'libraries/PHPPOSSpreadsheetNative.php');
		return new PHPPOSSpreadsheetNative($inputFileName, $type);
	}
	
	public static function getFirstRow($inputFileName, $type='xlsx')
	{
		require_once (APPPATH.'libraries/PHPPOSSpreadsheetNative.php');
		return PHPPOSSpreadsheetNative::getFirstRow($inputFileName, $type);
	}
	
	//$column starts at 0 and row starts at 1
	public abstract function getCellByColumnAndRow($column, $row);
	
	public abstract function getNumberOfRows();
	
	//$data is a matrix to export to excel
	//$force_text_columns = array of 0-based column indexes that must always be written as text (only trimmed at start/end)
	//$force_numeric_columns = array of 0-based column indexes that are currency amounts and must be written as numbers
	public abstract function arrayToSpreadsheet($arr,$filename, $is_report = false, $force_text_columns = array(), $force_numeric_columns = array());
	
	protected function stripCurrency($val, $force = false)
	{
		$CI =& get_instance();
		
		$currency_symbol = $CI->config->item('currency_symbol') ? $CI->config->item('currency_symbol') : '$';
		$thousands_separator = $CI->config->item('thousands_separator') ? $CI->config->item('thousands_separator') : ',';
		$decimal_point = $CI->config->item('decimal_point') ? $CI->config->item('decimal_point') : '.';
	
		if ($force)
		{
			//$force = column the caller already knows is a currency amount (to_currency output).
			//Keep only digits, the minus sign and the decimal point, so the value becomes a clean
			//number regardless of which currency symbol / thousands separator is configured.
			$stripped = preg_replace('/[^0-9\-' . preg_quote($decimal_point, '/') . ']/', '', $val);
			
			if (is_numeric($stripped))
			{
				$val = $stripped;
			}
		}
		//Only apply the guard-based strip when the result is a real number, so plain text
		//(e.g. product_id 'GE IP 13') is never altered by this function
		elseif ($val !== '' && strpos($val, $currency_symbol) !== false)
		{
			$thousands_separator = preg_quote($thousands_separator);
			$currency_symbol = preg_quote($currency_symbol);
			$stripped = preg_replace("/[${thousands_separator}${currency_symbol}]/", "", $val);
			
			if (is_numeric($stripped))
			{
				$val = $stripped;
			}
		}
		
		return $val;
		
	}
}
?>