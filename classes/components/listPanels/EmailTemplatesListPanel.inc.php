<?php
/**
 * @file components/listPanels/EmailTemplatesListPanel.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class EmailTemplatesListPanel
 * @ingroup classes_components_listPanels
 *
 * @brief A ListPanel component for viewing and editing email templates
 */

namespace APP\components\listPanels;
use \PKP\components\listPanels\PKPEmailTemplatesListPanel;

class EmailTemplatesListPanel extends PKPEmailTemplatesListPanel {
	/**
	 * @copydoc ListPanel::getConfig()
	 */
	public function getConfig() {
		$config = parent::getConfig();
		$filterByStageKey = count($config['filters']) - 1;
		unset($config['filters'][$filterByStageKey]['filters'][1]); // remove internal review stage
		$config['filters'][$filterByStageKey]['filters'][2]['title'] = __('manager.publication.reviewStage');

		return $config;
	}
}
