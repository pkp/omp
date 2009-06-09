<?php

/**
 * @file StatisticsHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StatisticsHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for statistics functions. 
 */

// $Id$

import('pages.manager.ManagerHandler');

class StatisticsHandler extends ManagerHandler {
	/**
	 * Constructor
	 */	
	function StatisticsHandler() {
		parent::ManagerHandler();
	}
	
	/**
	 * Display a list of press statistics.
	 * WARNING: This implementation should be kept roughly synchronized
	 * with the reader's statistics view in the About pages.
	 */
	function statistics() {
		$this->validate();
		$this->setupTemplate(true);

		$press =& Request::getPress();
		$templateMgr =& TemplateManager::getManager();

		$statisticsYear = Request::getUserVar('statisticsYear');
		if (empty($statisticsYear)) $statisticsYear = date('Y');
		$templateMgr->assign('statisticsYear', $statisticsYear);

		$arrangementIds = $press->getSetting('statisticsArrangementIds');
		if (!is_array($arrangementIds)) $arrangementIds = array();
		$templateMgr->assign('arrangementIds', $arrangementIds);

		foreach (StatisticsHandler::getPublicStatisticsNames() as $name) {
			$templateMgr->assign($name, $press->getSetting($name));
		}
		$templateMgr->assign('statViews', $press->getSetting('statViews'));

		$fromDate = mktime(0, 0, 0, 1, 1, $statisticsYear);
		$toDate = mktime(23, 59, 59, 12, 31, $statisticsYear);

		$pressStatisticsDao =& DAORegistry::getDAO('PressStatisticsDAO');
		$monographStatistics = $pressStatisticsDao->getMonographStatistics($press->getPressId(), null, $fromDate, $toDate);
		$templateMgr->assign('monographStatistics', $monographStatistics);

		$limitedMonographStatistics = $pressStatisticsDao->getMonographStatistics($press->getPressId(), $arrangementIds, $fromDate, $toDate);
		$templateMgr->assign('limitedMonographStatistics', $limitedMonographStatistics);

		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
		$arrangements =& $arrangementDao->getPressAcquisitionsArrangements($press->getPressId());
		$templateMgr->assign('arrangements', $arrangements->toArray());

		$reviewerStatistics = $pressStatisticsDao->getReviewerStatistics($press->getPressId(), $arrangementIds, $fromDate, $toDate);
		$templateMgr->assign('reviewerStatistics', $reviewerStatistics);

		$allUserStatistics = $pressStatisticsDao->getUserStatistics($press->getPressId(), null, $toDate);
		$templateMgr->assign('allUserStatistics', $allUserStatistics);

		$userStatistics = $pressStatisticsDao->getUserStatistics($press->getPressId(), $fromDate, $toDate);
		$templateMgr->assign('userStatistics', $userStatistics);

		$enableSubscriptions = $press->getSetting('enableSubscriptions');
		if ($enableSubscriptions) {
			$templateMgr->assign('enableSubscriptions', true);
			$allSubscriptionStatistics = $pressStatisticsDao->getSubscriptionStatistics($press->getPressId(), null, $toDate);
			$templateMgr->assign('allSubscriptionStatistics', $allSubscriptionStatistics);

			$subscriptionStatistics = $pressStatisticsDao->getSubscriptionStatistics($press->getPressId(), $fromDate, $toDate);
			$templateMgr->assign('subscriptionStatistics', $subscriptionStatistics);
		}

		$notificationStatusDao =& DAORegistry::getDAO('NotificationStatusDAO');
		$notifiableUsers = $notificationStatusDao->getNotifiableUsersCount($press->getPressId());
		$templateMgr->assign('notifiableUsers', $notifiableUsers);

		$reportPlugins =& PluginRegistry::loadCategory('reports');
		$templateMgr->assign_by_ref('reportPlugins', $reportPlugins);

		$templateMgr->assign('helpTopicId', 'press.managementPages.statsAndReports');

		$templateMgr->display('manager/statistics/index.tpl');
	}

	function saveStatisticsArrangements() {
		// The manager wants to save the list of acquisitions arrangements used to
		// generate statistics.

		$this->validate();

		$press =& Request::getPress();

		$arrangementIds = Request::getUserVar('arrangementIds');
		if (!is_array($arrangementIds)) {
			if (empty($arrangementIds)) $arrangementIds = array();
			else $arrangementIds = array($arrangementIds);
		}

		$press->updateSetting('statisticsArrangementIds', $arrangementIds);
		Request::redirect(null, null, 'statistics', null, array('statisticsYear' => Request::getUserVar('statisticsYear')));
	}

	function getPublicStatisticsNames() {
		return array(
			'statItemsPublished',
			'statNumSubmissions',
			'statPeerReviewed',
			'statCountAccept',
			'statCountDecline',
			'statCountRevise',
			'statDaysPerReview',
			'statDaysToPublication',
			'statRegisteredUsers',
			'statRegisteredReaders',
			'statSubscriptions',
		);
	}

	function savePublicStatisticsList() {
		$this->validate();

		$press =& Request::getPress();
		foreach (StatisticsHandler::getPublicStatisticsNames() as $name) {
			$press->updateSetting($name, Request::getUserVar($name)?true:false);
		}
		$press->updateSetting('statViews', Request::getUserVar('statViews')?true:false);
		Request::redirect(null, null, 'statistics', null, array('statisticsYear' => Request::getUserVar('statisticsYear')));
	}

	function report($args) {
		$this->validate();
		$this->setupTemplate();

		$press =& Request::getPress();

		$pluginName = array_shift($args);
		$reportPlugins =& PluginRegistry::loadCategory('reports');

		if ($pluginName == '' || !isset($reportPlugins[$pluginName])) {
			Request::redirect(null, null, 'statistics');
		}

		$plugin =& $reportPlugins[$pluginName];
		$plugin->display($args);
	}
}

?>
