<?php

/**
 * @file controllers/grid/EmailGridRow.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailGridRow
 * @ingroup controllers_grid_translator
 *
 * @brief Handle email grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');
import('lib.pkp.classes.linkAction.request.RedirectAction');

class EmailGridRow extends GridRow {
	/** @var string JQuery selector for containing tab element */
	var $tabsSelector;

	/** @var string Locale */
	var $locale;

	/**
	 * Constructor
	 * @param $tabsSelector string Selector for containing tab element
	 */
	function EmailGridRow($tabsSelector, $locale) {
		parent::GridRow();
		$this->tabsSelector = $tabsSelector;
		$this->locale = $locale;
	}

	//
	// Overridden template methods
	//
	/**
	 * @copydoc GridRow::initialize()
	 */
	function initialize($request) {
		parent::initialize($request);
		$router = $request->getRouter();
		$data = $this->getData();

		$actionArgs = array(
			'locale' => $this->locale,
			'emailKey' => $this->getId(),
		);

		// Create the "edit" action
		import('lib.pkp.classes.linkAction.request.AddTabAction');
		$this->addAction(
			new LinkAction(
				'edit',
				new AddTabAction(
					$this->tabsSelector,
					$router->url($request, null, null, 'edit', null, $actionArgs),
					$this->getId() // Title; just use email key
				),
				__('grid.action.edit'),
				'edit'
			)
		);
	}
}

?>
