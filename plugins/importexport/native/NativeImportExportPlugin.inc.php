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
	 * @see ImportExportPlugin::display()
	 */
	function display($args, $request) {
		$context = $request->getContext();
		$user = $request->getUser();
		$deployment = new NativeImportExportDeployment($context, $user);

		$this->setDeployment($deployment);

		parent::display($args, $request);

		if ($this->isResultManaged) {
			if ($this->result) {
				return $this->result;
			}

			return false;
		}

		switch ($this->opType) {
			default:
				$dispatcher = $request->getDispatcher();
				$dispatcher->handle404();
		}
	}

	/**
	 * @see ImportExportPlugin::getImportFilter
	 */
	function getImportFilter($xmlFile) {
		$filter = 'native-xml=>monograph';

		$xmlString = file_get_contents($xmlFile);

		return array($filter, $xmlString);
	}

	/**
	 * @see ImportExportPlugin::getExportFilter
	 */
	function getExportFilter($exportType) {
		$filter = false;
		if ($exportType == 'exportSubmissions') {
			$filter = 'monograph=>native-xml';
		}

		return $filter;
	}

	/**
	 * @see ImportExportPlugin::getAppSpecificDeployment
	 */
	function getAppSpecificDeployment($journal, $user) {
		return new NativeImportExportDeployment($journal, $user);
	}
}
