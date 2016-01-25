<?php

/**
 * @file classes/plugins/PubIdPlugin.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PubIdPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for public identifiers plugins
 */

import('lib.pkp.classes.plugins.Plugin');

abstract class PubIdPlugin extends Plugin {

	/**
	 * Constructor
	 */
	function PubIdPlugin() {
		parent::Plugin();
	}


	//
	// Implement template methods from PKPPlugin
	//
	/**
	 * @copydoc PKPPlugin::register()
	 */
	function register($category, $path) {
		if (!parent::register($category, $path)) return false;

		// Enable storage of additional fields.
		foreach($this->_getDAOs() as $daoName) {
			HookRegistry::register(strtolower_codesafe($daoName).'::getAdditionalFieldNames', array($this, 'getAdditionalFieldNames'));
		}
		return true;
	}

 	/**
	 * @copydoc PKPPlugin::manage()
	 */
	function manage($args, $request) {
		$notificationManager = new NotificationManager();
	 	$user = $request->getUser();
	 	$press = $request->getPress();
 
		$settingsFormName = $this->getSettingsFormName();
		$settingsFormNameParts = explode('.', $settingsFormName);
		$settingsFormClassName = array_pop($settingsFormNameParts);
		$this->import($settingsFormName);
		$form = new $settingsFormClassName($this, $press->getId());
		if ($request->getUserVar('save')) {
			$form->readInputData();
			if ($form->validate()) {
				$form->execute();
				$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS);
				return new JSONMessage(true);
			} else {
				return new JSONMessage(true, $form->fetch($request));
			}
		} elseif ($request->getUserVar('clearPubIds')) {
			$pressDao = DAORegistry::getDAO('PressDAO');
			$pressDao->deleteAllPubIds($press->getId(), $this->getPubIdType());
			$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS);
			return new JSONMessage(true);
		} else {
			$form->initData();
			return new JSONMessage(true, $form->fetch($request));
		}
	}


	//
	// Protected template methods to be implemented by sub-classes.
	//
	/**
	 * Get the public identifier.
	 * @param $pubObject object
	 *  (PublicationFormat)
	 * @param $preview boolean
	 *  when true, the public identifier will not be stored
	 * @return string
	 */
	abstract function getPubId($pubObject, $preview = false);

	/**
	 * Public identifier type, see
	 * http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html
	 * @return string
	 */
	abstract function getPubIdType();

	/**
	 * Public identifier type that will be displayed to the reader.
	 * @return string
	 */
	abstract function getPubIdDisplayType();

	/**
	 * Full name of the public identifier.
	 * @return string
	 */
	abstract function getPubIdFullName();

	/**
	 * Get the whole resolving URL.
	 * @param $pressId int
	 * @param $pubId string
	 * @return string resolving URL
	 */
	abstract function getResolvingURL($pressId, $pubId);

	/**
	 * Get the file (path + filename)
	 * to be included into the object's
	 * metadata pages.
	 * @return string
	 */
	abstract function getPubIdMetadataFile();

	/**
	 * Get the class name of the settings form.
	 * @return string
	 */
	abstract function getSettingsFormName();

	/**
	 * Verify form data.
	 * @param $fieldName string The form field to be checked.
	 * @param $fieldValue string The value of the form field.
	 * @param $pubObject object
	 * @param $monographId integer
	 * @param $errorMsg string Return validation error messages here.
	 * @return boolean
	 */
	abstract function verifyData($fieldName, $fieldValue, &$pubObject, $monographId, &$errorMsg);

	/**
	 * Check whether the given pubId is valid.
	 * @param $pubId string
	 * @return boolean
	 */
	function validatePubId($pubId) {
		return true; // Assume a valid ID by default;
	}

	/**
	 * Get the additional form field names.
	 * @return array
	 */
	abstract function getFormFieldNames();

	/**
	 * Get additional field names to be considered for storage.
	 * @return array
	 */
	abstract function getDAOFieldNames();

	/**
	 * @copydoc Plugin::getActions()
	 */
	function getActions($request, $actionArgs) {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, $actionArgs),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			):array(),
			parent::getActions($request, $actionArgs)
		);
	}


	//
	// Public API
	//
	/**
	 * Check for duplicate public identifiers.
	 * @param $pubId string
	 * @param $pubObject object
	 * @param $pressId integer
	 * @return boolean
	 */
	function checkDuplicate($pubId, &$pubObject, $pressId) {
		// FIXME: Hack to ensure that we get a published submission if possible.
		// Remove this when we have migrated getBest...(), etc. to Submission.
		if (is_a($pubObject, 'PublicationFormat')) {
			$publicationFormatDao = DAORegistry::getDAO('PublishedFormatDAO'); /* @var $publicationFormatDao PublishedFormatDAO */
			$format = $publicationFormatDao->getById($pubObject->getId());
			if (is_a($format, 'PublicationFormat')) {
				unset($pubObject);
				$pubObject =& $format;
			}
		}

		// Check all objects of the press whether they have
		// the same pubId. This includes pubIds that are not yet generated
		// but could be generated at any moment if someone accessed
		// the object publicly. We have to check "real" pubIds rather than
		// the pubId suffixes only as a pubId with the given suffix may exist
		// (e.g. through import) even if the suffix itself is not in the
		// database.
		$typesToCheck = array('PublicationFormat');
		foreach($typesToCheck as $pubObjectType) {
			switch($pubObjectType) {
				case 'PublicationFormat':
					// FIXME: We temporarily have to use the published submission
					// DAO here until we've moved pubId-generation to the submission
					// class.
					$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDao'); /* @var $monographDao PublishedMonographDAO */
					$objectsToCheck = $publicationFormatDao->getByPressId($pressId);
					break;
				default:
					$objectsToCheck = null; // Suppress warn
					assert(false);
			}

			$excludedId = (is_a($pubObject, $pubObjectType) ? $pubObject->getId() : null);
			while ($objectToCheck = $objectsToCheck->next()) {
				// The publication object for which the new pubId
				// should be admissible is to be ignored. Otherwise
				// we might get false positives by checking against
				// a pubId that we're about to change anyway.
				if ($objectToCheck->getId() == $excludedId) continue;

				// Check for ID clashes.
				$existingPubId = $this->getPubId($objectToCheck, true);
				if ($pubId == $existingPubId) return false;
			}

			unset($objectsToCheck);
		}

		// We did not find any ID collision, so go ahead.
		return true;
	}

	/**
	 * Add the suffix element and the public identifier
	 * to the object (submission, galley).
	 * @param $hookName string
	 * @param $params array ()
	 */
	function getAdditionalFieldNames($hookName, $params) {
		$fields =& $params[1];
		$formFieldNames = $this->getFormFieldNames();
		foreach ($formFieldNames as $formFieldName) {
			$fields[] = $formFieldName;
		}
		$daoFieldNames = $this->getDAOFieldNames();
		foreach ($daoFieldNames as $daoFieldName) {
			$fields[] = $daoFieldName;
		}
		return false;
	}

	/**
	 * Return the object type.
	 * @param $pubObject object
	 *  (PublicationFormat)
	 * @return array
	 */
	function getPubObjectType($pubObject) {
		$allowedTypes = array(
			'PublicationFormat' => 'PublicationFormat',
			'PublishedMonograph' => 'PublishedMonograph',
		);
		$pubObjectType = null;
		foreach ($allowedTypes as $allowedType => $pubObjectTypeCandidate) {
			if (is_a($pubObject, $allowedType)) {
				$pubObjectType = $pubObjectTypeCandidate;
				break;
			}
		}
		if (is_null($pubObjectType)) {
			// This must be a dev error, so bail with an assertion.
			assert(false);
			return null;
		}
		return $pubObjectType;
	}

	/**
	 * Set and store a public identifier.
	 * @param $pubObject PublicationFormat
	 * @param $pubObjectType string As returned from self::getPubObjectType()
	 * @param $pubId string
	 * @return string
	 */
	function setStoredPubId(&$pubObject, $pubObjectType, $pubId) {
		$dao =& $this->getDAO($pubObjectType);
		$dao->changePubId($pubObject->getId(), $this->getPubIdType(), $pubId);
		$pubObject->setStoredPubId($this->getPubIdType(), $pubId);
	}

	/**
	 * Return the name of the corresponding DAO.
	 * @param $pubObject object
	 * @return DAO
	 */
	function &getDAO($pubObjectType) {
		$daos =  array(
			'PublicationFormat' => 'PublicationFormatDAO',
			'Monograph' => 'MonographDAO',
		);
		$daoName = $daos[$pubObjectType];
		assert(!empty($daoName));
		return DAORegistry::getDAO($daoName);
	}


	//
	// Private helper methods
	//
	/**
	 * Return an array of the corresponding DAOs.
	 * @return array
	 */
	function _getDAOs() {
		return array('PublicationFormatDAO');
	}
}

?>
