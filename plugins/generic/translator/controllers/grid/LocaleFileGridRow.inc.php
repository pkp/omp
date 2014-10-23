<?php

/**
 * @file controllers/grid/LocaleFileGridRow.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LocaleFileGridRow
 * @ingroup controllers_grid_translator
 *
 * @brief Handle locale grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');
import('lib.pkp.classes.linkAction.request.RedirectAction');

class LocaleFileGridRow extends GridRow {
	/** @var string JQuery selector for containing tab element */
	var $tabsSelector;

	/** @var string Locale */
	var $locale;

	/**
	 * Constructor
	 * @param $tabsSelector string Selector for containing tab element
	 */
	function LocaleFileGridRow($tabsSelector, $locale) {
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

		$actionArgs = array(
			'locale' => $this->locale,
			'filename' => $this->getData(),
		);

		// Create the "edit" action
		import('lib.pkp.classes.linkAction.request.AddTabAction');
		$this->addAction(
			new LinkAction(
				'edit',
				new AddTabAction(
					$this->tabsSelector,
					$router->url($request, null, null, 'edit', null, $actionArgs),
					$this->getData() // Title; just use filename
				),
				__('grid.action.edit'),
				'edit'
			)
		);

		// Create the "download" action
		import('lib.pkp.classes.linkAction.request.RedirectAction');
		if (file_exists($this->getData())) $this->addAction(
			new LinkAction(
				'download',
				new RedirectAction(
					$router->url($request, null, null, 'download', null, $actionArgs)
				),
				__('grid.action.download'),
				'zip'
			)
		);
	}
}

?>
