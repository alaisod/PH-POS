<?php
/**
 * PHPPOSSpreadsheetNative - Custom spreadsheet library for PHPPOS
 * Built entirely with PHP built-in extensions (ZipArchive, SimpleXML, DOM)
 * No external dependencies required
 * Supports XLSX and CSV formats for both reading and writing
 * 
 * Replaces: PHPExcel (deprecated), Box/Spout v2 (abandoned)
 * PHP 8.x compatible
 */

class PHPPOSSpreadsheetNative extends PHPPOSSpreadsheet
{
	private $data;
	private $numRows;

	/**
	 * Constructor - loads spreadsheet data into memory
	 * @param string|null $inputFileName Path to file
	 * @param string $type 'xlsx' or 'csv'
	 */
	function __construct($inputFileName = NULL, $type = 'xlsx')
	{
		if ($inputFileName) {
			if (strtolower($type) == 'xlsx') {
				$this->loadXLSX($inputFileName);
			} else {
				$this->loadCSV($inputFileName);
			}
		}
	}

	/**
	 * Load XLSX file into memory using ZipArchive + SimpleXML
	 * 
	 * @param string $inputFileName Path to .xlsx file
	 */
	private function loadXLSX($inputFileName)
	{
		$this->data = array();
		
		if (!class_exists('ZipArchive')) {
			$this->numRows = 0;
			return;
		}

		$zip = new ZipArchive();
		if ($zip->open($inputFileName) !== true) {
			$this->numRows = 0;
			return;
		}

		// --- Read shared strings table ---
		$sharedStrings = array();
		$sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
		if ($sharedStringsXml !== false) {
			$ssXml = simplexml_load_string($sharedStringsXml);
			if ($ssXml) {
				foreach ($ssXml->si as $si) {
					// Check for rich text (multiple runs <r>)
					$runs = $si->r;
					if ($runs && count($runs) > 0) {
						$text = '';
						foreach ($runs as $run) {
							$text .= (string)$run->t;
						}
						$sharedStrings[] = $text;
					} else {
						// Plain text in <t>
						$sharedStrings[] = (string)$si->t;
					}
				}
			}
		}

		// --- Read sheet data ---
		$sheetXmlStr = $zip->getFromName('xl/worksheets/sheet1.xml');
		$zip->close();

		if ($sheetXmlStr === false) {
			$this->numRows = 0;
			return;
		}

		$sheetXml = simplexml_load_string($sheetXmlStr);
		if (!$sheetXml) {
			$this->numRows = 0;
			return;
		}

		// Get the sheet data (SimpleXML handles default namespace automatically)
		$sheetData = $sheetXml->sheetData;
		if (!$sheetData) {
			$this->numRows = 0;
			return;
		}

		// Register namespace for XPath if needed
		$namespaces = $sheetXml->getNamespaces(true);
		$ns = isset($namespaces['']) ? $namespaces[''] : '';

		if ($ns) {
			$sheetXml->registerXPathNamespace('s', $ns);
			$rows = $sheetXml->xpath('//s:sheetData/s:row');
		} else {
			$rows = $sheetData->row;
		}

		if (!$rows) {
			$this->numRows = 0;
			return;
		}

		$allRows = array();
		foreach ($rows as $row) {
			$rowIndex = (int)$row['r'];
			$rowData = array();

			foreach ($row->c as $cell) {
				$cellRef = (string)$cell['r'];
				$cellType = (string)$cell['t'];
				$cellValue = (string)$cell->v;

				// Parse column index from cell reference (e.g., "A1" -> 0)
				$colStr = preg_replace('/[0-9]/', '', $cellRef);
				$colIndex = $this->columnLetterToIndex($colStr);

				// Get actual value based on type
				if ($cellType === 's' && $cellValue !== '') {
					// Shared string
					$strIndex = intval($cellValue);
					$rowData[$colIndex] = isset($sharedStrings[$strIndex]) ? $sharedStrings[$strIndex] : '';
				} elseif ($cellType === 'inlineStr') {
					// Inline string
					$tNode = $cell->is->t;
					$rowData[$colIndex] = $tNode !== null ? (string)$tNode : '';
				} elseif ($cellType === 'b') {
					// Boolean
					$rowData[$colIndex] = ($cellValue === '1' || $cellValue === 'true') ? '1' : '0';
				} elseif ($cellType === 'e') {
					// Error - skip
					$rowData[$colIndex] = '';
				} else {
					// Numeric or str
					$rowData[$colIndex] = $cellValue;
				}
			}

			ksort($rowData);
			
			// Fill in gaps for empty columns (e.g., if A,B empty but C has data)
			if (!empty($rowData)) {
				$maxCol = max(array_keys($rowData));
				for ($i = 0; $i <= $maxCol; $i++) {
					if (!isset($rowData[$i])) {
						$rowData[$i] = null;
					}
				}
				ksort($rowData);
			}
			
			$allRows[$rowIndex] = $rowData;
		}

		$this->data = $allRows;
		$this->numRows = count($this->data);
	}

