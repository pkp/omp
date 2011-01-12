<?php

/**
 * @file classes/press/PressSettingsDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSettingsDAO
 * @ingroup press
 *
 * @brief Operations for retrieving and modifying press settings.
 */



class PressSettingsDAO extends DAO {
	function &_getCache($pressId) {
		static $settingCache;
		if (!isset($settingCache)) {
			$settingCache = array();
		}
		if (!isset($settingCache[$pressId])) {
			$cacheManager =& CacheManager::getManager();
			$settingCache[$pressId] = $cacheManager->getCache(
				'pressSettings', $pressId,
				array($this, '_cacheMiss')
			);
		}
		return $settingCache[$pressId];
	}

	/**
	 * Retrieve a press setting value.
	 * @param $pressId int
	 * @param $name string
	 * @param $locale string optional
	 * @return mixed
	 */
	function &getSetting($pressId, $name, $locale = null) {
		$cache =& $this->_getCache($pressId);
		$returner = $cache->get($name);
		if ($locale !== null) {
			if (!isset($returner[$locale]) || !is_array($returner)) {
				unset($returner);
				$returner = null;
				return $returner;
			}
			return $returner[$locale];
		}
		return $returner;
	}

	function _cacheMiss(&$cache, $id) {
		$settings =& $this->getPressSettings($cache->getCacheId());
		if (!isset($settings[$id])) {
			$cache->setCache($id, null);
			return null;
		}
		return $settings[$id];
	}

	/**
	 * Retrieve and cache all settings for a press.
	 * @param $pressId int
	 * @return array
	 */
	function &getPressSettings($pressId) {
		$pressSettings = array();

		$result =& $this->retrieve(
			'SELECT setting_name, setting_value, setting_type, locale FROM press_settings WHERE press_id = ?', $pressId
		);

		while (!$result->EOF) {
			$row =& $result->getRowAssoc(false);
			$value = $this->convertFromDB($row['setting_value'], $row['setting_type']);
			if ($row['locale'] == '') $pressSettings[$row['setting_name']] = $value;
			else $pressSettings[$row['setting_name']][$row['locale']] = $value;
			$result->MoveNext();
		}
		$result->Close();
		unset($result);

		$cache =& $this->_getCache($pressId);
		$cache->setEntireCache($pressSettings);

		return $pressSettings;
	}

	/**
	 * Add/update a press setting.
	 * @param $pressId int
	 * @param $name string
	 * @param $value mixed
	 * @param $type string data type of the setting. If omitted, type will be guessed
	 * @param $isLocalized boolean
	 */
	function updateSetting($pressId, $name, $value, $type = null, $isLocalized = false) {
		$cache =& $this->_getCache($pressId);
		$cache->setCache($name, $value);

		$keyFields = array('setting_name', 'locale', 'press_id');
		
		if (!$isLocalized) {
			$value = $this->convertToDB($value, $type);
			$this->replace('press_settings',
				array(
					'press_id' => $pressId,
					'setting_name' => $name,
					'setting_value' => $value,
					'setting_type' => $type,
					'locale' => ''
				),
				$keyFields
			);
		} else {
			if (is_array($value)) foreach ($value as $locale => $localeValue) {
				$this->update('DELETE FROM press_settings WHERE press_id = ? AND setting_name = ? AND locale = ?', array($pressId, $name, $locale));
				if (empty($localeValue)) continue;
				$type = null;
				$this->update('INSERT INTO press_settings
					(press_id, setting_name, setting_value, setting_type, locale)
					VALUES (?, ?, ?, ?, ?)',
					array(
						$pressId, $name, $this->convertToDB($localeValue, $type), $type, $locale
					)
				);
			}
		}
	}

	/**
	 * Delete a press setting.
	 * @param $pressId int
	 * @param $name string
	 */
	function deleteSetting($pressId, $name, $locale = null) {
		$cache =& $this->_getCache($pressId);
		$cache->setCache($name, null);

		$params = array($pressId, $name);
		$sql = 'DELETE FROM press_settings WHERE press_id = ? AND setting_name = ?';
		if ($locale !== null) {
			$params[] = $locale;
			$sql .= ' AND locale = ?';
		}

		return $this->update($sql, $params);
	}

