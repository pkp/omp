<?php

/**
 * @file classes/install/Upgrade.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Upgrade
 * @ingroup install
 *
 * @brief Perform system upgrade.
 */


import('lib.pkp.classes.install.Installer');

class Upgrade extends Installer {

	/**
	 * Constructor.
	 * @param $params array upgrade parameters
	 */
	function __construct($params, $installFile = 'upgrade.xml', $isPlugin = false) {
		parent::__construct($installFile, $params, $isPlugin);
	}


	/**
	 * Returns true iff this is an upgrade process.
	 * @return boolean
	 */
	function isUpgrade() {
		return true;
	}


	//
	// Specific upgrade actions
	//

	/**
	 * Fix broken submission filenames (bug #8461)
	 * @param $upgrade Upgrade
	 * @param $params array
	 * @param $dryrun boolean True iff only a dry run (displaying rather than executing changes) should be done.
	 * @return boolean
	 */
	function fixFilenames($upgrade, $params, $dryrun = false) {
		$pressDao = DAORegistry::getDAO('PressDAO');
		$submissionDao = DAORegistry::getDAO('MonographDAO');
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		DAORegistry::getDAO('GenreDAO'); // Load constants
		$siteDao = DAORegistry::getDAO('SiteDAO'); /* @var $siteDao SiteDAO */
		$site = $siteDao->getSite();
		$adminEmail = $site->getLocalizedContactEmail();

		import('lib.pkp.classes.file.SubmissionFileManager');

		$contexts = $pressDao->getAll();
		while ($context = $contexts->next()) {
			$submissions = $submissionDao->getByPressId($context->getId());
			while ($submission = $submissions->next()) {
				$submissionFileManager = new SubmissionFileManager($context->getId(), $submission->getId());
				$submissionFiles = $submissionFileDao->getBySubmissionId($submission->getId());
				foreach ($submissionFiles as $submissionFile) {
					$generatedFilename = $submissionFile->getServerFileName();
					$basePath = $submissionFileManager->getBasePath() . $submissionFile->_fileStageToPath($submissionFile->getFileStage()) . '/';
					$globPattern = $submissionFile->getSubmissionId() . '-' .
						'*' . '-' . // Genre name and designation globbed (together)
						$submissionFile->getFileId() . '-' .
						$submissionFile->getRevision() . '-' .
						$submissionFile->getFileStage() . '-' .
						date('Ymd', strtotime($submissionFile->getDateUploaded())) .
						'.' . strtolower_codesafe($submissionFile->getExtension());

					$matchedResults = glob($basePath . $globPattern);
					if (count($matchedResults)>1) {
						error_log("Duplicate potential files for \"$globPattern\"!", 1, $adminEmail);
						continue;
					}
					if (count($matchedResults) == 1) {
						// 1 result matched.
						$discoveredFilename = array_shift($matchedResults);
						if ($dryrun) {
							echo "Need to rename \"$discoveredFilename\" to \"$generatedFilename\".\n";
						} else {
							rename($discoveredFilename, $basePath . $generatedFilename);
						}
					} else {
						// 0 results matched.
						error_log("Unable to find a match for \"$globPattern\".\n", 1, $adminEmail);
						continue;
					}
				}
			}
		}
		return true;
	}

	/**
	 * Enable the default theme plugin for versions < 1.1.
	 * @return boolean
	 */
	function enableDefaultTheme() {
		$pressDao = DAORegistry::getDAO('PressDAO');
		$contexts = $pressDao->getAll();
		$pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');

		// Site-wide
		$pluginSettingsDao->updateSetting(0, 'defaultthemeplugin', 'enabled', '1', 'int');

		// For each press
		while ($context = $contexts->next()) {
			$pluginSettingsDao->updateSetting($context->getId(), 'defaultthemeplugin', 'enabled', '1', 'int');
		}
		return true;
	}

