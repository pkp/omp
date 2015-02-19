<?php

/**
 * @file plugins/pubIds/doi/classes/form/DOISettingsForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DOISettingsForm
 * @ingroup plugins_pubIds_doi
 *
 * @brief Form for press managers to setup DOI plugin
 */


import('lib.pkp.classes.form.Form');

class DOISettingsForm extends Form {

	//
	// Private properties
	//
	/** @var integer */
	var $_pressId;

	/**
	 * Get the press ID.
	 * @return integer
	 */
	function _getPressId() {
		return $this->_pressId;
	}

	/** @var DOIPubIdPlugin */
	var $_plugin;

	/**
	 * Get the plugin.
	 * @return DOIPubIdPlugin
	 */
	function &_getPlugin() {
		return $this->_plugin;
	}


	//
	// Constructor
	//
	/**
	 * Constructor
	 * @param $plugin DOIPubIdPlugin
	 * @param $pressId integer
	 */
	function DOISettingsForm(&$plugin, $pressId) {
		$this->_pressId = $pressId;
		$this->_plugin =& $plugin;

		parent::Form($plugin->getTemplatePath() . 'settingsForm.tpl');

		$this->addCheck(new FormValidatorRegExp($this, 'doiPrefix', 'required', 'plugins.pubIds.doi.manager.settings.doiPrefixPattern', '/^10\.[0-9][0-9][0-9][0-9][0-9]?$/'));
		$this->addCheck(new FormValidatorCustom($this, 'doiPublicationFormatSuffixPattern', 'required', 'plugins.pubIds.doi.manager.settings.doiPublicationFormatSuffixPatternRequired', create_function('$doiPublicationFormatSuffixPattern,$form', 'if ($form->getData(\'doiSuffix\') == \'pattern\') return $doiPublicationFormatSuffixPattern != \'\';return true;'), array(&$this)));
		$this->addCheck(new FormValidator($this, 'doiSuffix' ,'required', 'plugins.pubIds.doi.manager.settings.doiSuffixRequired'));
		$this->addCheck(new FormValidatorPost($this));

		// for DOI reset requests
		import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
		$application = PKPApplication::getApplication();
		$request = $application->getRequest();
		$clearPubIdsLinkAction =
		new LinkAction(
				'reassignDOIs',
				new RemoteActionConfirmationModal(
					__('plugins.pubIds.doi.manager.settings.doiReassign.confirm'),
					__('common.delete'),
					$request->url(null, null, 'plugin', null, array('verb' => 'settings', 'clearPubIds' => true, 'plugin' => $plugin->getName(), 'category' => 'pubIds')),
					'modal_delete'
				),
				__('plugins.pubIds.doi.manager.settings.doiReassign'),
				'delete'
		);
		$this->setData('clearPubIdsLinkAction', $clearPubIdsLinkAction);
		$this->setData('pluginName', $plugin->getName());
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData() {
		$pressId = $this->_getPressId();
		$plugin =& $this->_getPlugin();
		foreach($this->_getFormFields() as $fieldName => $fieldType) {
			$this->setData($fieldName, $plugin->getSetting($pressId, $fieldName));
		}
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array_keys($this->_getFormFields()));
	}

	/**
	 * @see Form::execute()
	 */
	function execute() {
		$plugin =& $this->_getPlugin();
		$pressId = $this->_getPressId();
		foreach($this->_getFormFields() as $fieldName => $fieldType) {
			$plugin->updateSetting($pressId, $fieldName, $this->getData($fieldName), $fieldType);
		}
	}


	//
	// Private helper methods
	//
	function _getFormFields() {
		return array(
			'doiPrefix' => 'string',
			'doiSuffix' => 'string',
			'doiPublicationFormatSuffixPattern' => 'string',
		);
	}
}

?>
