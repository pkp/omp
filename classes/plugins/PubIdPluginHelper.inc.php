<?php

/**
 * @file classes/plugins/PubIdPluginHelper.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PubIdPluginHelper
 * @ingroup plugins
 *
 * @brief Helper class for public identifiers plugins
 */


class PubIdPluginHelper {

	/**
	 * Validate the additional form fields from public identifier plugins.
	 * @param $pressId int
	 * @param $form object CatalogEntryFormatMetadataForm
	 * @param $pubObject object A PublicationFormat object
	 */
	function validate($pressId, &$form, &$pubObject) {
		$pubIdPlugins =& PluginRegistry::loadCategory('pubIds', true);
		if (is_array($pubIdPlugins)) {
			foreach ($pubIdPlugins as $pubIdPlugin) {
				$fieldNames = $pubIdPlugin->getFormFieldNames();
				foreach ($fieldNames as $fieldName) {
					$fieldValue = $form->getData($fieldName);
					$errorMsg = '';
					if(!$pubIdPlugin->verifyData($fieldName, $fieldValue, $pubObject, $pressId, $errorMsg)) {
						$form->addError($fieldName, $errorMsg);
					}
				}
			}
		}
	}

	/**
	 * Init the additional form fields from public identifier plugins.
	 * @param $form object CatalogEntryFormatMetadataForm
	 * @param $pubObject object A PublicationFormat object
	 */
	function init(&$form, &$pubObject) {
		$pubIdPlugins =& PluginRegistry::loadCategory('pubIds', true);
		if (is_array($pubIdPlugins)) {
			foreach ($pubIdPlugins as $pubIdPlugin) {
				$fieldNames = $pubIdPlugin->getFormFieldNames();
				foreach ($fieldNames as $fieldName) {
					$form->setData($fieldName, $pubObject->getData($fieldName));
				}
			}
		}
	}

	/**
	 * Read the additional input data from public identifier plugins.
	 * @param $form object CatalogEntryFormatMetadataForm
	 */
	function readInputData(&$form) {
		$pubIdPlugins =& PluginRegistry::loadCategory('pubIds', true);
		if (is_array($pubIdPlugins)) {
			foreach ($pubIdPlugins as $pubIdPlugin) {
				$form->readUserVars($pubIdPlugin->getFormFieldNames());
			}
		}
	}

	/**
	 * Set the additional data from public identifier plugins.
	 * @param $form object CatalogEntryFormatMetadataForm
	 * @param $pubObject object A PublicationFormat object
	 */
	function execute(&$form, &$pubObject) {
		$pubIdPlugins =& PluginRegistry::loadCategory('pubIds', true);
		if (is_array($pubIdPlugins)) {
			foreach ($pubIdPlugins as $pubIdPlugin) {
				// Public ID data can only be changed as long
				// as no ID has been generated.
				$storedId = $pubObject->getStoredPubId($pubIdPlugin->getPubIdType());
				if (empty($storedId)) {
					$fieldNames = $pubIdPlugin->getFormFieldNames();
					foreach ($fieldNames as $fieldName) {
						$data = $form->getData($fieldName);
						$pubObject->setData($fieldName, $data);
					}
				}
			}
		}
	}
}
?>
