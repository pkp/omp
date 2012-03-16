<?php
/**
 * @file controllers/grid/content/navigation/form/SocialMediaForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SocialMediaForm
 * @ingroup controllers_grid_content_navigation_form
 *
 * @brief Form for reading/creating/editing social media navigation items.
 */


import('lib.pkp.classes.form.Form');

class SocialMediaForm extends Form {
	/**
	 * @var SocialMedia
	 */
	var $_socialMedia;

	/**
	 * @var int
	 */
	var $_pressId;

	/**
	 * Constructor
	 * @param $pressId int
	 * @param $socialMediaId int
	 */
	function SocialMediaForm($pressId, $socialMedia = null) {
		parent::Form('controllers/grid/content/navigation/form/socialMediaForm.tpl');

		$this->_socialMedia = $socialMedia;
		$this->_pressId = $pressId;

		$this->addCheck(new FormValidator($this, 'platform', 'required', 'grid.content.navigation.socialMedia.platformRequired'));
		$this->addCheck(new FormValidator($this, 'code', 'required', 'grid.content.navigation.socialMedia.codeRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Extended methods from Form
	//
	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();

		$socialMediaDao =& DAORegistry::getDAO('SocialMediaDAO');
		$socialMedia =& $this->getSocialMedia();
		$templateMgr->assign_by_ref('socialMedia', $socialMedia);
		$templateMgr->assign('pressId', $this->getPressId());

		if (isset($socialMedia)) {
			$templateMgr->assign('platform', $socialMedia->getPlatform(null));
			$templateMgr->assign('code', $socialMedia->getCode());
		}

		return parent::fetch($request);
	}

	//
	// Extended methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('platform', 'code'));
	}

	/**
	 * @see Form::execute()
	 */
	function execute(&$request) {

		$socialMediaDao =& DAORegistry::getDAO('SocialMediaDAO');
		$socialMedia =& $this->getSocialMedia();

		if (!$socialMedia) {
			// this is a new socialMedia object
			$socialMedia = $socialMediaDao->newDataObject();
			$socialMedia->setPressId($this->getPressId());
			$existingSocialMedia = false;
		} else {
			$existingSocialMedia = true;
		}

		$socialMedia->setPlatform($this->getData('platform'), null); // localized
		$socialMedia->setCode($this->getData('code'));

		if ($existingSocialMedia) {
			$socialMediaDao->updateObject($socialMedia);
			$socialMediaId = $socialMedia->getId();
		} else {
			$socialMediaId = $socialMediaDao->insertObject($socialMedia);
		}

		return $socialMediaId;
	}


	//
	// helper methods.
	//

	/**
	 * Fetch the SocialMedia object for this form.
	 * @return int $socialMediaId
	 */
	function getSocialMedia() {
		return $this->_socialMedia;
	}

	/**
	 * Fetch the press Id for this form.
	 * @return int $pressId
	 */
	function getPressId() {
		return $this->_pressId;
	}
}
?>
