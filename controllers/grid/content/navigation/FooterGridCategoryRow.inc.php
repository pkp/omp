<?php

/**
 * @file controllers/grid/content/navigation/FooterGridCategoryRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FooterGridCategoryRow
 * @ingroup controllers_grid_content_navigation
 *
 * @brief Footer grid category row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

// Link actions
import('lib.pkp.classes.linkAction.request.AjaxModal');
import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');


class FooterGridCategoryRow extends GridCategoryRow {
	/** @var Press **/
	var $_press;

	/** @var FooterCategory **/
	var $_footerCategory;

	/**
	 * Constructor
	 */
	function FooterGridCategoryRow() {
		parent::GridCategoryRow();
	}

	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		// Is this a new row or an existing row?
		$footerCategoryId = $this->getId();
		if (!empty($footerCategoryId) && is_numeric($footerCategoryId)) {
			$footerCategory =& $this->getData();
			$this->_footerCategory =& $footerCategory;

			$router =& $request->getRouter();
			$actionArgs = array(
					'footerCategoryId' => $footerCategoryId
			);

			$this->addAction(
				new LinkAction(
					'deleteFooterCategory',
					new RemoteActionConfirmationModal(
						__('grid.content.navigation.footer.deleteCategoryConfirm'),
						__('grid.content.navigation.footer.deleteCategory'),
						$router->url($request, null, null, 'deleteFooterCategory', null, $actionArgs)
					),
					null,
					'delete'
				)
			);

			$this->addAction(
				new LinkAction(
					'editFooterCategory',
					new AjaxModal(
						$router->url($request, null, null, 'editFooterCategory', null, $actionArgs),
						__('grid.content.navigation.footer.editCategory')
					),
					$footerCategory->getLocalizedTitle()
				)
			);
		}
	}

	//
	// Overridden methods from GridCategoryRow
	//

	/**
	 * Return an empty string, since the Action contains the label.
	 * @see FooterLinkCategory::getLocalizedTitle()
	 * @return string
	 */
	function getCategoryLabel() {
		return '';
	}
}
?>