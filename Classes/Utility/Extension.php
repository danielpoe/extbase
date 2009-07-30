<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Jochen Rau <jochen.rau@typoplanet.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Utilities to manage plugins and  modules of an extension. Also useful to auto-generate the autoloader registry 
 * file ext_autoload.php.
 *
 * @package Extbase
 * @subpackage Utility
 * @version $ID:$
 * @author Dmitry Dulepov <dmitry@typo3.org>
 * @author Sebastian Kurf�rst <sebastian@typo3.org>
 * @author Jochen Rau <jochen.rau@typoplanet.de>
 */
class Tx_Extbase_Utility_Extension {

	/**
	 * Build the autoload registry for a given extension and place it ext_autoload.php.
	 *
	 * @param	string	$extensionKey	Key of the extension
	 * @param	string	$extensionPath	full path of the extension
	 * @return	string	HTML string which should be outputted
	 */
	public function createAutoloadRegistryForExtension($extensionKey, $extensionPath) {
		$classNameToFileMapping = array();
		$extensionName = str_replace(' ', '', ucwords(str_replace('_', ' ', $extensionKey)));
		$errors = $this->buildAutoloadRegistryForSinglePath($classNameToFileMapping, $extensionPath . 'Classes/', '.*tslib.*', '$extensionClassesPath . \'|\'');
		if ($errors) {
			return $errors;
		}
		$globalPrefix = '$extensionClassesPath = t3lib_extMgm::extPath(\'' . $extensionKey . '\') . \'Classes/\';';

		$errors = array();
		foreach ($classNameToFileMapping as $className => $fileName) {
			if (!(strpos($className, 'tx_' . strtolower($extensionName)) === 0)) {
				$errors[] = $className . ' does not start with Tx_' . $extensionName . ' and was not added to the autoloader registry.';
				unset($classNameToFileMapping[$className]);
			}
		}
		$autoloadFileString = $this->generateAutoloadPHPFileData($classNameToFileMapping, $globalPrefix);
		if (!@file_put_contents($extensionPath . 'ext_autoload.php', $autoloadFileString)) {
			$errors[] = '<b>' . $extensionPath . 'ext_autoload.php could not be written!</b>';
		}
		$errors[] = 'Wrote the following data: <pre>' . htmlspecialchars($autoloadFileString) . '</pre>';
		return implode('<br />', $errors);
	}

	/**
	 * Generate autoload PHP file data. Takes an associative array with class name to file mapping, and outputs it as PHP.
	 * Does NOT escape the values in the associative array. Includes the <?php ... ?> syntax and an optional global prefix.
	 *
	 * @param	array	$classNameToFileMapping class name to file mapping
	 * @param	string	$globalPrefix	Global prefix which is prepended to all code.
	 * @return	string	The full PHP string
	 */
	protected function generateAutoloadPHPFileData($classNameToFileMapping, $globalPrefix = '') {
		$output = '<?php' . PHP_EOL;
		$output .= '// DO NOT CHANGE THIS FILE! It is automatically generated by Tx_Extbase_Utility_Extension::createAutoloadRegistryForExtension.' . PHP_EOL;
		$output .= '// This file was generated on ' . date('Y-m-d H:i') . PHP_EOL;
		$output .= PHP_EOL;
		$output .= $globalPrefix . PHP_EOL;
		$output .= 'return array(' . PHP_EOL;
		foreach ($classNameToFileMapping as $className => $quotedFileName) {
			$output .= '	\'' . $className . '\' => ' . $quotedFileName . ',' . PHP_EOL;
		}
		$output .= ');' . PHP_EOL;
		$output .= '?>';
		return $output;
	}

