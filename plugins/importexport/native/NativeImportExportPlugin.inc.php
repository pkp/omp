<?php

/**
 * @file plugins/importexport/native/NativeImportExportPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NativeImportExportPlugin
 * @ingroup plugins_importexport_native
 *
 * @brief Native XML import/export plugin
 */

import('lib.pkp.plugins.importexport.native.PKPNativeImportExportPlugin');

class NativeImportExportPlugin extends PKPNativeImportExportPlugin {
	/**
	 * Display the plugin.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function display($args, $request) {
		$context = $request->getContext();
		$user = $request->getUser();
		$deployment = new NativeImportExportDeployment($context, $user);

		$this->setDeployment($deployment);

		$templateMgr = TemplateManager::getManager($request);

		list ($returnString, $managed) = parent::display($args, $request);

		if ($managed) {
			if ($returnString) {
				return $returnString;
			}

			return;
		}

		$templateMgr->assign('plugin', $this);

		switch (array_shift($args)) {
			default:
				$dispatcher = $request->getDispatcher();
				$dispatcher->handle404();
		}
	}

	function getImportFilter($xmlFile) {
		$filter = 'native-xml=>monograph';

		$xmlString = file_get_contents($xmlFile);

		return array($filter, $xmlString);
	}

	function getExportFilter($exportType) {
		$filter = false;
		if ($exportType == 'exportSubmissions') {
			$filter = 'monograph=>native-xml';
		}

		return $filter;
	}

	function getAppSpecificDeployment($journal, $user) {
		return new NativeImportExportDeployment($journal, $user);
	}
}
