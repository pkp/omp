<?php

/**
 * @file controllers/tab/settings/categoriesAndSeries/form/CategoriesAndSeriesForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoriesAndSeriesForm
 * @ingroup controllers_tab_settings_categoriesAndSeries_form
 *
 * @brief Form to edit categories and series.
 */


// Import the base Form.
import('lib.pkp.classes.form.Form');

class CategoriesAndSeriesForm extends Form {

	/**
	 * Constructor.
	 */
	function CategoriesAndSeriesForm() {
		parent::Form('controllers/tab/settings/categoriesAndSeries/form/categoriesAndSeriesForm.tpl');
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		return parent::fetch(&$request);
	}
}

?>
