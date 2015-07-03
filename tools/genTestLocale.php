<?php

/**
 * @file tools/genTestLocale.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class genTestLocale
 * @ingroup tools
 *
 * @brief CLI tool to generate a test locale file by munging the message strings of a real locale file.
 */



require(dirname(__FILE__) . '/bootstrap.inc.php');

define('DEFAULT_IN_LOCALE', 'en_US');
define('DEFAULT_OUT_LOCALE', 'te_ST');
define('DEFAULT_OUT_LOCALE_NAME', "Test Lo\xc3\xa7ale");

import('classes.i18n.AppLocale');

class genTestLocale extends CommandLineTool {

	var $inLocale;
	var $outLocale;

	/**
	 * Constructor.
	 * @param $argv array command-line arguments
	 * 		If specified, the first parameter should be the input locale (default "en_US")
	 * 		and the second parameter the output locale (default "te_ST")
	 */
	function genTestLocale($argv = array()) {
		parent::CommandLineTool($argv);

		if (count($this->argv) == 2) {
			$this->inLocale = $this->argv[0];
			$this->outLocale = $this->argv[1];

		} else {
			$this->inLocale = DEFAULT_IN_LOCALE;
			$this->outLocale = DEFAULT_OUT_LOCALE;
		}

		$this->replaceMap = array(
			'a' => "\xc3\xa5",
			'A' => "\xc3\x86",
			'c' => "\xc3\xa7",
			'C' => "\xc3\x87",
			'd' => "\xc3\xb0",
			'D' => "\xc3\x90",
			'e' => "\xc3\xa8",
			'E' => "\xc3\x89",
			'i' => "\xc3\xae",
			'I' => "\xc3\x8e",
			'n' => "\xc3\xb1",
			'N' => "\xc3\x91",
			'o' => "\xc3\xb3",
			'O' => "\xc3\x92",
			's' => "\xc3\xbe",
			'S' => "\xc3\x9f",
			'u' => "\xc3\xbc",
			'U' => "\xc3\x9c",
			'y' => "\xc3\xbd",
			'Y' => "\xc3\x9d",
			'&' => "&amp;"
		);
	}

	/**
	 * Create the test locale file.
	 */
	function execute() {
		AppLocale::initialize();
		$localeFiles = AppLocale::makeComponentMap($this->inLocale);

		foreach($localeFiles as $localeFilePath) {
			$localeFile = basename($localeFilePath);
			$outFile = dirname(dirname($localeFilePath)) . '/' . $this->outLocale . '/' . $localeFile;
			$this->generateLocaleFile($localeFile, $localeFilePath, $outFile);
		}
	}

	/**
	 * Perform message string munging.
	 * @param $localeFile string
	 * @param $localeFilePath string
	 * @param $outFile string
	 */
	function generateLocaleFile($localeFile, $localeFilePath, $outFile) {
		$localeData = LocaleFile::load($localeFilePath);

		if (!isset($localeData)) {
			printf('Invalid locale \'%s\'', $this->inLocale);
			exit(1);
		}

		$destDir = dirname($outFile);
		if (!file_exists($destDir)) {
			import('lib.pkp.classes.file.FileManager');
			$fileManager = new FileManager();
			if (!$fileManager->mkdir($destDir)) {
				printf('Failed to createDirectory \'%s\'', $destDir);
				exit(1);
			}
		}

		$fp = fopen($outFile, 'wb');
		if (!$fp) {
			printf('Failed to write to \'%s\'', $outFile);
			exit(1);
		}

		$dtdLocation = substr($localeFilePath, 0, 3) == 'lib' ? '../../dtd/locale.dtd' : '../../lib/pkp/dtd/locale.dtd';

		fwrite($fp,
					"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
					"<!DOCTYPE locale SYSTEM \"$dtdLocation\">\n\n" .
					"<!--\n" .
					"  * $localeFile\n" .
					"  *\n" .
					"  * Copyright (c) 2013-2015 Simon Fraser University Library\n" .
					"  * Copyright (c) 2003-2015 John Willinsky\n" .
					"  * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.\n" .
					"  *\n" .
					sprintf("  * Localization strings for the %s (%s) locale.\n", $this->outLocale, DEFAULT_OUT_LOCALE_NAME) .
					"  *\n" .
					"  -->\n\n" .
					sprintf("<locale name=\"%s\" full_name=\"%s\">\n", $this->outLocale, DEFAULT_OUT_LOCALE_NAME)
		);

		foreach ($localeData as $key => $message) {
			$outMessage = $this->fancifyString($message);

			if (strstr($outMessage, '<') || strstr($outMessage, '>')) {
				$outMessage = '<![CDATA[' . $outMessage . ']]>';
			}

			fwrite($fp, sprintf("\t<message key=\"%s\">%s</message>\n", $key, $outMessage));
		}

		fwrite($fp, "</locale>\n");

		fclose($fp);
	}

	/**
	 * Perform message string munging.
	 * @param $str string
	 * @return string
	 */
	function fancifyString($str) {
		$inHTML = 0;
		$inVar = 0;

		$outStr = "";

		for ($i = 0, $len = strlen($str); $i < $len; $i++) {
			switch ($str[$i]) {
				case '{':
					$inVar++;
					break;
				case '}':
					$inVar--;
					break;
				case '<':
					$inHTML++;
					break;
				case '>':
					$inHTML--;
					break;
			}

			if ($inHTML == 0 && $inVar == 0) {
				$outStr .= strtr($str[$i], $this->replaceMap);
			} else {
				$outStr .= $str[$i];
			}
		}

		return $outStr;
	}

}

$tool = new genTestLocale(isset($argv) ? $argv : array());
$tool->execute();

?>