	/**
	 * Synchronize the ASSOC_TYPE_SERIES constant to ASSOC_TYPE_SECTION defined in PKPApplication.
	 * @return boolean
	 */
	function syncSeriesAssocType() {
		// Can be any DAO.
		$dao =& DAORegistry::getDAO('UserDAO'); /* @var $dao DAO */
		$tablesToUpdate = array(
			'features',
			'data_object_tombstone_oai_set_objects',
			'new_releases',
			'spotlights',
			'notifications',
			'email_templates',
			'email_templates_data',
			'controlled_vocabs',
			'event_log',
			'email_log',
			'metadata_descriptions',
			'notes',
			'item_views');

		foreach ($tablesToUpdate as $tableName) {
			$dao->update('UPDATE ' . $tableName . ' SET assoc_type = ' . ASSOC_TYPE_SERIES . ' WHERE assoc_type = 526');
		}

		return true;
	}

	/**
	 * Fix incorrectly stored author settings. (See bug #8663.)
	 * @return boolean
	 */
	function fixAuthorSettings() {
		$authorDao = DAORegistry::getDAO('AuthorDAO');

		// Get all authors with broken data
		$result = $authorDao->retrieve(
			'SELECT DISTINCT author_id
			FROM	author_settings
			WHERE	(setting_name = ? OR setting_name = ?)
				AND setting_type = ?',
			array('affiliation', 'biography', 'object')
		);

		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$authorId = $row['author_id'];
			$result->MoveNext();

			$author = $authorDao->getById($authorId);
			if (!$author) continue; // Bonehead check (DB integrity)

			foreach ((array) $author->getAffiliation(null) as $locale => $affiliation) {
				if (is_array($affiliation)) foreach($affiliation as $locale => $s) {
					$author->setAffiliation($s, $locale);
				}
			}

			foreach ((array) $author->getBiography(null) as $locale => $biography) {
				if (is_array($biography)) foreach($biography as $locale => $s) {
					$author->setBiography($s, $locale);
				}
			}
			$authorDao->updateObject($author);
		}

