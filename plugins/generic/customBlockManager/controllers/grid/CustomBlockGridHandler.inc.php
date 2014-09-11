<?php

/**
 * @file plugins/generic/customBlockManager/controllers/grid/CustomBlockGridHandler.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomBlockGridHandler
 * @ingroup controllers_grid_customBlockManager
 *
 * @brief Handle custom block manager grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('plugins.generic.customBlockManager.controllers.grid.CustomBlockGridRow');

class CustomBlockGridHandler extends GridHandler {
	/** @var CustomBlockManagerPlugin */
	var $plugin;

	/**
	 * Constructor
	 */
	function CustomBlockGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER),
			array('fetchGrid', 'fetchRow', 'addCustomBlock', 'editCustomBlock', 'updateCustomBlock', 'deleteCustomBlock')
		);
		$this->plugin = PluginRegistry::getPlugin('generic', CUSTOMBLOCKMANAGER_PLUGIN_NAME);

	}


	//
	// Overridden template methods
	//
	/**
	 * @see Gridhandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request);
		$press = $request->getPress();

		// Set the grid title.
		$this->setTitle('plugins.generic.customBlockManager.customBlocks');
		// Set the grid instructions.
		$this->setInstructions('plugin.generic.customBlockManager.introduction');
		// Set the no items row text.
		$this->setEmptyRowText('plugins.generic.customBlockManager.noneCreated');

		$customBlockManagerPlugin = $this->plugin;
		$blocks = $customBlockManagerPlugin->getSetting($press->getId(), 'blocks');
		$gridData = array();
		if (is_array($blocks)) foreach ($blocks as $block) {
			$gridData[$block] = array(
				'title' => $block
			);
		}
		$this->setGridDataElements($gridData);

		// Add grid-level actions
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'addCustomBlock',
				new AjaxModal(
					$router->url($request, null, null, 'addCustomBlock'),
					//$router->url($request, null, null, 'addCustomBlock', null, array('gridId' => $this->getId())),
					__('plugins.generic.customBlockManager.addBlock'),
					'modal_add_item'
				),
				__('plugins.generic.customBlockManager.addBlock'),
				'add_item'
			)
		);

		// Columns
		$this->addColumn(
			new GridColumn(
				'title',
				'plugins.generic.customBlockManager.blockName',
				null,
				'controllers/grid/gridCell.tpl'
			)
		);
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see Gridhandler::getPublishChangeEvents()
	 *
	 * Used to update the site context switcher upon create/delete.
	 */
	function getPublishChangeEvents() {
		return array('updateSidebar');
	}

	/**
	 * @see Gridhandler::getRowInstance()
	 */
	function getRowInstance() {
		return new CustomBlockGridRow();
	}

	//
	// Public Grid Actions
	//
	/**
	 * An action to add a new custom block
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addCustomBlock($args, $request) {
		// Calling editCustomBlock with an empty ID will add
		// a new custom block.
		return $this->editCustomBlock($args, $request);
	}

	/**
	 * An action to edit a custom block
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editCustomBlock($args, $request) {
		$blockName = $request->getUserVar('blockName');
		$press = $request->getPress();
		$this->setupTemplate($request);

		$customBlockPlugin = null;
		// if this is the edit of the existing custom block plugin
		if ($blockName) {
			// Create the custom block plugin
			import('plugins.generic.customBlockManager.CustomBlockPlugin');
			$customBlockPlugin = new CustomBlockPlugin($blockName, CUSTOMBLOCKMANAGER_PLUGIN_NAME);
		}

		// Call the form
		import('plugins.generic.customBlockManager.controllers.grid.form.CustomBlockForm');
		$customBlockManagerPlugin = $this->plugin;
		$template = $customBlockManagerPlugin->getTemplatePath() . 'editCustomBlockForm.tpl';
		$customBlockForm = new CustomBlockForm($template, $press->getId(), $customBlockPlugin);
		$customBlockForm->initData();
		$json = new JSONMessage(true, $customBlockForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a custom block
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateCustomBlock($args, $request) {
		$pluginName = $request->getUserVar('existingBlockName');
		$press = $request->getPress();
		$this->setupTemplate($request);

		$customBlockPlugin = null;
		// if this was the edit of the existing custom block plugin
		if ($pluginName) {
			// Create the custom block plugin
			import('plugins.generic.customBlockManager.CustomBlockPlugin');
			$customBlockPlugin = new CustomBlockPlugin($pluginName, CUSTOMBLOCKMANAGER_PLUGIN_NAME);
		}

		// Call the form
		import('plugins.generic.customBlockManager.controllers.grid.form.CustomBlockForm');
		$customBlockManagerPlugin = $this->plugin;
		$template = $customBlockManagerPlugin->getTemplatePath() . 'editCustomBlockForm.tpl';
		$customBlockForm = new CustomBlockForm($template, $press->getId(), $customBlockPlugin);
		$customBlockForm->readInputData();
		if ($customBlockForm->validate()) {
			$customBlockForm->execute();
 			return DAO::getDataChangedEvent();
		} else {
			$json = new JSONMessage(true, $customBlockForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a custom block
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteCustomBlock($args, $request) {
		$blockName = $request->getUserVar('blockName');
		$press = $request->getPress();

		// Delete all the entries for this block plugin
		$pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
		$pluginSettingsDao->deleteSetting($press->getId(), $blockName, 'enabled');
		$pluginSettingsDao->deleteSetting($press->getId(), $blockName, 'context');
		$pluginSettingsDao->deleteSetting($press->getId(), $blockName, 'seq');
		$pluginSettingsDao->deleteSetting($press->getId(), $blockName, 'blockContent');
		// Remove this block plugin from the list of the custom block plugins
		$customBlockManagerPlugin = $this->plugin;
		$blocks = $customBlockManagerPlugin->getSetting($press->getId(), 'blocks');
		$newBlocks = array_diff($blocks, array($blockName));
		ksort($newBlocks);
		$customBlockManagerPlugin->updateSetting($press->getId(), 'blocks', $newBlocks);
		return DAO::getDataChangedEvent();
	}
}

?>
