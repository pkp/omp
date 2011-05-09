<?php

/**
 * @file controllers/tab/settings/divisionsAndSeries/form/DivisionsAndSeriesForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DivisionsAndSeriesForm
 * @ingroup controllers_tab_settings_divisionsAndSeries_form
 *
 * @brief Form to edit divisions and series.
 */


// Import the base Form.
import('lib.pkp.classes.form.Form');

class DivisionsAndSeriesForm extends Form {

	/**
	 * Constructor.
	 */
	function DivisionsAndSeriesForm() {
		parent::Form('controllers/tab/settings/divisionsAndSeries/form/divisionsAndSeriesForm.tpl');
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		return parent::fetch(&$request);
	}
}