		$result->Close();
		return true;
	}

	/**
	 * Convert email templates to HTML.
	 * @return boolean True indicates success.
	 */
	function htmlifyEmailTemplates() {
		$emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');

		// Convert the email templates in email_templates_data to localized
		$result = $emailTemplateDao->retrieve('SELECT * FROM email_templates_data');
		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$emailTemplateDao->update(
				'UPDATE	email_templates_data
				SET	body = ?
				WHERE	email_key = ? AND
					locale = ? AND
					assoc_type = ? AND
					assoc_id = ?',
				array(
					preg_replace('/{\$[a-zA-Z]+Url}/', '<a href="\0">\0</a>', nl2br($row['body'])),
					$row['email_key'],
					$row['locale'],
					$row['assoc_type'],
					$row['assoc_id']
				)
			);
			$result->MoveNext();
		}
		$result->Close();

		// Convert the email templates in email_templates_default_data to localized
		$result = $emailTemplateDao->retrieve('SELECT * FROM email_templates_default_data');
		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$emailTemplateDao->update(
				'UPDATE	email_templates_default_data
				SET	body = ?
				WHERE	email_key = ? AND
					locale = ?',
				array(
					preg_replace('/{\$[a-zA-Z]+Url}/', '<a href="\0">\0</a>', nl2br($row['body'])),
					$row['email_key'],
					$row['locale'],
				)
			);
			$result->MoveNext();
		}
		$result->Close();

		// Localize the email header and footer fields.
		$contextDao = DAORegistry::getDAO('PressDAO');
		$settingsDao = DAORegistry::getDAO('PressSettingsDAO');
		$contexts = $contextDao->getAll();
		while ($context = $contexts->next()) {
			foreach (array('emailFooter', 'emailSignature') as $settingName) {
				$settingsDao->updateSetting(
					$context->getId(),
					$settingName,
					$context->getSetting('emailHeader'),
					'string'
				);
			}
		}

		return true;
	}

	/**
	 * Convert signoffs to queries.
	 * @return boolean True indicates success.
	 */
	function convertQueries() {
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		import('lib.pkp.classes.submission.SubmissionFile');

		$filesResult = $submissionFileDao->retrieve(
			'SELECT DISTINCT sf.file_id, sf.assoc_type, sf.assoc_id, s.symbolic, s.date_notified, s.date_completed, s.user_id, s.signoff_id FROM submission_files sf, signoffs s WHERE s.assoc_type=? AND s.assoc_id=sf.file_id AND s.symbolic IN (?, ?)',
			array(ASSOC_TYPE_SUBMISSION_FILE, 'SIGNOFF_COPYEDITING', 'SIGNOFF_PROOFING')
		);

		$queryDao = DAORegistry::getDAO('QueryDAO');
		$noteDao = DAORegistry::getDAO('NoteDAO');
		$userDao = DAORegistry::getDAO('UserDAO');
		$stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');

		//
		// 1. Go through all signoff/file pairs and migrate them into queries.
		// Queries should be created per file and users should be consolidated
		// from potentially multiple audit assignments into fewer queries.
		//

		while (!$filesResult->EOF) {
			$row = $filesResult->getRowAssoc(false);
			$fileId = $row['file_id'];
			$symbolic = $row['symbolic'];
			$dateNotified = $row['date_notified']?strtotime($row['date_notified']):null;
			$dateCompleted = $row['date_completed']?strtotime($row['date_completed']):null;
			$userId = $row['user_id'];
			$signoffId = $row['signoff_id'];
			$fileAssocType = $row['assoc_type'];
			$fileAssocId = $row['assoc_id'];
			$filesResult->MoveNext();

			$submissionFiles = $submissionFileDao->getAllRevisions($fileId);
			assert(count($submissionFiles)>0);
			$anyFile = end($submissionFiles);

			$assocType = $assocId = $query = null; // Prevent PHP scrutinizer warnings
			switch ($symbolic) {
				case 'SIGNOFF_COPYEDITING':
					$query = $queryDao->newDataObject();
					$query->setAssocType($assocType = ASSOC_TYPE_SUBMISSION);
					$query->setAssocId($assocId = $anyFile->getSubmissionId());
					$query->setStageId(WORKFLOW_STAGE_ID_EDITING);
					break;
				case 'SIGNOFF_PROOFING':
					// We've already migrated a signoff for this file; add this user to it too.
					if ($anyFile->getAssocType() == ASSOC_TYPE_NOTE) {
						$note = $noteDao->getById($anyFile->getAssocId());
						assert($note && $note->getAssocType() == ASSOC_TYPE_QUERY);
						if (count($queryDao->getParticipantIds($note->getAssocId(), $userId))==0) $queryDao->insertParticipant($anyFile->getAssocId(), $userId);
						$this->_transferSignoffData($signoffId, $note->getAssocId());
						continue 2;
					}
					$query = $queryDao->newDataObject();
					assert($anyFile->getAssocType()==ASSOC_TYPE_REPRESENTATION);
					$query->setAssocType($assocType = ASSOC_TYPE_SUBMISSION);
					$query->setAssocId($assocId = $anyFile->getSubmissionId());
					$query->setStageId(WORKFLOW_STAGE_ID_PRODUCTION);
					break;
				default: assert(false);
			}
			$query->setSequence(REALLY_BIG_NUMBER);
			$query->setIsClosed($dateCompleted?true:false);
			$queryDao->insertObject($query);
			$queryDao->resequence($assocType, $assocId);

			// Build a list of all users who should be involved in the query
			$user = $userDao->getById($userId);
			$assignedUserIds = array($userId);
			foreach (array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT) as $roleId) {
				$stageAssignments = $stageAssignmentDao->getBySubmissionAndRoleId($anyFile->getSubmissionId(), $roleId, $query->getStageId());
				while ($stageAssignment = $stageAssignments->next()) {
					$assignedUserIds[] = $stageAssignment->getUserId();
				}
			}
			// Add the assigned auditor as a query participant
			foreach (array_unique($assignedUserIds) as $assignedUserId) {
				$queryDao->insertParticipant($query->getId(), $assignedUserId);
			}

			// Create a head note
			$headNote = $noteDao->newDataObject();
			$headNote->setAssocType(ASSOC_TYPE_QUERY);
			$headNote->setAssocId($query->getId());
			switch($symbolic) {
				case 'SIGNOFF_COPYEDITING':
					$headNote->setTitle('Copyediting for "' . $anyFile->getFileLabel() . '"');
					$headNote->setContents('Auditing assignment for the file "' . htmlspecialchars($anyFile->getFileLabel()) . '" (Signoff ID: ' . $signoffId . ')');
					break;
				case 'SIGNOFF_PROOFING':
					$headNote->setTitle('Proofreading for ' . $anyFile->getFileLabel());
					$headNote->setContents('Proofing assignment for the file "' . htmlspecialchars($anyFile->getFileLabel()) . '" (Signoff ID: ' . $signoffId . ')');
					break;
				default: assert(false);
			}
			$noteDao->insertObject($headNote);

			// Correct the creation date (automatically assigned) with the signoff value
			$headNote->setDateCreated($dateNotified);
			$noteDao->updateObject($headNote);

			// Add completion as a note
			if ($dateCompleted) {
				$completionNote = $noteDao->newDataObject();
				$completionNote->setAssocType(ASSOC_TYPE_QUERY);
				$completionNote->setAssocId($query->getId());
				$completionNote->setContents('The assignment is complete.');
				$completionNote->setUserId($userId);
				$noteDao->insertObject($completionNote);
				$completionNote->setDateCreated($dateCompleted);
				$noteDao->updateObject($completionNote);
			}

			$this->_transferSignoffData($signoffId, $query->getId());
		}
		$filesResult->Close();
		return true;
	}

	/**
	 * The assoc_type = ASSOC_TYPE_REPRESENTATION (from SIGNOFF_PROOFING migration)
	 * should be changed to assoc_type = ASSOC_TYPE_SUBMISSION, for queries to be
	 * displayed in the production discussions list.
	 * After changing this, the submission queries should be resequenced, in their
	 * order in the DB table.
	 * @return boolean True indicates success.
	 */
	function fixQueriesAssocTypes() {
		// Get queries by submission ids, in order to resequence them correctly after the assoc_type change
		$queryDao = DAORegistry::getDAO('QueryDAO');
		$allQueriesResult = $queryDao->retrieve(
			'SELECT DISTINCT q.*,
				COALESCE(pf.submission_id, qs.assoc_id) AS submission_id
			FROM queries q
			LEFT JOIN publication_formats pf ON (q.assoc_type = ? AND q.assoc_id = pf.publication_format_id AND q.stage_id = ?)
			LEFT JOIN queries qs ON (qs.assoc_type = ?)
			WHERE q.assoc_type = ? OR q.assoc_type = ?
			ORDER BY query_id',
			array((int) ASSOC_TYPE_REPRESENTATION, (int) WORKFLOW_STAGE_ID_PRODUCTION, (int) ASSOC_TYPE_SUBMISSION, (int) ASSOC_TYPE_SUBMISSION, (int) ASSOC_TYPE_REPRESENTATION)
		);
		$allQueries = array();
		while (!$allQueriesResult->EOF) {
			$row = $allQueriesResult->getRowAssoc(false);
			$allQueries[$row['submission_id']]['queries'][] = $query = $queryDao->_fromRow($row);
			// mark if this submission queries should be fixed
			$fix = array_key_exists('fix', $allQueries[$row['submission_id']]) ? $allQueries[$row['submission_id']]['fix'] : false;
			$allQueries[$row['submission_id']]['fix'] = ($query->getAssocType() == ASSOC_TYPE_REPRESENTATION) || $fix;
			$allQueriesResult->MoveNext();
		}
		$allQueriesResult->Close();
		foreach ($allQueries as $submissionId => $queriesBySubmission) {
			// Touch i.e. fix and resequence only the submission queries that contained assoc_type = ASSOC_TYPE_REPRESENTATION
			if ($allQueries[$submissionId]['fix']) {
				$i = 1;
				foreach($queriesBySubmission['queries'] as $query) {
					if ($query->getAssocType() == ASSOC_TYPE_REPRESENTATION) {
						$query->setAssocType(ASSOC_TYPE_SUBMISSION);
						$query->setAssocId($submissionId);
					}
					$query->setSequence($i);
					$queryDao->updateObject($query);
					$i++;
				}
			}
		}
		return true;
	}

	/**
	 * Convert comments to editors to queries.
	 * @return boolean True indicates success.
	 */
	function convertCommentsToEditor() {
		$submissionDao = Application::getSubmissionDAO();
		$stageAssignmetDao = DAORegistry::getDAO('StageAssignmentDAO');
		$queryDao = DAORegistry::getDAO('QueryDAO');
		$noteDao = DAORegistry::getDAO('NoteDAO');
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');

		import('lib.pkp.classes.security.Role'); // ROLE_ID_...

		$commentsResult = $submissionDao->retrieve(
			'SELECT s.submission_id, s.context_id, s.comments_to_ed, s.date_submitted
			FROM submissions_tmp s
			WHERE s.comments_to_ed IS NOT NULL AND s.comments_to_ed != \'\''
		);
		while (!$commentsResult->EOF) {
			$row = $commentsResult->getRowAssoc(false);
			$comments_to_ed = PKPString::stripUnsafeHtml($row['comments_to_ed']);
			if ($comments_to_ed != ""){
				$userId = null;
				$authorAssignmentsResult = $stageAssignmetDao->getBySubmissionAndRoleId($row['submission_id'], ROLE_ID_AUTHOR);
				if ($authorAssignmentsResult->getCount() != 0) {
					// We assume the results are ordered by stage_assignment_id i.e. first author assignemnt is first
					$userId = $authorAssignmentsResult->next()->getUserId();
				} else {
					$managerUserGroup = $userGroupDao->getDefaultByRoleId($row['context_id'], ROLE_ID_MANAGER);
					$managerUsers = $userGroupDao->getUsersById($managerUserGroup->getId(), $row['context_id']);
					$userId = $managerUsers->next()->getId();
				}
				assert($userId);

				$query = $queryDao->newDataObject();
				$query->setAssocType(ASSOC_TYPE_SUBMISSION);
				$query->setAssocId($row['submission_id']);
				$query->setStageId(WORKFLOW_STAGE_ID_SUBMISSION);
				$query->setSequence(REALLY_BIG_NUMBER);

				$queryDao->insertObject($query);
				$queryDao->resequence(ASSOC_TYPE_SUBMISSION, $row['submission_id']);
				$queryDao->insertParticipant($query->getId(), $userId);

				$queryId = $query->getId();

				$note = $noteDao->newDataObject();
				$note->setUserId($userId);
				$note->setAssocType(ASSOC_TYPE_QUERY);
				$note->setTitle('Cover Note to Editor');
				$note->setContents($comments_to_ed);
				$note->setDateCreated(strtotime($row['date_submitted']));
				$note->setDateModified(strtotime($row['date_submitted']));
				$note->setAssocId($queryId);
				$noteDao->insertObject($note);
			}
			$commentsResult->MoveNext();
		}
		$commentsResult->Close();

		// remove temporary table
		$submissionDao->update('DROP TABLE submissions_tmp');

		return true;
	}

	/**
	 * Private function to reassign signoff notes and files to queries.
	 * @param $signoffId int Signoff ID
	 * @param $queryId int Query ID
	 */
	private function _transferSignoffData($signoffId, $queryId) {
		$noteDao = DAORegistry::getDAO('NoteDAO');
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$userDao = DAORegistry::getDAO('UserDAO');

		$notes = $noteDao->getByAssoc(1048582 /* ASSOC_TYPE_SIGNOFF */, $signoffId);
		while ($note = $notes->next()) {
			$note->setAssocType(ASSOC_TYPE_QUERY);
			$note->setAssocId($queryId);
			$noteDao->updateObject($note);

			// Convert any attached files
			$submissionFiles = $submissionFileDao->getAllRevisionsByAssocId(ASSOC_TYPE_NOTE, $note->getId());
			foreach ($submissionFiles as $submissionFile) {
				$submissionFile->setAssocType(ASSOC_TYPE_NOTE);
				$submissionFile->setAssocId($note->getId());
				$submissionFile->setFileStage(SUBMISSION_FILE_QUERY);
				$submissionFileDao->updateObject($submissionFile);
			}
		}

		// Transfer signoff signoffs into notes
		$signoffsResult = $submissionFileDao->retrieve(
			'SELECT * FROM signoffs WHERE symbolic = ? AND assoc_type = ? AND assoc_id = ?',
			array('SIGNOFF_SIGNOFF', 1048582 /* ASSOC_TYPE_SIGNOFF */, $signoffId)
		);
		while (!$signoffsResult->EOF) {
			$row = $signoffsResult->getRowAssoc(false);
			$metaSignoffId = $row['signoff_id'];
			$userId = $row['user_id'];
			$dateCompleted = $row['date_completed']?strtotime($row['date_completed']):null;
			$signoffsResult->MoveNext();

			if ($dateCompleted) {
				$user = $userDao->getById($userId);
				$note = $noteDao->newDataObject();
				$note->setAssocType(ASSOC_TYPE_QUERY);
				$note->setAssocId($queryId);
				$note->setUserId($userId);
				$note->setContents('The completed task has been reviewed by ' . htmlspecialchars($user->getFullName()) . ' (' . $user->getEmail() . ').');
				$noteDao->insertObject($note);
				$note->setDateCreated(Core::getCurrentDate());
				$noteDao->updateObject($note);
			}
			$submissionFileDao->update('DELETE FROM signoffs WHERE signoff_id=?', array($metaSignoffId));
		}
		$signoffsResult->Close();

		$submissionFileDao->update('DELETE FROM signoffs WHERE signoff_id=?', array($signoffId));
	}

	/**
	 * If StaticPages table exists we should port the data as NMIs
	 * @return boolean
	 */
	function migrateStaticPagesToNavigationMenuItems() {
		if ($this->tableExists('static_pages')) {
			$contextDao = Application::getContextDAO();
			$navigationMenuItemDao = DAORegistry::getDAO('NavigationMenuItemDAO');

			import('plugins.generic.staticPages.classes.StaticPagesDAO');

			$staticPagesDao = new StaticPagesDAO();

			$contexts = $contextDao->getAll();
			while ($context = $contexts->next()) {
				$contextStaticPages = $staticPagesDao->getByContextId($context->getId())->toAssociativeArray();
				foreach($contextStaticPages as $staticPage) {
					$retNMIId = $navigationMenuItemDao->portStaticPage($staticPage);
					if ($retNMIId) {
						$staticPagesDao->deleteById($staticPage->getId());
					} else {
						error_log('WARNING: The StaticPage "' . $staticPage->getLocalizedTitle() . '" uses a path (' . $staticPage->getPath() . ') that conflicts with an existing Custom Navigation Menu Item path. Skipping this StaticPage.');
					}
				}
			}
		}

		return true;
	}

	/**
	 * Migrate first and last user names as multilingual into the DB table user_settings.
	 * @return boolean
	 */
	function migrateUserAndAuthorNames() {
		$userDao = DAORegistry::getDAO('UserDAO');
		import('lib.pkp.classes.identity.Identity'); // IDENTITY_SETTING_...
		// the user names will be saved in the site's primary locale
		$userDao->update("INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type) SELECT DISTINCT u.user_id, s.primary_locale, ?, u.first_name, 'string' FROM users_tmp u, site s", array(IDENTITY_SETTING_GIVENNAME));
		$userDao->update("INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type) SELECT DISTINCT u.user_id, s.primary_locale, ?, u.last_name, 'string' FROM users_tmp u, site s", array(IDENTITY_SETTING_FAMILYNAME));
		// the author names will be saved in the submission's primary locale
		$userDao->update("INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) SELECT DISTINCT a.author_id, s.locale, ?, a.first_name, 'string' FROM authors_tmp a, submissions s WHERE s.submission_id = a.submission_id", array(IDENTITY_SETTING_GIVENNAME));
		$userDao->update("INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) SELECT DISTINCT a.author_id, s.locale, ?, a.last_name, 'string' FROM authors_tmp a, submissions s WHERE s.submission_id = a.submission_id", array(IDENTITY_SETTING_FAMILYNAME));

		// middle name will be migrated to the given name
		// note that given names are already migrated to the settings table
		$driver = $userDao->getDriver();
		switch ($driver) {
			case 'mysql':
			case 'mysqli':
				// the alias for _settings table cannot be used for some reason -- syntax error
				$userDao->update("UPDATE user_settings, users_tmp u SET user_settings.setting_value = CONCAT(user_settings.setting_value, ' ', u.middle_name) WHERE user_settings.setting_name = ? AND u.user_id = user_settings.user_id AND u.middle_name IS NOT NULL AND u.middle_name <> ''", array(IDENTITY_SETTING_GIVENNAME));
				$userDao->update("UPDATE author_settings, authors_tmp a SET author_settings.setting_value = CONCAT(author_settings.setting_value, ' ', a.middle_name) WHERE author_settings.setting_name = ? AND a.author_id = author_settings.author_id AND a.middle_name IS NOT NULL AND a.middle_name <> ''", array(IDENTITY_SETTING_GIVENNAME));
				break;
			case 'postgres':
				$userDao->update("UPDATE user_settings SET setting_value = CONCAT(setting_value, ' ', u.middle_name) FROM users_tmp u WHERE user_settings.setting_name = ? AND u.user_id = user_settings.user_id AND u.middle_name IS NOT NULL AND u.middle_name <> ''", array(IDENTITY_SETTING_GIVENNAME));
				$userDao->update("UPDATE author_settings SET setting_value = CONCAT(setting_value, ' ', a.middle_name) FROM authors_tmp a WHERE author_settings.setting_name = ? AND a.author_id = author_settings.author_id AND a.middle_name IS NOT NULL AND a.middle_name <> ''", array(IDENTITY_SETTING_GIVENNAME));
				break;
			default: fatalError('Unknown database type!');
		}

		// salutation and suffix will be migrated to the preferred public name
		// user preferred public names will be inserted for each supported site locales
		$siteDao = DAORegistry::getDAO('SiteDAO');
		$site = $siteDao->getSite();
		$supportedLocales = $site->getSupportedLocales();
		$userResult = $userDao->retrieve("
			SELECT user_id, first_name, last_name, middle_name, salutation, suffix FROM users_tmp
			WHERE (salutation IS NOT NULL AND salutation <> '') OR
				(suffix IS NOT NULL AND suffix <> '')
		");
		while (!$userResult->EOF) {
			$row = $userResult->GetRowAssoc(false);
			$userId = $row['user_id'];
			$firstName = $row['first_name'];
			$lastName = $row['last_name'];
			$middleName = $row['middle_name'];
			$salutation = $row['salutation'];
			$suffix = $row['suffix'];
			foreach ($supportedLocales as $siteLocale) {
				$preferredPublicName = ($salutation != '' ? "$salutation " : '') . "$firstName " . ($middleName != '' ? "$middleName " : '') . $lastName . ($suffix != '' ? ", $suffix" : '');
				if (AppLocale::isLocaleWithFamilyFirst($siteLocale)) {
					$preferredPublicName = "$lastName, " . ($salutation != '' ? "$salutation " : '') . $firstName . ($middleName != '' ? " $middleName" : '');
				}
				$params = array((int) $userId, $siteLocale, $preferredPublicName);
				$userDao->update("INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type) VALUES (?, ?, 'preferredPublicName', ?, 'string')", $params);
			}
			$userResult->MoveNext();
		}
		$userResult->Close();

		// author suffix will be migrated to the author preferred public name
		// author preferred public names will be inserted for each press supported locale
		// get supported locales for the press (there shold actually be only one press)
		$pressDao = DAORegistry::getDAO('PressDAO');
		$presses = $pressDao->getAll();
		$pressessSupportedLocales = array();
		while ($press = $presses->next()) {
			$pressessSupportedLocales[$press->getId()] = $press->getSupportedLocales();
		}
		// get all authors with a suffix
		$authorResult = $userDao->retrieve("
			SELECT a.author_id, a.first_name, a.last_name, a.middle_name, a.suffix, p.press_id FROM authors_tmp a
			LEFT JOIN submissions s ON (s.submission_id = a.submission_id)
			LEFT JOIN presses p ON (p.press_id = s.context_id)
			WHERE suffix IS NOT NULL AND suffix <> ''
		");
		while (!$authorResult->EOF) {
			$row = $authorResult->GetRowAssoc(false);
			$authorId = $row['author_id'];
			$firstName = $row['first_name'];
			$lastName = $row['last_name'];
			$middleName = $row['middle_name'];
			$suffix = $row['suffix'];
			$pressId = $row['press_id'];
			$supportedLocales = $pressessSupportedLocales[$pressId];
			foreach ($supportedLocales as $locale) {
				$preferredPublicName = "$firstName " . ($middleName != '' ? "$middleName " : '') . $lastName . ($suffix != '' ? ", $suffix" : '');
				if (AppLocale::isLocaleWithFamilyFirst($locale)) {
					$preferredPublicName = "$lastName, " . $firstName . ($middleName != '' ? " $middleName" : '');
				}
				$params = array((int) $authorId, $locale, $preferredPublicName);
				$userDao->update("INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) VALUES (?, ?, 'preferredPublicName', ?, 'string')", $params);
			}
			$authorResult->MoveNext();
		}
		$authorResult->Close();

		// remove temporary table
		$siteDao->update('DROP TABLE users_tmp');
		$siteDao->update('DROP TABLE authors_tmp');
		return true;
	}

}