	/**
	 * Delete all settings for a press.
	 * @param $pressId int
	 */
	function deleteSettingsByPress($pressId) {
		$cache =& $this->_getCache($pressId);
		$cache->flush();

		return $this->update(
				'DELETE FROM press_settings WHERE press_id = ?', $pressId
		);
	}

	/**
	 * Used internally by installSettings to perform variable and translation replacements.
	 * @param $rawInput string contains text including variable and/or translate replacements.
	 * @param $paramArray array contains variables for replacement
	 * @returns string
	 */
	function _performReplacement($rawInput, $paramArray = array()) {
		$value = preg_replace_callback('{{translate key="([^"]+)"}}', '_installer_regexp_callback', $rawInput);
		foreach ($paramArray as $pKey => $pValue) {
			$value = str_replace('{$' . $pKey . '}', $pValue, $value);
		}
		return $value;
	}

	/**
	 * Used internally by installSettings to recursively build nested arrays.
	 * Deals with translation and variable replacement calls.
	 * @param $node object XMLNode <array> tag
	 * @param $paramArray array Parameters to be replaced in key/value contents
	 */
	function &_buildObject (&$node, $paramArray = array()) {
		$value = array();
		foreach ($node->getChildren() as $element) {
			$key = $element->getAttribute('key');
			$childArray =& $element->getChildByName('array');
			if (isset($childArray)) {
				$content = $this->_buildObject($childArray, $paramArray);
			} else {
				$content = $this->_performReplacement($element->getValue(), $paramArray);
			}
			if (!empty($key)) {
				$key = $this->_performReplacement($key, $paramArray);
				$value[$key] = $content;
			} else $value[] = $content;
		}
		return $value;
	}

	/**
	 * Install press settings from an XML file.
	 * @param $pressId int ID of press for settings to apply to
	 * @param $filename string Name of XML file to parse and install
	 * @param $paramArray array Optional parameters for variable replacement in settings
	 */
	function installSettings($pressId, $filename, $paramArray = array()) {
		$xmlParser = new XMLParser();
		$tree = $xmlParser->parse($filename);

		if (!$tree) {
			$xmlParser->destroy();
			return false;
		}

		foreach ($tree->getChildren() as $setting) {
			$nameNode =& $setting->getChildByName('name');
			$valueNode =& $setting->getChildByName('value');

			if (isset($nameNode) && isset($valueNode)) {
				$type = $setting->getAttribute('type');
				$isLocaleField = $setting->getAttribute('locale');
				$name =& $nameNode->getValue();

				if ($type == 'object') {
					$arrayNode =& $valueNode->getChildByName('array');
					$value = $this->_buildObject($arrayNode, $paramArray);
				} else {
					$value = $this->_performReplacement($valueNode->getValue(), $paramArray);
				}

				// Replace translate calls with translated content
				$this->updateSetting(
					$pressId,
					$name,
					$isLocaleField?array(Locale::getLocale() => $value):$value,
					$type,
					$isLocaleField
				);
			}
		}

		$xmlParser->destroy();

	}

	/**
	 * Used internally by reloadLocalizedSettingDefaults to perform variable and translation replacements.
	 * @param $rawInput string contains text including variable and/or translate replacements.
	 * @param $paramArray array contains variables for replacement
	 * @param $locale string contains the name of the locale that should be used for the translation
	 * @returns string
	 */
	function _performLocalizedReplacement($rawInput, $paramArray = array(), $locale = null) {
		$value = preg_replace_callback('{{translate key="([^"]+)"}}',
		// this only translates from mail locale file 
		create_function('$matches', 
		'$locale = "' . $locale . '";'.'$localeFileName = Locale::getMainLocaleFilename($locale);'.
		'$localeFile = new LocaleFile($locale, $localeFileName);'.'return $localeFile->translate($matches[1]);'),$rawInput); 
		foreach ($paramArray as $pKey => $pValue) {
			$value = str_replace('{$' . $pKey . '}', $pValue, $value);
		}
		return $value;
	}

