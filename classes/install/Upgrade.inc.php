<?php

/**
 * @file classes/install/Upgrade.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Upgrade
 * @ingroup install
 *
 * @brief Perform system upgrade.
 */
use Illuminate\Database\Capsule\Manager as Capsule;


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
		$pressDao = DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$submissionDao = DAORegistry::getDAO('SubmissionDAO'); /* @var $submissionDao SubmissionDAO */
		DAORegistry::getDAO('GenreDAO'); // Load constants
		$siteDao = DAORegistry::getDAO('SiteDAO'); /* @var $siteDao SiteDAO */
		$site = $siteDao->getSite();
		$adminEmail = $site->getLocalizedContactEmail();
		import('lib.pkp.classes.submission.SubmissionFile'); // SUBMISSION_FILE_ constants
		import('lib.pkp.classes.file.FileManager');
		$fileManager = new FileManager();

		$contexts = $pressDao->getAll();
		while ($context = $contexts->next()) {
			$submissions = $submissionDao->getByContextId($context->getId());
			while ($submission = $submissions->next()) {
				$submissionDir = Services::get('submissionFile')->getSubmissionDir($context->getId(), $submission->getId());
				$rows = Capsule::table('submission_files')
					->where('submission_id', '=', $submission->getId())
					->get();
				foreach ($rows as $row) {
					$generatedFilename = sprintf(
						'%d-%s-%d-%d-%d-%s.%s',
						$row->submission_id,
						$row->genre_id,
						$row->file_id,
						$row->revision,
						$row->file_stage,
						date('Ymd', strtotime($row->date_uploaded)),
						strtolower_codesafe($fileManager->parseFileExtension($row->original_file_name))
					);
					$basePath = sprintf(
						'%s/%s/%s/',
						Config::getVar('files', 'files_dir'),
						$submissionDir,
						$this->_fileStageToPath($row->file_stage)
					);
					$globPattern = $$row->submission_id . '-' .
						'*' . '-' . // Genre name and designation globbed (together)
						$row->file_id . '-' .
						$row->revision . '-' .
						$row->file_stage . '-' .
						date('Ymd', strtotime($row->date_uploaded)) .
						'.' . strtolower_codesafe($fileManager->parseFileExtension($row->original_file_name));

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
		$pressDao = DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$contexts = $pressDao->getAll();
		$pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO'); /* @var $pluginSettingsDao PluginSettingsDAO */

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
		$dao = DAORegistry::getDAO('UserDAO'); /* @var $dao DAO */
		$tablesToUpdate = [
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
			'item_views'
		];

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
		$authorDao = DAORegistry::getDAO('AuthorDAO'); /* @var $authorDao AuthorDAO */

		// Get all authors with broken data
		$result = $authorDao->retrieve(
			'SELECT DISTINCT author_id
			FROM	author_settings
			WHERE	(setting_name = ? OR setting_name = ?)
				AND setting_type = ?',
			['affiliation', 'biography', 'object']
		);

		foreach ($result as $row) {
			$authorId = $row->author_id;

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
		return true;
	}

	/**
	 * Convert email templates to HTML.
	 * @return boolean True indicates success.
	 */
	function htmlifyEmailTemplates() {
		$emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */

		// Convert the email templates in email_templates_data to localized
		$result = $emailTemplateDao->retrieve('SELECT * FROM email_templates_data');
		foreach ($result as $row) {
			$emailTemplateDao->update(
				'UPDATE	email_templates_data
				SET	body = ?
				WHERE	email_key = ? AND
					locale = ? AND
					assoc_type = ? AND
					assoc_id = ?',
				[
					preg_replace('/{\$[a-zA-Z]+Url}/', '<a href="\0">\0</a>', nl2br($row->body)),
					$row->email_key,
					$row->locale,
					$row->assoc_type,
					$row->assoc_id
				]
			);
		}

		// Convert the email templates in email_templates_default_data to localized
		$result = $emailTemplateDao->retrieve('SELECT * FROM email_templates_default_data');
		foreach ($result as $row) {
			$emailTemplateDao->update(
				'UPDATE	email_templates_default_data
				SET	body = ?
				WHERE	email_key = ? AND
					locale = ?',
				[
					preg_replace('/{\$[a-zA-Z]+Url}/', '<a href="\0">\0</a>', nl2br($row->body)),
					$row->email_key,
					$row->locale,
				]
			);
		}

		// Localize the email header and footer fields.
		$contextDao = DAORegistry::getDAO('PressDAO'); /* @var $contextDao PressDAO */
		$settingsDao = DAORegistry::getDAO('PressSettingsDAO'); /* @var $settingsDao PressSettingsDAO */
		$contexts = $contextDao->getAll();
		while ($context = $contexts->next()) {
			foreach (['emailFooter', 'emailSignature'] as $settingName) {
				$settingsDao->updateSetting(
					$context->getId(),
					$settingName,
					$context->getData('emailHeader'),
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
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		import('lib.pkp.classes.submission.SubmissionFile');

		$filesResult = $submissionFileDao->retrieve(
			'SELECT DISTINCT sf.file_id, sf.assoc_type, sf.assoc_id, sf.submission_id, sf.original_file_name, sf.revision, s.symbolic, s.date_notified, s.date_completed, s.user_id, s.signoff_id FROM submission_files sf, signoffs s WHERE s.assoc_type=? AND s.assoc_id=sf.file_id AND s.symbolic IN (?, ?)',
			[ASSOC_TYPE_SUBMISSION_FILE, 'SIGNOFF_COPYEDITING', 'SIGNOFF_PROOFING']
		);

		$queryDao = DAORegistry::getDAO('QueryDAO'); /* @var $queryDao QueryDAO */
		$noteDao = DAORegistry::getDAO('NoteDAO'); /* @var $noteDao NoteDAO */
		$userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
		$stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */

		//
		// 1. Go through all signoff/file pairs and migrate them into queries.
		// Queries should be created per file and users should be consolidated
		// from potentially multiple audit assignments into fewer queries.
		//
		foreach ($filesResult as $row) {
			$fileId = $row->file_id;
			$symbolic = $row->symbolic;
			$dateNotified = $row->date_notified?strtotime($row->date_notified):null;
			$dateCompleted = $row->date_completed?strtotime($row->date_completed):null;
			$userId = $row->user_id;
			$signoffId = $row->signoff_id;
			$fileAssocType = $row->assoc_type;
			$fileAssocId = $row->assoc_id;
			$submissionId = $row->submission_id;
			$originalFileName = $row->original_file_name;
			$revision = $row->revision;

			// Reproduces removed SubmissionFile::getFileLabel() method
			$label = $originalFileName;
			$filename = Capsule::table('submission_file_settings')
				->where('file_id', '=', $fileId)
				->where('setting_name', '=', 'name')
				->first();
			if ($filename) {
				$label = $filename->setting_value;
			}
			if ($revision) {
				$label .= '(' . $revision . ')';
			}

			$assocType = $assocId = $query = null; // Prevent PHP scrutinizer warnings
			switch ($symbolic) {
				case 'SIGNOFF_COPYEDITING':
					$query = $queryDao->newDataObject();
					$query->setAssocType($assocType = ASSOC_TYPE_SUBMISSION);
					$query->setAssocId($assocId = $submissionId);
					$query->setStageId(WORKFLOW_STAGE_ID_EDITING);
					break;
				case 'SIGNOFF_PROOFING':
					// We've already migrated a signoff for this file; add this user to it too.
					if ($fileAssocType == ASSOC_TYPE_NOTE) {
						$note = $noteDao->getById($fileAssocId);
						assert($note && $note->getAssocType() == ASSOC_TYPE_QUERY);
						if (count($queryDao->getParticipantIds($note->getAssocId(), $userId))==0) $queryDao->insertParticipant($fileAssocId, $userId);
						$this->_transferSignoffData($signoffId, $note->getAssocId());
						continue 2;
					}
					$query = $queryDao->newDataObject();
					assert($fileAssocType==ASSOC_TYPE_REPRESENTATION);
					$query->setAssocType($assocType = ASSOC_TYPE_SUBMISSION);
					$query->setAssocId($assocId = $submissionId);
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
			$assignedUserIds = [$userId];
			foreach ([ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT] as $roleId) {
				$stageAssignments = $stageAssignmentDao->getBySubmissionAndRoleId($submissionId, $roleId, $query->getStageId());
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
					$headNote->setTitle('Copyediting for "' . $label . '"');
					$headNote->setContents('Auditing assignment for the file "' . htmlspecialchars($label) . '" (Signoff ID: ' . $signoffId . ')');
					break;
				case 'SIGNOFF_PROOFING':
					$headNote->setTitle('Proofreading for ' . $label);
					$headNote->setContents('Proofing assignment for the file "' . htmlspecialchars($label) . '" (Signoff ID: ' . $signoffId . ')');
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
		$queryDao = DAORegistry::getDAO('QueryDAO'); /* @var $queryDao QueryDAO */
		$allQueriesResult = $queryDao->retrieve(
			'SELECT DISTINCT q.*,
				COALESCE(pf.submission_id, qs.assoc_id) AS submission_id
			FROM queries q
			LEFT JOIN publication_formats pf ON (q.assoc_type = ? AND q.assoc_id = pf.publication_format_id AND q.stage_id = ?)
			LEFT JOIN queries qs ON (qs.assoc_type = ?)
			WHERE q.assoc_type = ? OR q.assoc_type = ?
			ORDER BY query_id',
			[(int) ASSOC_TYPE_REPRESENTATION, (int) WORKFLOW_STAGE_ID_PRODUCTION, (int) ASSOC_TYPE_SUBMISSION, (int) ASSOC_TYPE_SUBMISSION, (int) ASSOC_TYPE_REPRESENTATION]
		);
		$allQueries = [];
		foreach ($allQueriesResult as $row) {
			$allQueries[$row->submission_id]['queries'][] = $query = $queryDao->_fromRow((array) $row);
			// mark if this submission queries should be fixed
			$fix = array_key_exists('fix', $allQueries[$row->submission_id]) ? $allQueries[$row->submission_id]['fix'] : false;
			$allQueries[$row->submission_id]['fix'] = ($query->getAssocType() == ASSOC_TYPE_REPRESENTATION) || $fix;
		}
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
		$submissionDao = DAORegistry::getDAO('SubmissionDAO'); /* @var $submissionDao SubmissionDAO */
		$stageAssignmetDao = DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmetDao StageAssignmentDAO */
		$queryDao = DAORegistry::getDAO('QueryDAO'); /* @var $queryDao QueryDAO */
		$noteDao = DAORegistry::getDAO('NoteDAO'); /* @var $noteDao NoteDAO */
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */

		import('lib.pkp.classes.security.Role'); // ROLE_ID_...

		$commentsResult = $submissionDao->retrieve(
			'SELECT s.submission_id, s.context_id, s.comments_to_ed, s.date_submitted
			FROM submissions_tmp s
			WHERE s.comments_to_ed IS NOT NULL AND s.comments_to_ed != \'\''
		);
		foreach ($commentsResult as $row) {
			$commentsToEd = PKPString::stripUnsafeHtml($row->comments_to_ed);
			if ($commentsToEd == '') continue;

			$authorAssignments = $stageAssignmetDao->getBySubmissionAndRoleId($row->submission_id, ROLE_ID_AUTHOR);
			if ($authorAssignment = $authorAssignments->next()) {
				// We assume the results are ordered by stage_assignment_id i.e. first author assignemnt is first
				$userId = $authorAssignment->getUserId();
			} else {
				$managerUserGroup = $userGroupDao->getDefaultByRoleId($row->context_id, ROLE_ID_MANAGER);
				$managerUsers = $userGroupDao->getUsersById($managerUserGroup->getId(), $row->context_id);
				$userId = $managerUsers->next()->getId();
			}
			assert($userId);

			$query = $queryDao->newDataObject();
			$query->setAssocType(ASSOC_TYPE_SUBMISSION);
			$query->setAssocId($row->submission_id);
			$query->setStageId(WORKFLOW_STAGE_ID_SUBMISSION);
			$query->setSequence(REALLY_BIG_NUMBER);

			$queryDao->insertObject($query);
			$queryDao->resequence(ASSOC_TYPE_SUBMISSION, $row->submission_id);
			$queryDao->insertParticipant($query->getId(), $userId);

			$queryId = $query->getId();

			$note = $noteDao->newDataObject();
			$note->setUserId($userId);
			$note->setAssocType(ASSOC_TYPE_QUERY);
			$note->setTitle('Cover Note to Editor');
			$note->setContents($commentsToEd);
			$note->setDateCreated(strtotime($row->date_submitted));
			$note->setDateModified(strtotime($row->date_submitted));
			$note->setAssocId($queryId);
			$noteDao->insertObject($note);
		}

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
		$noteDao = DAORegistry::getDAO('NoteDAO'); /* @var $noteDao NoteDAO */
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */

		$notes = $noteDao->getByAssoc(1048582 /* ASSOC_TYPE_SIGNOFF */, $signoffId);
		while ($note = $notes->next()) {
			$note->setAssocType(ASSOC_TYPE_QUERY);
			$note->setAssocId($queryId);
			$noteDao->updateObject($note);

			// Convert any attached files
			$submissionFilesIterator = Services::get('submissionFile')->getMany([
				'assocTypes' => [ASSOC_TYPE_NOTE],
				'assocIds' => [$note->getId()],
			]);
			foreach ($submissionFilesIterator as $submissionFile) {
				$submissionFile->setData('fileStage', SUBMISSION_FILE_QUERY);
				$submissionFileDao->updateObject($submissionFile);
			}
		}

		// Transfer signoff signoffs into notes
		$signoffsResult = $submissionFileDao->retrieve(
			'SELECT * FROM signoffs WHERE symbolic = ? AND assoc_type = ? AND assoc_id = ?',
			['SIGNOFF_SIGNOFF', 1048582 /* ASSOC_TYPE_SIGNOFF */, $signoffId]
		);
		foreach ($signoffsResult as $row) {
			$metaSignoffId = $row->signoff_id;
			$userId = $row->user_id;
			$dateCompleted = $row->date_completed ? strtotime($row->date_completed) : null;

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
			$submissionFileDao->update('DELETE FROM signoffs WHERE signoff_id=?', [$metaSignoffId]);
		}

		$submissionFileDao->update('DELETE FROM signoffs WHERE signoff_id=?', [$signoffId]);
	}

	/**
	 * If StaticPages table exists we should port the data as NMIs
	 * @return boolean
	 */
	function migrateStaticPagesToNavigationMenuItems() {
		if ($this->tableExists('static_pages')) {
			$contextDao = Application::getContextDAO();
			$navigationMenuItemDao = DAORegistry::getDAO('NavigationMenuItemDAO'); /* @var $navigationMenuItemDao NavigationMenuItemDAO */

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
		$userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
		import('lib.pkp.classes.identity.Identity'); // IDENTITY_SETTING_...
		// the user names will be saved in the site's primary locale
		$userDao->update("INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type) SELECT DISTINCT u.user_id, s.primary_locale, ?, u.first_name, 'string' FROM users_tmp u, site s", [IDENTITY_SETTING_GIVENNAME]);
		$userDao->update("INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type) SELECT DISTINCT u.user_id, s.primary_locale, ?, u.last_name, 'string' FROM users_tmp u, site s", [IDENTITY_SETTING_FAMILYNAME]);
		// the author names will be saved in the submission's primary locale
		$userDao->update("INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) SELECT DISTINCT a.author_id, s.locale, ?, a.first_name, 'string' FROM authors_tmp a, submissions s WHERE s.submission_id = a.submission_id", [IDENTITY_SETTING_GIVENNAME]);
		$userDao->update("INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) SELECT DISTINCT a.author_id, s.locale, ?, a.last_name, 'string' FROM authors_tmp a, submissions s WHERE s.submission_id = a.submission_id", [IDENTITY_SETTING_FAMILYNAME]);

		// middle name will be migrated to the given name
		// note that given names are already migrated to the settings table
		switch (Config::getVar('database', 'driver')) {
			case 'mysql':
			case 'mysqli':
				// the alias for _settings table cannot be used for some reason -- syntax error
				$userDao->update("UPDATE user_settings, users_tmp u SET user_settings.setting_value = CONCAT(user_settings.setting_value, ' ', u.middle_name) WHERE user_settings.setting_name = ? AND u.user_id = user_settings.user_id AND u.middle_name IS NOT NULL AND u.middle_name <> ''", [IDENTITY_SETTING_GIVENNAME]);
				$userDao->update("UPDATE author_settings, authors_tmp a SET author_settings.setting_value = CONCAT(author_settings.setting_value, ' ', a.middle_name) WHERE author_settings.setting_name = ? AND a.author_id = author_settings.author_id AND a.middle_name IS NOT NULL AND a.middle_name <> ''", [IDENTITY_SETTING_GIVENNAME]);
				break;
			case 'postgres':
			case 'postgres64':
			case 'postgres7':
			case 'postgres8':
			case 'postgres9':
				$userDao->update("UPDATE user_settings SET setting_value = CONCAT(setting_value, ' ', u.middle_name) FROM users_tmp u WHERE user_settings.setting_name = ? AND u.user_id = user_settings.user_id AND u.middle_name IS NOT NULL AND u.middle_name <> ''", [IDENTITY_SETTING_GIVENNAME]);
				$userDao->update("UPDATE author_settings SET setting_value = CONCAT(setting_value, ' ', a.middle_name) FROM authors_tmp a WHERE author_settings.setting_name = ? AND a.author_id = author_settings.author_id AND a.middle_name IS NOT NULL AND a.middle_name <> ''", [IDENTITY_SETTING_GIVENNAME]);
				break;
			default: throw new Exception('Unknown database type!');
		}

		// salutation and suffix will be migrated to the preferred public name
		// user preferred public names will be inserted for each supported site locales
		$siteDao = DAORegistry::getDAO('SiteDAO'); /* @var $siteDao SiteDAO */
		$site = $siteDao->getSite();
		$supportedLocales = $site->getSupportedLocales();
		$userResult = $userDao->retrieve(
			"SELECT user_id, first_name, last_name, middle_name, salutation, suffix FROM users_tmp
			WHERE (salutation IS NOT NULL AND salutation <> '') OR
				(suffix IS NOT NULL AND suffix <> '')"
		);
		foreach ($userResult as $row) {
			$userId = $row->user_id;
			$firstName = $row->first_name;
			$lastName = $row->last_name;
			$middleName = $row->middle_name;
			$salutation = $row->salutation;
			$suffix = $row->suffix;
			foreach ($supportedLocales as $siteLocale) {
				$preferredPublicName = ($salutation != '' ? "$salutation " : '') . "$firstName " . ($middleName != '' ? "$middleName " : '') . $lastName . ($suffix != '' ? ", $suffix" : '');
				if (AppLocale::isLocaleWithFamilyFirst($siteLocale)) {
					$preferredPublicName = "$lastName, " . ($salutation != '' ? "$salutation " : '') . $firstName . ($middleName != '' ? " $middleName" : '');
				}
				$userDao->update(
					"INSERT INTO user_settings (user_id, locale, setting_name, setting_value, setting_type) VALUES (?, ?, 'preferredPublicName', ?, 'string')",
					[(int) $userId, $siteLocale, $preferredPublicName]
				);
			}
		}

		// author suffix will be migrated to the author preferred public name
		// author preferred public names will be inserted for each press supported locale
		// get supported locales for the press (there shold actually be only one press)
		$pressDao = DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$presses = $pressDao->getAll();
		$pressessSupportedLocales = [];
		while ($press = $presses->next()) {
			$pressessSupportedLocales[$press->getId()] = $press->getSupportedLocales();
		}
		// get all authors with a suffix
		$authorResult = $userDao->retrieve(
			"SELECT a.author_id, a.first_name, a.last_name, a.middle_name, a.suffix, p.press_id FROM authors_tmp a
			LEFT JOIN submissions s ON (s.submission_id = a.submission_id)
			LEFT JOIN presses p ON (p.press_id = s.context_id)
			WHERE suffix IS NOT NULL AND suffix <> ''"
		);
		foreach ($authorResult as $row) {
			$authorId = $row->author_id;
			$firstName = $row->first_name;
			$lastName = $row->last_name;
			$middleName = $row->middle_name;
			$suffix = $row->suffix;
			$pressId = $row->press_id;
			$supportedLocales = $pressessSupportedLocales[$pressId];
			foreach ($supportedLocales as $locale) {
				$preferredPublicName = "$firstName " . ($middleName != '' ? "$middleName " : '') . $lastName . ($suffix != '' ? ", $suffix" : '');
				if (AppLocale::isLocaleWithFamilyFirst($locale)) {
					$preferredPublicName = "$lastName, " . $firstName . ($middleName != '' ? " $middleName" : '');
				}
				$userDao->update(
					"INSERT INTO author_settings (author_id, locale, setting_name, setting_value, setting_type) VALUES (?, ?, 'preferredPublicName', ?, 'string')",
					[(int) $authorId, $locale, $preferredPublicName]
				);
			}
		}

		// remove temporary table
		$siteDao->update('DROP TABLE users_tmp');
		$siteDao->update('DROP TABLE authors_tmp');
		return true;
	}

	/**
	 * Update permit_metadata_edit and can_change_metadata for user_groups and stage_assignments tables.
	 *
	 * @return boolean True indicates success.
	 */
	function changeUserRolesAndStageAssignmentsForStagePermitSubmissionEdit() {
		$stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO'); /** @var $stageAssignmentDao StageAssignmentDAO */
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO'); /** @var $userGroupDao UserGroupDAO */

		$roles = UserGroupDAO::getNotChangeMetadataEditPermissionRoles();
		$roleString = '(' . implode(",", $roles) . ')';

		$userGroupDao->update('UPDATE user_groups SET permit_metadata_edit = 1 WHERE role_id IN ' . $roleString);
		switch (Config::getVar('database', 'driver')) {
			case 'mysql':
			case 'mysqli':
				$stageAssignmentDao->update('UPDATE stage_assignments sa JOIN user_groups ug on sa.user_group_id = ug.user_group_id SET sa.can_change_metadata = 1 WHERE ug.role_id IN ' . $roleString);
				break;
			case 'postgres':
			case 'postgres64':
			case 'postgres7':
			case 'postgres8':
			case 'postgres9':
				$stageAssignmentDao->update('UPDATE stage_assignments sa SET can_change_metadata=1 FROM user_groups ug WHERE sa.user_group_id = ug.user_group_id AND ug.role_id IN ' . $roleString);
				break;
			default: throw new Exception("Unknown database type!");
			}

		return true;
	}

	/**
	 * Update how submission cover images are stored
	 *
	 * 1. Move the cover images into /public and rename them.
	 *
	 * 2. Change the coverImage setting to a multilingual setting
	 *    stored under the submission_settings table, which will
	 *    be migrated to the publication_settings table once it
	 *    is created.
	 */
	function migrateSubmissionCoverImages() {
		import('lib.pkp.classes.file.FileManager');
		import('classes.file.PublicFileManager');

		$fileManager = new \FileManager();
		$publicFileManager = new \PublicFileManager();
		$contexts = [];

		$submissionDao = DAORegistry::getDAO('SubmissionDAO'); /* @var $submissionDao SubmissionDAO */
		$result = $submissionDao->retrieve(
			'SELECT	ps.submission_id as submission_id,
				ps.cover_image as cover_image,
				s.context_id as context_id
			FROM	published_submissions ps
			LEFT JOIN	submissions s ON (s.submission_id = ps.submission_id)'
		);
		foreach ($result as $row) {
			if (empty($row->cover_image)) continue;
			$coverImage = unserialize($row->cover_image);
			if (empty($coverImage)) continue;

			if (!isset($contexts[$row->context_id])) {
				$contexts[$row->context_id] = Services::get('context')->get($row->context_id);
			};
			$context = $contexts[$row->context_id];

			// Get existing image paths
			$basePath = Services::get('submissionFile')->getSubmissionDir($row->context_id, $row->submission_id);
			$coverPath = Config::getVar('files', 'files_dir') . '/' . $basePath . '/simple/' . $coverImage['name'];
			$coverPathInfo = pathinfo($coverPath);
			$thumbPath = Config::getVar('files', 'files_dir') . '/' . $basePath . '/simple/' . $coverImage['thumbnailName'];
			$thumbPathInfo = pathinfo($thumbPath);

			// Copy the files to the public directory
			$newCoverPath = join('_', [
				'submission',
				$row->submission_id,
				$row->submission_id,
				'coverImage',
			]) . '.' . $coverPathInfo['extension'];
			$publicFileManager->copyContextFile(
				$row->context_id,
				$coverPath,
				$newCoverPath
			);
			$newThumbPath = join('_', [
				'submission',
				$row->submission_id,
				$row->submission_id,
				'coverImage',
				't'
			]) . '.' . $thumbPathInfo['extension'];
			$publicFileManager->copyContextFile(
				$row->context_id,
				$thumbPath,
				$newThumbPath
			);

			// Create a submission_settings entry for each locale
			if(isset($context)) {
				foreach ($context->getSupportedFormLocales() as $localeKey) {
					$newCoverPathInfo = pathinfo($newCoverPath);
					$submissionDao = DAORegistry::getDAO('SubmissionDAO');
					/* @var $submissionDao SubmissionDAO */
					$submissionDao->update(
						'INSERT INTO submission_settings (submission_id, setting_name, setting_value, setting_type, locale)
						VALUES (?, ?, ?, ?, ?)',
						[
							$row->submission_id,
							'coverImage',
							serialize([
								'uploadName' => $newCoverPathInfo['basename'],
								'dateUploaded' => $coverImage['dateUploaded'],
								'altText' => '',
							]),
							'object',
							$localeKey,
						]
					);
				}
			}

			// Delete the old images
			$fileManager->deleteByPath($coverPath);
			$fileManager->deleteByPath($thumbPath);
		}

		return true;
	}

	/**
	 * Get the directory of a file based on its file stage
	 *
	 * @param int $fileStage ONe of SUBMISSION_FILE_ constants
	 * @return string
	 */
	function _fileStageToPath($fileStage) {
		import('lib.pkp.classes.submission.SubmissionFile');
		static $fileStagePathMap = [
			SUBMISSION_FILE_SUBMISSION => 'submission',
			SUBMISSION_FILE_NOTE => 'note',
			SUBMISSION_FILE_REVIEW_FILE => 'submission/review',
			SUBMISSION_FILE_REVIEW_ATTACHMENT => 'submission/review/attachment',
			SUBMISSION_FILE_REVIEW_REVISION => 'submission/review/revision',
			SUBMISSION_FILE_FINAL => 'submission/final',
			SUBMISSION_FILE_COPYEDIT => 'submission/copyedit',
			SUBMISSION_FILE_DEPENDENT => 'submission/proof',
			SUBMISSION_FILE_PROOF => 'submission/proof',
			SUBMISSION_FILE_PRODUCTION_READY => 'submission/productionReady',
			SUBMISSION_FILE_ATTACHMENT => 'attachment',
			SUBMISSION_FILE_QUERY => 'submission/query',
		];

		if (!isset($fileStagePathMap[$fileStage])) {
			throw new Exception('A file assigned to the file stage ' . $fileStage . ' could not be migrated.');
		}

		return $fileStagePathMap[$fileStage];
	}
}