	/**
	 * Convert Excel column letter(s) to 0-based index
	 * A=0, B=1, ..., Z=25, AA=26, AZ=51, BA=52, ...
	 * 
	 * @param string $letter Column letter(s)
	 * @return int 0-based index
	 */
	private function columnLetterToIndex($letter)
	{
		$letter = strtoupper(trim($letter));
		$index = 0;
		$len = strlen($letter);
		for ($i = 0; $i < $len; $i++) {
			$index = $index * 26 + (ord($letter[$i]) - ord('A') + 1);
		}
		return $index - 1;
	}

	/**
	 * Convert 0-based column index to Excel column letter(s)
	 * 0=A, 1=B, ..., 25=Z, 26=AA, 51=AZ, 52=BA, ...
	 * 
	 * @param int $index 0-based index
	 * @return string Column letter(s)
	 */
	private function indexToColumnLetter($index)
	{
		$letter = '';
		$index++; // Convert to 1-based
		while ($index > 0) {
			$mod = ($index - 1) % 26;
			$letter = chr($mod + ord('A')) . $letter;
			$index = (int)(($index - $mod) / 26);
		}
		return $letter;
	}

	/**
	 * Load CSV file into memory using fgetcsv
	 * Handles UTF-8 BOM and encoding detection
	 * 
	 * @param string $inputFileName Path to .csv file
	 */
	private function loadCSV($inputFileName)
	{
		$this->data = array();
		$handle = fopen($inputFileName, 'r');
		
		if (!$handle) {
			$this->numRows = 0;
			return;
		}

		// Check for UTF-8 BOM and skip it
		$bom = fread($handle, 3);
		if ($bom !== "\xEF\xBB\xBF") {
			// No BOM, rewind
			rewind($handle);
		}

		// Try to detect delimiter (comma vs semicolon for European locales)
		$firstLine = fgets($handle);
		if ($firstLine === false) {
			fclose($handle);
			$this->numRows = 0;
			return;
		}
		rewind($handle);
		if ($bom === "\xEF\xBB\xBF") {
			fread($handle, 3); // Skip BOM again
		}

		// Detect delimiter
		$delimiter = ',';
		if ($firstLine !== false) {
			$commaCount = substr_count($firstLine, ',');
			$semicolonCount = substr_count($firstLine, ';');
			if ($semicolonCount > $commaCount) {
				$delimiter = ';';
			}
		}
		rewind($handle);
		if ($bom === "\xEF\xBB\xBF") {
			fread($handle, 3);
		}

		$rowIndex = 1;
		while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
			// Convert from other encodings if needed
			foreach ($row as $key => $value) {
				$row[$key] = $this->ensureUtf8($value);
			}
			$this->data[$rowIndex] = $row;
			$rowIndex++;
		}

