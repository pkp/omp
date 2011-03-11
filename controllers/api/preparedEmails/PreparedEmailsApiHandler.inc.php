<?php
/**
 * @defgroup controllers_api_file
 */

/**
 * @file controllers/api/preparedEmails/PreparedEmailsApiHandler.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PreparedEmailsApiHandler
 * @ingroup controllers_api_preparedEmails
 *
 * @brief Class defining an AJAX API for prepared email template manipulation.
 */

// Import the base Handler.
import('classes.handler.Handler');

class PreparedEmailsApiHandler extends Handler {

	/**
	 * Constructor.
	 */
	function PreparedEmailsApiHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER),
			array('resetEmail', 'resetAllEmails', 'disableEmail', 'enableEmail', 'deleteCustomEmail')
		);
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler methods
	//
	/**
	 * Reset a single email
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function resetEmail($args, &$request) {
		$emailKey = $request->getUserVar('emailKey');
		assert(is_string($emailKey));

		$press =& $request->getPress();

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
		if ($emailTemplateDao->templateExistsByKey($emailKey, $press->getId())) {
			$emailTemplateDao->deleteEmailTemplateByKey($emailKey, $press->getId());
			return DAO::getDataChangedEvent($emailKey);
		} else {
			$json = new JSON(false);
			return $json->getString();
		}
	}

	/**
	 * Reset all email to stock.
	 * @param $args array
	 * @param $request Request
	 */
	function resetAllEmails($args, &$request) {
		$press =& $request->getPress();
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
		$emailTemplateDao->deleteEmailTemplatesByPress($press->getId());
		return DAO::getDataChangedEvent();
	}

	/**
	 * Disables an email template.
	 * @param $args array
	 * @param $request Request
	 */
	function disableEmail($args, &$request) {
		$emailKey = $request->getUserVar('emailKey');
		assert(is_string($emailKey));

		$press =& $request->getPress();

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
		$emailTemplate = $emailTemplateDao->getBaseEmailTemplate($emailKey, $press->getId());

		if (isset($emailTemplate)) {
			if ($emailTemplate->getCanDisable()) {
				$emailTemplate->setEnabled(0);

				if ($emailTemplate->getAssocId() == null) {
					$emailTemplate->setAssocId($press->getId());
					$emailTemplate->setAssocType(ASSOC_TYPE_PRESS);
				}

				if ($emailTemplate->getEmailId() != null) {
					$emailTemplateDao->updateBaseEmailTemplate($emailTemplate);
				} else {
					$emailTemplateDao->insertBaseEmailTemplate($emailTemplate);
				}

				return DAO::getDataChangedEvent($emailKey);
			}
		} else {
			$json = new JSON(false);
			return $json->getString();
		}
	}


	/**
	 * Enables an email template.
	 * @param $args array
	 * @param $request Request
	 */
	function enableEmail($args, &$request) {
		$emailKey = $request->getUserVar('emailKey');
		assert(is_string($emailKey));

		$press =& $request->getPress();

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
		$emailTemplate = $emailTemplateDao->getBaseEmailTemplate($emailKey, $press->getId());

		if (isset($emailTemplate)) {
			if ($emailTemplate->getCanDisable()) {
				$emailTemplate->setEnabled(1);

				if ($emailTemplate->getEmailId() != null) {
					$emailTemplateDao->updateBaseEmailTemplate($emailTemplate);
				} else {
					$emailTemplateDao->insertBaseEmailTemplate($emailTemplate);
				}

				return DAO::getDataChangedEvent($emailKey);
			}
		} else {
			$json = new JSON(false);
			return $json->getString();
		}
	}

	/**
	 * Delete a custom email.
	 * @param $args array
	 * @param $request Request
	 */
	function deleteCustomEmail($args, &$request) {
		$emailKey = $request->getUserVar('emailKey');
		$press =& $request->getPress();

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
		if ($emailTemplateDao->customTemplateExistsByKey($emailKey, $press->getId())) {
			$emailTemplateDao->deleteEmailTemplateByKey($emailKey, $press->getId());
			return DAO::getDataChangedEvent($emailKey);
		} else {
			$json = new JSON(false);
			return $json->getString();
		}
	}



}
?>