<?php

/**
 * @file plugins/pubIds/doi/DOIPubIdPlugin.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DOIPubIdPlugin
 * @ingroup plugins_pubIds_doi
 *
 * @brief DOI plugin class
 */


import('classes.plugins.PubIdPlugin');

class DOIPubIdPlugin extends PubIdPlugin {

	//
	// Implement template methods from PKPPlugin.
	//
	/**
	 * @see PubIdPlugin::register()
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	/**
	 * @see PKPPlugin::getName()
	 */
	function getName() {
		return 'DOIPubIdPlugin';
	}

	/**
	 * @see PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.pubIds.doi.displayName');
	}

	/**
	 * @see PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.pubIds.doi.description');
	}

	/**
	 * @see Plugin::getTemplatePath($inCore)
	 */
	function getTemplatePath($inCore = false) {
		return parent::getTemplatePath($inCore) . 'templates/';
	}

	/**
	 * Define management link actions for the settings verb.
	 * @return LinkAction
	 */
	function getManagementVerbLinkAction($request, $verb) {
		$router = $request->getRouter();

		list($verbName, $verbLocalized) = $verb;

		if ($verbName === 'settings') {
			import('lib.pkp.classes.linkAction.request.AjaxLegacyPluginModal');
			$actionRequest = new AjaxLegacyPluginModal(
					$router->url($request, null, null, 'plugin', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'pubIds')),
					$this->getDisplayName()
			);
			return new LinkAction($verbName, $actionRequest, $verbLocalized, null);
		}