	/**
	 * Used internally by reloadLocalizedSettingDefaults to recursively build nested arrays.
	 * Deals with translation and variable replacement calls.
	 * @param $node object XMLNode <array> tag
	 * @param $paramArray array Parameters to be replaced in key/value contents
	 * @param $locale string contains the name of the locale that should be used for the translation
	 */
	function &_buildLocalizedObject (&$node, $paramArray = array(), $locale = null) {
		$value = array();
		foreach ($node->getChildren() as $element) {
			$key = $element->getAttribute('key');
			$childArray =& $element->getChildByName('array');
			if (isset($childArray)) {
				$content = $this->_buildLocalizedObject($childArray, $paramArray, $locale);
			} else {
				$content = $this->_performLocalizedReplacement($element->getValue(), $paramArray, $locale);
			}
			if (!empty($key)) {
				$key = $this->_performLocalizedReplacement($key, $paramArray, $locale);
				$value[$key] = $content;
			} else $value[] = $content;
		}
		return $value;
	}

	/**
	 * Reload a default press setting from an XML file.
	 * @param $pressId int ID of press for settings to apply to
	 * @param $filename string Name of XML file to parse and install
	 * @param $settingName string Name of the setting that is to be reloaded
	 * @param $paramArray array Optional parameters for variable replacement in settings
	 */
	function reloadDefaultSetting($pressId, $filename, $settingName, $paramArray) {
		$xmlParser = new XMLParser();
		$tree = $xmlParser->parse($filename);

		if (!$tree) {
			$xmlParser->destroy();
			return false;
		}

		foreach ($tree->getChildren() as $setting) {
			$nameNode =& $setting->getChildByName('name');
			$valueNode =& $setting->getChildByName('value');

			if (isset($nameNode) && isset($valueNode)) {

				if ($nameNode->getValue() == $settingName) {
					$type = $setting->getAttribute('type');
					$isLocaleField = $setting->getAttribute('locale');
					$name =& $nameNode->getValue();

					if ($type == 'object') {
						$arrayNode =& $valueNode->getChildByName('array');
						$value = $this->_buildObject($arrayNode, $paramArray);
					} else {
						$value = $this->_performReplacement($valueNode->getValue(), $paramArray);
					}

					$this->updateSetting(
						$pressId,
						$name,
						$isLocaleField?array(Locale::getLocale() => $value):$value,
						$type,
						$isLocaleField
					);

					$xmlParser->destroy();
					return true;
				}
			}
		}

		$xmlParser->destroy();

	}

	/**
	 * Install locale field Only press settings from an XML file.
	 * @param $pressId int ID of press for settings to apply to
	 * @param $filename string Name of XML file to parse and install
	 * @param $paramArray array Optional parameters for variable replacement in settings
	 * @param $locale string locale id for which settings will be loaded
	 */
	function reloadLocalizedDefaultSettings($pressId, $filename, $paramArray, $locale) {
		$xmlParser = new XMLParser();
		$tree = $xmlParser->parse($filename);

		if (!$tree) {
			$xmlParser->destroy();
			return false;
		}

		foreach ($tree->getChildren() as $setting) {
			$nameNode =& $setting->getChildByName('name');
			$valueNode =& $setting->getChildByName('value');

			if (isset($nameNode) && isset($valueNode)) {
				$type = $setting->getAttribute('type');
				$isLocaleField = $setting->getAttribute('locale');
				$name =& $nameNode->getValue();

				//skip all settings that are not locale fields
				if (!$isLocaleField) continue;

				if ($type == 'object') {
					$arrayNode =& $valueNode->getChildByName('array');
					$value = $this->_buildLocalizedObject($arrayNode, $paramArray, $locale);
				} else {
					$value = $this->_performLocalizedReplacement($valueNode->getValue(), $paramArray, $locale);
				}
				
				// Replace translate calls with translated content
				$this->updateSetting(
					$pressId,
					$name,
					array($locale => $value),
					$type,
					true
				);
			}
		}

		$xmlParser->destroy();

	}
}

/**
 * Used internally by press setting installation code to perform translation function.
 */
function _installer_regexp_callback($matches) {
	return Locale::translate($matches[1]);
}

?>