		fclose($handle);
		$this->numRows = count($this->data);
	}

	/**
	 * Ensure string is UTF-8 encoded
	 * 
	 * @param string $str Input string
	 * @return string UTF-8 encoded string
	 */
	private function ensureUtf8($str)
	{
		if ($str === null || $str === '') {
			return $str;
		}
		
		if (mb_detect_encoding($str, 'UTF-8', true) === false) {
			$detected = mb_detect_encoding($str, 'ISO-8859-1, Windows-1252, UTF-8', true);
			if ($detected && $detected !== 'UTF-8') {
				return @mb_convert_encoding($str, 'UTF-8', $detected);
			}
		}
		
		return $str;
	}

	/**
	 * Static method to get first row of a spreadsheet
	 * Must match existing API: PHPPOSSpreadsheetSpout::getFirstRow()
	 * 
	 * @param string $inputFileName Path to file
	 * @param string $type File type ('xlsx' or 'csv')
	 * @return array First row as array of values
	 */
	public static function getFirstRow($inputFileName, $type = 'xlsx')
	{
		$obj = new self($inputFileName, $type);
		if ($obj->numRows > 0) {
			$keys = array_keys($obj->data);
			if (!empty($keys)) {
				return $obj->data[$keys[0]];
			}
		}
		return array();
	}

	/**
	 * Get cell value by column and row
	 * $column starts at 0, $row starts at 1
	 * Matches existing API: PHPExcel getCellByColumnAndRow()
	 * 
	 * @param int $column 0-based column index
	 * @param int $row 1-based row index
	 * @return string|null Cell value or null if not found
	 */
	public function getCellByColumnAndRow($column, $row)
	{
		if (isset($this->data[$row]) && is_array($this->data[$row]) && array_key_exists($column, $this->data[$row])) {
			return $this->data[$row][$column];
		}
		return null;
	}

	/**
	 * Get total number of rows in the spreadsheet
	 * Matches existing API
	 * 
	 * @return int Number of rows
	 */
	public function getNumberOfRows()
	{
		return $this->numRows;
	}

	/**
	 * Export array data as a spreadsheet download
	 * Matches existing API: PHPPOSSpreadsheetPHPExcel::arrayToSpreadsheet()
	 * 
	 * @param array $arr 2D array of data (rows of cells)
	 * @param string $filename Output filename (with extension)
	 * @param bool $is_report Whether this is a report (affects formatting)
	 */
	public function arrayToSpreadsheet($arr, $filename, $is_report = false)
	{
		$CI =& get_instance();

		if ($CI->config->item('spreadsheet_format') == 'XLSX') {
			$this->arrayToXLSX($arr, $filename, $is_report);
		} else {
			$this->arrayToCSV($arr, $filename, $is_report);
		}
	}

	/**
	 * Export data as CSV file download
	 * Includes UTF-8 BOM for proper Thai/Unicode support
	 * 
	 * @param array $arr 2D array of data
	 * @param string $filename Output filename
	 * @param bool $is_report Whether this is a report
	 */
	private function arrayToCSV($arr, $filename, $is_report)
	{
		$CI =& get_instance();

		ob_start();
		$output = fopen('php://output', 'w');

		if (!$output) {
			return;
		}

		// Write UTF-8 BOM for proper Unicode support (Thai, etc.)
		fprintf($output, "\xEF\xBB\xBF");

		foreach ($arr as $row) {
			if ($is_report) {
				$processedRow = array();
				foreach ($row as $cell) {
					$processedRow[] = $this->stripCurrency((string)$cell);
				}
				fputcsv($output, $processedRow);
			} else {
				// For non-reports, ensure all values are treated as text strings
				$processedRow = array();
				foreach ($row as $cell) {
					$processedRow[] = (string)$cell;
				}
				fputcsv($output, $processedRow);
			}
		}

		fclose($output);
		$csvOutput = ob_get_clean();

		$CI->load->helper('download');
		force_download($filename, $csvOutput);
	}

	/**
	 * Export data as XLSX file download using pure PHP
	 * Creates a proper Office Open XML Spreadsheet (ZIP containing XML)
	 * 
	 * @param array $arr 2D array of data
	 * @param string $filename Output filename
	 * @param bool $is_report Whether this is a report
	 */
	private function arrayToXLSX($arr, $filename, $is_report)
	{
		$CI =& get_instance();

		if (!class_exists('ZipArchive')) {
			// Fallback to CSV if ZipArchive not available
			$csvFilename = preg_replace('/\.xlsx$/i', '.csv', $filename);
			$this->arrayToCSV($arr, $csvFilename, $is_report);
			return;
		}

		// Build shared strings and sheet data
		$sharedStrings = array();
		$sharedStringIndex = array(); // value => id mapping
		$sheetRows = '';
		$rowIndex = 1;

		foreach ($arr as $row) {
			$sheetRows .= '<row r="' . $rowIndex . '">';
			$colIndex = 0;

			foreach ($row as $cellValue) {
				$colLetter = $this->indexToColumnLetter($colIndex);
				$cellValue = (string)$cellValue;

				if ($is_report) {
					$cellValue = $this->stripCurrency($cellValue);
				}

				if ($is_report && is_numeric($cellValue) && $cellValue !== '') {
					// Report mode: keep numbers as numeric values
					if (strpos($cellValue, '.') !== false) {
						$sheetRows .= '<c r="' . $colLetter . $rowIndex . '" t="n"><v>' . floatval($cellValue) . '</v></c>';
					} else {
						$sheetRows .= '<c r="' . $colLetter . $rowIndex . '" t="n"><v>' . intval($cellValue) . '</v></c>';
					}
				} else {
					// Non-report mode (or non-numeric in report): use shared strings
					$strKey = $cellValue;
					if (!isset($sharedStringIndex[$strKey])) {
						$sharedStringIndex[$strKey] = count($sharedStrings);
						$sharedStrings[] = $strKey;
					}
					$ssId = $sharedStringIndex[$strKey];
					// Style index 1 = text format, 0 = default
					$sheetRows .= '<c r="' . $colLetter . $rowIndex . '" t="s" s="1"><v>' . $ssId . '</v></c>';
				}

				$colIndex++;
			}

			$sheetRows .= '</row>';
			$rowIndex++;
		}

		// Build shared strings XML
		$ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		$ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
		foreach ($sharedStrings as $ss) {
			$ssXml .= '<si><t>' . htmlspecialchars($ss, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></si>';
		}
		$ssXml .= '</sst>';

		// Build sheet XML
		$sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		$sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
		$sheetXml .= '<sheetData>' . $sheetRows . '</sheetData>';
		$sheetXml .= '</worksheet>';

		// Build styles XML
		$stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		$stylesXml .= '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
		$stylesXml .= '<numFmts count="1">';
		$stylesXml .= '<numFmt numFmtId="164" formatCode="@"/>';
		$stylesXml .= '</numFmts>';
		$stylesXml .= '<fonts count="1">';
		$stylesXml .= '<font><sz val="10"/><name val="Arial"/></font>';
		$stylesXml .= '</fonts>';
		$stylesXml .= '<fills count="2">';
		$stylesXml .= '<fill><patternFill patternType="none"/></fill>';
		$stylesXml .= '<fill><patternFill patternType="gray125"/></fill>';
		$stylesXml .= '</fills>';
		$stylesXml .= '<borders count="1">';
		$stylesXml .= '<border><left/><right/><top/><bottom/><diagonal/></border>';
		$stylesXml .= '</borders>';
		$stylesXml .= '<cellStyleXfs count="1">';
		$stylesXml .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>';
		$stylesXml .= '</cellStyleXfs>';
		$stylesXml .= '<cellXfs count="2">';
		// xfId 0: Default format
		$stylesXml .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>';
		// xfId 1: Text format (numFmtId 164 = @) for non-reports, default for reports
		if (!$is_report) {
			$stylesXml .= '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>';
		} else {
			$stylesXml .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>';
		}
		$stylesXml .= '</cellXfs>';
		$stylesXml .= '<cellStyles count="1">';
		$stylesXml .= '<cellStyle name="Normal" xfId="0" builtinId="0"/>';
		$stylesXml .= '</cellStyles>';
		$stylesXml .= '</styleSheet>';

		// Create the ZIP file
		$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
		$zip = new ZipArchive();

		if ($zip->open($tmpFile, ZipArchive::CREATE) !== true) {
			return;
		}

		// [Content_Types].xml
		$zip->addFromString('[Content_Types].xml',
			'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
			'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
			'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
			'<Default Extension="xml" ContentType="application/xml"/>' .
			'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
			'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
			'<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>' .
			'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
			'</Types>'
		);

		// _rels/.rels
		$zip->addFromString('_rels/.rels',
			'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
			'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
			'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
			'</Relationships>'
		);

		// xl/_rels/workbook.xml.rels
		$zip->addFromString('xl/_rels/workbook.xml.rels',
			'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
			'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
			'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
			'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>' .
			'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
			'</Relationships>'
		);

		// xl/workbook.xml
		$zip->addFromString('xl/workbook.xml',
			'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
			'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
			'<sheets>' .
			'<sheet name="Sheet1" sheetId="1" r:id="rId1"/>' .
			'</sheets>' .
			'</workbook>'
		);

		// xl/worksheets/sheet1.xml
		$zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

		// xl/sharedStrings.xml
		$zip->addFromString('xl/sharedStrings.xml', $ssXml);

		// xl/styles.xml
		$zip->addFromString('xl/styles.xml', $stylesXml);

		$zip->close();

		// Output the file
		$CI->load->helper('download');

		$xlsxData = file_get_contents($tmpFile);
		unlink($tmpFile);

		force_download($filename, $xlsxData);
	}
}