		return null;
	}

	//
	// Implement template methods from PubIdPlugin.
	//
	/**
	 * @see PubIdPlugin::getPubId()
	 */
	function getPubId(&$pubObject, $preview = false) {
		$doi = null;
		if (!$this->isExcluded($pubObject)) {
			// Determine the type of the publishing object.
			$pubObjectType = $this->getPubObjectType($pubObject);

			// Initialize variables for publication objects.
			$publicationFormat = ($pubObjectType == 'PublicationFormat' ? $pubObject : null);
			$monograph = ($pubObjectType == 'Monograph' ? $pubObject : null);


			// Get the press id of the object.
			if (in_array($pubObjectType, array('PublicationFormat', 'Monograph'))) {
				$pressId = $pubObject->getContextId();
			} else {
				return null;
			}

			$press = $this->_getPress($pressId);
			if (!$press) return null;
			$pressId = $press->getId();

			// If we already have an assigned DOI, use it.
			$storedDOI = $pubObject->getStoredPubId('doi');
			if ($storedDOI) return $storedDOI;

			// Retrieve the DOI prefix.
			$doiPrefix = $this->getSetting($pressId, 'doiPrefix');
			if (empty($doiPrefix)) return null;

			// Generate the DOI suffix.
			$doiSuffixGenerationStrategy = $this->getSetting($pressId, 'doiSuffix');

			switch ($doiSuffixGenerationStrategy) {
				case 'customId':
					$doiSuffix = $pubObject->getData('doiSuffix');
					break;

				case 'pattern':
					$doiSuffix = $this->getSetting($pressId, "doi${pubObjectType}SuffixPattern");

					// %p - press initials
					$doiSuffix = String::regexp_replace('/%p/', String::strtolower($press->getPath()), $doiSuffix);

					if ($publicationFormat) {
						// %m - monograph id, %f - publication format id
						$doiSuffix = String::regexp_replace('/%m/', $publicationFormat->getMonographId(), $doiSuffix);
						$doiSuffix = String::regexp_replace('/%f/', $publicationFormat->getId(), $doiSuffix);
					}
					if ($monograph) {
						// %m - monograph id
						$doiSuffix = String::regexp_replace('/%m/', $monograph->getId(), $doiSuffix);
					}

					break;

				default:
					$doiSuffix = String::strtolower($press->getPath());

					if ($publicationFormat) {
						$doiSuffix .= '.' . $publicationFormat->getMonographId();
						$doiSuffix .= '.' . $publicationFormat->getId();
					}
					if ($monograph) {
						$doiSuffix .= '.' . $monograph->getId();
					}
			}
			if (empty($doiSuffix)) return null;

			// Join prefix and suffix.
			$doi = $doiPrefix . '/' . $doiSuffix;

			if (!$preview) {
				// Save the generated DOI.
				$this->setStoredPubId($pubObject, $pubObjectType, $doi);
			}
		}
		return $doi;
	}

	/**
	 * @see PubIdPlugin::getPubIdType()
	 */
	function getPubIdType() {
		return 'doi';
	}

	/**
	 * @see PubIdPlugin::getPubIdDisplayType()
	 */
	function getPubIdDisplayType() {
		return 'DOI';
	}

	/**
	 * @see PubIdPlugin::getPubIdFullName()
	 */
	function getPubIdFullName() {
		return 'Digital Object Identifier';
	}

	/**
	 * @see PubIdPlugin::getResolvingURL()
	 */
	function getResolvingURL($pressId, $pubId) {
		return 'http://dx.doi.org/'.$this->_doiURLEncode($pubId);
	}

	/**
	 * @see PubIdPlugin::getFormFieldNames()
	 */
	function getFormFieldNames() {
		return array('doiSuffix', 'excludeDoi');
	}

	/**
	 * @see PubIdPlugin::getExcludeFormFieldName()
	 */
	function getExcludeFormFieldName() {
		return 'excludeDoi';
	}

	/**
	 * @see PubIdPlugin::getDAOFieldNames()
	 */
	function getDAOFieldNames() {
		return array('pub-id::doi');
	}

	/**
	 * @see PubIdPlugin::getPubIdMetadataFile()
	 */
	function getPubIdMetadataFile() {
		return $this->getTemplatePath().'doiSuffixEdit.tpl';
	}

	/**
	 * @see PubIdPlugin::getSettingsFormName()
	 */
	function getSettingsFormName() {
		return 'classes.form.DOISettingsForm';
	}

	/**
	 * @see PubIdPlugin::verifyData()
	 */
	function verifyData($fieldName, $fieldValue, &$pubObject, $pressId, &$errorMsg) {
		// Verify DOI uniqueness.
		if ($fieldName == 'doiSuffix') {
			if (empty($fieldValue)) return true;

			// Construct the potential new DOI with the posted suffix.
			$doiPrefix = $this->getSetting($pressId, 'doiPrefix');
			if (empty($doiPrefix)) return true;
			$newDoi = $doiPrefix . '/' . $fieldValue;

			if($this->checkDuplicate($newDoi, $pubObject, $pressId)) {
				return true;
			} else {
				$errorMsg = __('plugins.pubIds.doi.editor.doiSuffixCustomIdentifierNotUnique');
				return false;
			}
		}
		return true;
	}

	/**
	 * @see PubIdPlugin::validatePubId()
	 */
	function validatePubId($pubId) {
		return preg_match('/^\d+(.\d+)+\//', $pubId);
	}


	//
	// Private helper methods
	//
	/**
	 * Get the press object.
	 * @param $pressId integer
	 * @return Press
	 */
	function &_getPress($pressId) {
		assert(is_numeric($pressId));

		// Get the press object from the context (optimized).
		$request = $this->getRequest();
		$router = $request->getRouter();
		$press = $router->getContext($request); /* @var $press Press */

		// Check whether we still have to retrieve the press from the database.
		if (!$press || $press->getId() != $pressId) {
			unset($press);
			$pressDao = DAORegistry::getDAO('PressDAO');
			$press = $pressDao->getById($pressId);
		}

		return $press;
	}

	/**
	 * Encode DOI according to ANSI/NISO Z39.84-2005, Appendix E.
	 * @param $pubId string
	 * @return string
	 */
	function _doiURLEncode($pubId) {
		$search = array ('%', '"', '#', ' ', '<', '>', '{');
		$replace = array ('%25', '%22', '%23', '%20', '%3c', '%3e', '%7b');
		$pubId = str_replace($search, $replace, $pubId);
		return $pubId;
	}

}

?>