	/**
	 * Generate the $classNameToFileMapping for a given filePath.
	 *
	 * @param	array	$classNameToFileMapping	(Reference to array) All values are appended to this array.
	 * @param	string	$path	Path which should be crawled
	 * @param	string	$excludeRegularExpression	Exclude regular expression, to exclude certain files from being processed
	 * @param	string	$valueWrap	Wrap for the file name
	 * @return void
	 */
	protected function buildAutoloadRegistryForSinglePath(&$classNameToFileMapping, $path, $excludeRegularExpression = '', $valueWrap = '\'|\'') {
//		if (file_exists($path . 'Classes/')) {
//			return "<b>This appears to be a new-style extension which has its PHP classes inside the Classes/ subdirectory. It is not needed to generate the autoload registry for these extensions.</b>";
//		}
		$extensionFileNames = t3lib_div::removePrefixPathFromList(t3lib_div::getAllFilesAndFoldersInPath(array(), $path, 'php', FALSE, 99, $excludeRegularExpression), $path);

		foreach ($extensionFileNames as $extensionFileName) {
			$classNamesInFile = $this->extractClassNames($path . $extensionFileName);
			if (!count($classNamesInFile)) continue;
			foreach ($classNamesInFile as $className) {
				$classNameToFileMapping[strtolower($className)] = str_replace('|', $extensionFileName, $valueWrap);
			}
		}
	}

	/**
	 * Extracts class names from the given file.
	 *
	 * @param	string	$filePath	File path (absolute)
	 * @return	array	Class names
	 */
	protected function extractClassNames($filePath) {
		$fileContent = php_strip_whitespace($filePath);
		$classNames = array();
		if (function_exists('token_get_all')) {
			$tokens = token_get_all($fileContent);
			while(1) {
				// look for "class" or "interface"
				$token = $this->findToken($tokens, array(T_ABSTRACT, T_CLASS, T_INTERFACE));
				// fetch "class" token if "abstract" was found
				if ($token === 'abstract') {
					$token = $this->findToken($tokens, array(T_CLASS));
				}
				if ($token === false) {
					// end of file
					break;
				}
				// look for the name (a string) skipping only whitespace and comments
				$token = $this->findToken($tokens, array(T_STRING), array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT));
				if ($token === false) {
					// unexpected end of file or token: remove found names because of parse error
					t3lib_div::sysLog('Parse error in "' . $file. '".', 'Core', 2);
					$classNames = array();
					break;
				}
				$token = t3lib_div::strtolower($token);
				// exclude XLASS classes
				if (strncmp($token, 'ux_', 3)) {
					$classNames[] = $token;
				}
			}
		} else {
			// TODO: parse PHP - skip coments and strings, apply regexp only on the remaining PHP code
			$matches = array();
			preg_match_all('/^[ \t]*(?:(?:abstract|final)?[ \t]*(?:class|interface))[ \t\n\r]+([a-zA-Z][a-zA-Z_0-9]*)/mS', $fileContent, $matches);
			$classNames = array_map('t3lib_div::strtolower', $matches[1]);
		}
		return $classNames;
	}

	/**
	 * Find tokens in the tokenList
	 *
	 * @param	array	$tokenList	list of tokens as returned by token_get_all()
	 * @param	array	$wantedToken	the tokens to be found
	 * @param	array	$intermediateTokens	optional: list of tokens that are allowed to skip when looking for the wanted token
	 * @return	mixed
	 */
	protected function findToken(array &$tokenList, array $wantedTokens, array $intermediateTokens = array()) {
		$skipAllTokens = count($intermediateTokens) ? false : true;

		$returnValue = false;
		// Iterate with while since we need the current array position:
		while (list(,$token) = each($tokenList)) {
			// parse token (see http://www.php.net/manual/en/function.token-get-all.php for format of token list)
			if (is_array($token)) {
				list($id, $text) = $token;
			} else {
				$id = $text = $token;
			}
			if (in_array($id, $wantedTokens)) {
				$returnValue = $text;
				break;
			}
			// look for another token
			if ($skipAllTokens || in_array($id, $intermediateTokens)) {
				continue;
			}
			break;
		}
		return $returnValue;
	}

}
?>