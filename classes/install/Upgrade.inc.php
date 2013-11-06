<?php

/**
 * @file classes/install/Upgrade.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
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
	function Upgrade($params, $installFile = 'upgrade.xml', $isPlugin = false) {
		parent::Installer($installFile, $params, $isPlugin);
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
	 * @param $dryrun boolean True iff only a dry run (displaying rather than executing changes) should be done.
	 * @return boolean
	 */
	function fixFilenames($dryrun = false) {
		$pressDao = DAORegistry::getDAO('PressDAO');
		$submissionDao = DAORegistry::getDAO('MonographDAO');
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$genreDao = DAORegistry::getDAO('GenreDAO');

		import('classes.file.MonographFileManager');

		$contexts = $pressDao->getPresses();
		while ($context = $contexts->next()) {
			$submissions = $submissionDao->getByPressId($context->getId());
			while ($submission = $submissions->next()) {
				$submissionFileManager = new MonographFileManager($context->getId(), $submission->getId());
				$submissionFiles = $submissionFileDao->getBySubmissionId($submission->getId());
				foreach ($submissionFiles as $submissionFile) {
					$generatedFilename = $submissionFile->getFileName();
					$basePath = $submissionFileManager->getBasePath() . $submissionFile->_fileStageToPath($submissionFile->getFileStage()) . '/';
					$discoveredFilename = null;
					// Filename may have fallen victim to a designation, primary locale, or
					// genre name change. See if we can find it by generating a cartesian
					// product of all name/designation combos.
					$genreId = $submissionFile->getGenreId();
					$result = $genreDao->retrieve(
						'SELECT	designation.setting_value AS designation,
							name.setting_value AS name
						FROM	genre_settings designation,
							genre_settings name
						WHERE	designation.genre_id=? AND designation.setting_name=? AND
							name.genre_id=? AND name.setting_name=?',
						array($genreId, 'designation', $genreId, 'name')
					);
					while (!$result->EOF) {
						$row = $result->GetRowAssoc(false);
						$designation = $row['designation'];
						$name = $row['name'];
						// Build a potential filename (see SubmissionFile::generateFilename)
						$potentialFilename = $submissionFile->getSubmissionId() . '-' .
							$designation . '_' . $name . '-' .
							$submissionFile->getFileId() . '-' .
							$submissionFile->getRevision() . '-' .
							$submissionFile->getFileStage() . '-' .
							date('Ymd', strtotime($submissionFile->getDateUploaded())) .
							'.' . strtolower_codesafe($submissionFile->getExtension());
						if (file_exists($basePath . $potentialFilename)) {
							$discoveredFilename = $potentialFilename;
						}
						$result->MoveNext();
					}

					if ($discoveredFilename === null) {
						fatalError('Unable to find a match for "' . $basePath . $generatedFilename . "\".\n");
						return false;
					} elseif ($discoveredFilename != $generatedFilename) {
						if ($dryrun) {
							echo "Need to rename \"$discoveredFilename\" to \"$generatedFilename\".";
						} else {
							rename($basePath . $discoveredFilename, $basePath . $generatedFilename);
						}
					}
				}
			}
		}
		return true;
	}
}

?>
