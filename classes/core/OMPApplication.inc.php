<?php

/**
 * @file classes/core/OMPApplication.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OMPApplication
 * @ingroup core
 * @see PKPApplication
 *
 * @brief Class describing this application.
 *
 */

// $Id$


import('core.PKPApplication');

class OMPApplication extends PKPApplication {
	function OMPApplication() {
		parent::PKPApplication();
	}

	function initialize(&$application) {
		PKPApplication::initialize($application);

		import('i18n.Locale');
		import('core.Request');
	}

	/**
	 * Get the "context depth" of this application, i.e. the number of
	 * parts of the URL after index.php that represent the context of
	 * the current request (e.g. Journal [1], or Conference and
	 * Scheduled Conference [2]).
	 * @return int
	 */
	function getContextDepth() {
		return 1;
	}
		
	function getContextList() {
		return array('press');
	}
	/**
	 * Get the locale key for the name of this application.
	 * @return string
	 */
	function getNameKey() {
		return('common.openMonographPress');
	}
	/**
	 * Get the symbolic name of this application
	 * @return string
	 */
	function getName() {
		return 'omp';
	}

	/**
	 * Get the URL to the XML descriptor for the current version of this
	 * application.
	 * @return string
	 */
	function getVersionDescriptorUrl() {
		return('http://pkp.sfu.ca/omp/xml/omp-version.xml');
	}

	/**
	 * Determine whether or not the request is cacheable.
	 * @return boolean
	 */
	function isCacheable() {
		if (defined('SESSION_DISABLE_INIT')) return false;
		if (!Config::getVar('general', 'installed')) return false;
		if (!empty($_POST) || Validation::isLoggedIn()) return false;
		if (!PKPRequest::isPathInfoEnabled()) {
			$ok = array('journal', 'page', 'op', 'path');
			if (!empty($_GET) && count(array_diff(array_keys($_GET), $ok)) != 0) {
				return false;
			}
		} else {
			if (!empty($_GET)) return false;
		}

		if (in_array(PKPRequest::getRequestedPage(), array(
			'about', 'announcement', 'help', 'index', 'information', 'rt', 'issue', ''
		))) return true;

		return false;
	}

	/**
	 * Get the filename to use for cached content for the current request.
	 * @return string
	 */
	function getCacheFilename() {
		static $cacheFilename;
		if (!isset($cacheFilename)) {
			if (PKPRequest::isPathInfoEnabled()) {
				$id = isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'index';
				$id .= '-' . Locale::getLocale();
			} else {
				$id = Request::getUserVar('journal') . '-' . Request::getUserVar('page') . '-' . Request::getUserVar('op') . '-' . Request::getUserVar('path') . '-' . Locale::getLocale();
			}
			$path = dirname(dirname(dirname(__FILE__)));
			$cacheFilename = $path . '/cache/wc-' . md5($id) . '.html';
		}
		return $cacheFilename;
	}

	/**
	 * Get the map of DAOName => full.class.Path for this application.
	 * @return array
	 */
	function getDAOMap() {
		return array_merge(parent::getDAOMap(), array(
			'AnnouncementDAO' => 'announcement.AnnouncementDAO',
			'AnnouncementTypeDAO' => 'announcement.AnnouncementTypeDAO',
			'ArticleEmailLogDAO' => 'article.log.ArticleEmailLogDAO',
			'ArticleEventLogDAO' => 'article.log.ArticleEventLogDAO',
			'MonographCommentDAO' => 'monograph.MonographCommentDAO',
			'MonographComponentDAO' => 'monograph.MonographComponentDAO',
			'ArticleDAO' => 'article.ArticleDAO',
			'AcquisitionsArrangementDAO' => 'press.AcquisitionsArrangementDAO',
			'AcquisitionsArrangementEditorsDAO' => 'press.AcquisitionsArrangementEditorsDAO',
			'MonographFileDAO' => 'monograph.MonographFileDAO',
			'ArticleGalleyDAO' => 'article.ArticleGalleyDAO',
			'ArticleNoteDAO' => 'article.ArticleNoteDAO',
			'ArticleSearchDAO' => 'search.ArticleSearchDAO',
			'AuthorDAO' => 'monograph.AuthorDAO',
			'AuthorSubmissionDAO' => 'submission.author.AuthorSubmissionDAO',
			'CommentDAO' => 'comment.CommentDAO',
			'CopyAssignmentDAO' => 'submission.copyAssignment.CopyAssignmentDAO',
			'CopyeditorSubmissionDAO' => 'submission.copyeditor.CopyeditorSubmissionDAO',
			'EditAssignmentDAO' => 'submission.editAssignment.EditAssignmentDAO',
			'EditorSubmissionDAO' => 'submission.editor.EditorSubmissionDAO',
			'EmailTemplateDAO' => 'mail.EmailTemplateDAO',
			'GroupDAO' => 'group.GroupDAO',
			'GroupMembershipDAO' => 'group.GroupMembershipDAO',
			'IssueDAO' => 'issue.IssueDAO',
			'LayoutAssignmentDAO' => 'submission.layoutAssignment.LayoutAssignmentDAO',
			'LayoutEditorSubmissionDAO' => 'submission.layoutEditor.LayoutEditorSubmissionDAO',
			'MonographDAO' => 'monograph.MonographDAO',
			'MonographGalleyDAO' => 'monograph.MonographGalleyDAO',
			'NotificationStatusDAO' => 'journal.NotificationStatusDAO',
			'OAIDAO' => 'oai.ojs.OAIDAO',
			'OJSCompletedPaymentDAO' => 'payment.ojs.OJSCompletedPaymentDAO',
			'PluginSettingsDAO' => 'plugins.PluginSettingsDAO',
			'PressDAO' => 'press.PressDAO',
			'PressSettingsDAO' => 'press.PressSettingsDAO',
			'ProofAssignmentDAO' => 'submission.proofAssignment.ProofAssignmentDAO',
			'ProofreaderSubmissionDAO' => 'submission.proofreader.ProofreaderSubmissionDAO',
			'PublishedArticleDAO' => 'article.PublishedArticleDAO',
			'QueuedPaymentDAO' => 'payment.QueuedPaymentDAO',
			'ReviewAssignmentDAO' => 'submission.reviewAssignment.ReviewAssignmentDAO',
			'ReviewerSubmissionDAO' => 'submission.reviewer.ReviewerSubmissionDAO',
			'ReviewFormDAO' => 'reviewForm.ReviewFormDAO',
			'ReviewFormElementDAO' => 'reviewForm.ReviewFormElementDAO',
			'ReviewFormResponseDAO' => 'reviewForm.ReviewFormResponseDAO',
			'RoleDAO' => 'security.RoleDAO',
			'RTDAO' => 'rt.ojs.RTDAO',
			'SuppFileDAO' => 'monograph.SuppFileDAO',
			'ScheduledTaskDAO' => 'scheduledTask.ScheduledTaskDAO',
			'AcquisitionsEditorSubmissionDAO' => 'submission.acquisitionsEditor.AcquisitionsEditorSubmissionDAO',
			'SubscriptionDAO' => 'subscription.SubscriptionDAO',
			'SubscriptionTypeDAO' => 'subscription.SubscriptionTypeDAO',
			'UserDAO' => 'user.UserDAO',
			'UserSettingsDAO' => 'user.UserSettingsDAO'
		));
	}

	/**
	 * Get the list of plugin categories for this application.
	 */
	function getPluginCategories() {
		return array(
			'auth',
			'blocks',
			'generic',
			'importexport',
			'themes'
		);
	}

	/**
	 * Instantiate the help object for this application.
	 * @return object
	 */
	function &instantiateHelp() {
		import('help.Help');
		$help =& new Help();
		return $help;
	}
}

?>
