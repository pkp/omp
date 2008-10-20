<?php

/**
 * @defgroup issue_form
 */
 
/**
 * @file classes/form/IssueForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueForm
 * @ingroup issue_form
 * @see Issue
 *
 * @brief Form to create or edit an issue
 */

// $Id$


import('form.Form');
import('issue.Issue'); // Bring in constants

class IssueForm extends Form {

	/**
	 * Constructor.
	 */
	function IssueForm($template) {
		parent::Form($template);
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Get a list of fields for which localization should be used.
	 * @return array
	 */
	function getLocaleFieldNames() {
		$issueDao =& DAORegistry::getDAO('IssueDAO');
		return $issueDao->getLocaleFieldNames();
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$press =& Request::getPress();

		// set up the accessibility options pulldown
		$templateMgr->assign('enableSubscriptions', $press->getSetting('enableSubscriptions'));
		$templateMgr->assign('enableDelayedOpenAccess', $press->getSetting('enableDelayedOpenAccess'));

		$accessOptions = array();
		$accessOptions[OPEN_ACCESS] = Locale::Translate('editor.issues.openAccess');
		$accessOptions[SUBSCRIPTION] = Locale::Translate('editor.issues.subscription');
		$templateMgr->assign('accessOptions', $accessOptions);

		$templateMgr->assign('enablePublicIssueId', $press->getSetting('enablePublicIssueId'));

		parent::display();
	}

	/**
	 * Validate the form
	 */
	function validate($issueId = 0) {
		if ($this->getData('showVolume')) {
			$this->addCheck(new FormValidatorCustom($this, 'volume', 'required', 'editor.issues.volumeRequired', create_function('$volume', 'return ($volume > 0);')));
		}

		if ($this->getData('showNumber')) {
			$this->addCheck(new FormValidatorCustom($this, 'number', 'required', 'editor.issues.numberRequired', create_function('$number', 'return ($number > 0);')));
		}

		if ($this->getData('showYear')) {
			$this->addCheck(new FormValidatorCustom($this, 'year', 'required', 'editor.issues.yearRequired', create_function('$year', 'return ($year > 0);')));
		}

		if ($this->getData('showTitle')) {
			$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'editor.issues.titleRequired'));
		}

		// check if public issue ID has already used
		$press =& Request::getPress();
		$issueDao =& DAORegistry::getDAO('IssueDAO');

		$publicIssueId = $this->getData('publicIssueId');
		if ($publicIssueId && $issueDao->publicIssueIdExists($publicIssueId, $issueId, $press->getPressId())) {
			$this->addError('publicIssueId', Locale::translate('editor.issues.issuePublicIdentificationExists'));
			$this->addErrorField('publicIssueId');
		}

		// check if date open access date is correct if subscription is selected and enabled
		// and delayed open access is not set
		$subscription = $press->getSetting('enableSubscriptions');
		$delayedOpenAccess = $press->getSetting('enableDelayedOpenAccess');
		if (!empty($issueId)) {
			$issue =& $issueDao->getIssueById($issueId);
			$issuePublished = $issue->getPublished();
		} else {
			$issuePublished = 0;
		}

		if (($subscription && !$delayedOpenAccess) || ($subscription && $delayedOpenAccess && $issuePublished)) {
			$month = $this->getData('Date_Month');
			$day = $this->getData('Date_Day');
			$year = $this->getData('Date_Year');
			if (!checkdate($month,$day,$year)) {
				$this->addError('openAccessDate', Locale::translate('editor.issues.invalidAccessDate'));
				$this->addErrorField('openAccessDate');
			}
		}

		import('file.PublicFileManager');
		$publicFileManager =& new PublicFileManager();

		if ($publicFileManager->uploadedFileExists('coverPage')) {
			$type = $publicFileManager->getUploadedFileType('coverPage');
			if (!$publicFileManager->getImageExtension($type)) {
				$this->addError('coverPage', Locale::translate('editor.issues.invalidCoverPageFormat'));
				$this->addErrorField('coverPage');
			}
		}

		if ($publicFileManager->uploadedFileExists('styleFile')) {
			$type = $publicFileManager->getUploadedFileType('styleFile');
			if ($type != 'text/plain' && $type != 'text/css') {
				$this->addError('styleFile', Locale::translate('editor.issues.invalidStyleFormat'));
			}
		}

		return parent::validate();
	}

	/**
	 * Initialize form data from current issue.
	 * returns issue id that it initialized the page with
	 */
	function initData($issueId = null) {
		$issueDao =& DAORegistry::getDAO('IssueDAO');

		// retrieve issue by id, if not specified, then select first unpublished issue
		if (isset($issueId)) {
			$issue =& $issueDao->getIssueById($issueId);
		}

		if (isset($issue)) {
			$openAccessDate = $issue->getOpenAccessDate();
			if (isset($openAccessDate)) $openAccessDate = getdate(strtotime($openAccessDate));

			$this->_data = array(
				'title' => $issue->getTitle(null), // Localized
				'volume' => $issue->getVolume(),
				'number' => $issue->getNumber(),
				'year' => $issue->getYear(),
				'datePublished' => $issue->getDatePublished(),
				'description' => $issue->getDescription(null), // Localized
				'publicIssueId' => $issue->getPublicIssueId(),
				'accessStatus' => $issue->getAccessStatus(),
				'Date_Month' => $openAccessDate['mon'],
				'Date_Day' => $openAccessDate['mday'],
				'Date_Year' => $openAccessDate['year'],
				'showVolume' => $issue->getShowVolume(),
				'showNumber' => $issue->getShowNumber(),
				'showYear' => $issue->getShowYear(),
				'showTitle' => $issue->getShowTitle(),
				'fileName' => $issue->getFileName(null), // Localized
				'originalFileName' => $issue->getOriginalFileName(null), // Localized
				'coverPageDescription' => $issue->getCoverPageDescription(null), // Localized
				'coverPageAltText' => $issue->getCoverPageAltText(null), // Localized
				'showCoverPage' => $issue->getShowCoverPage(null), // Localized
				'hideCoverPageArchives' => $issue->getHideCoverPageArchives(null), // Localized
				'hideCoverPageCover' => $issue->getHideCoverPageCover(null), // Localized
				'styleFileName' => $issue->getStyleFileName(),
				'originalStyleFileName' => $issue->getOriginalStyleFileName()
			);
			return $issue->getIssueId();

		} else {
			$press =& Request::getPress();
			$showVolume = $press->getSetting('publicationFormatVolume');
			$showNumber = $press->getSetting('publicationFormatNumber');
			$showYear = $press->getSetting('publicationFormatYear');
			$showTitle = $press->getSetting('publicationFormatTitle');

			$this->setData('showVolume', $showVolume);
			$this->setData('showNumber', $showNumber);
			$this->setData('showYear', $showYear);
			$this->setData('showTitle', $showTitle);

			// set up the default values for volume, number and year
			$issueDao =& DAORegistry::getDAO('IssueDAO');
			$issue = $issueDao->getLastCreatedIssue($press->getPressId());

			if (isset($issue)) {
				$volumePerYear = $press->getSetting('volumePerYear');
				$issuePerVolume = $press->getSetting('issuePerVolume');
				$number = $issue->getNumber();
				$volume = $issue->getVolume();
				$year = $issue->getYear();

				if ($showVolume && $showNumber && $showYear) {
					$number++;
					if ($issuePerVolume && $number > $issuePerVolume) {
						$number = 1;
						$volume++;
						if ($volumePerYear && $volume > $volumePerYear) {
							$volume = 1;
							$year++;
						}
					}
				} elseif ($showVolume && $showNumber) {
					$number++;
					if ($issuePerVolume && $number > $issuePerVolume) {
						$number = 1;
						$volume++;
					}
				} elseif ($showVolume && $showYear) {
					$number = 0;
					$volume++;
					if ($volumePerYear && $volume > $volumePerYear) {
						$volume = 1;
						$year++;
					}
				} elseif ($showYear) {
					$volume = $number = 0;
					$year++;
				} else {
					$year = $volume = $number = 0;
				}


			} else {
				$volume = $press->getSetting('initialVolume');
				$number = $press->getSetting('initialNumber');
				$year = $press->getSetting('initialYear');
			}


			if ($press->getSetting('enableSubscriptions')) {
				$accessStatus = SUBSCRIPTION;
			} else {
				$accessStatus = OPEN_ACCESS;
			}


			$this->_data = array(
				'showVolume' => $showVolume,
				'showNumber' => $showNumber,
				'showYear' => $showYear,
				'showTitle' => $showTitle,
				'volume' => $volume,
				'number' => $number,
				'year' => $year,
				'accessStatus' => $accessStatus
			);

		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
			'title',
			'volume',
			'number',
			'year',
			'description',
			'publicIssueId',
			'accessStatus',
			'Date_Month',
			'Date_Day',
			'Date_Year',
			'showVolume',
			'showNumber',
			'showYear',
			'showTitle',
			'fileName',
			'originalFileName',
			'coverPageDescription',
			'coverPageAltText',
			'showCoverPage',
			'hideCoverPageArchives',
			'hideCoverPageCover',
			'articles',
			'styleFileName',
			'originalStyleFileName'
		));

		$this->readUserDateVars(array('datePublished'));

		$this->addCheck(new FormValidatorCustom($this, 'showVolume', 'required', 'editor.issues.issueIdentificationRequired', create_function('$showVolume, $showNumber, $showYear, $showTitle', 'return $showVolume || $showNumber || $showYear || $showTitle ? true : false;'), array($this->getData('showNumber'), $this->getData('showYear'), $this->getData('showTitle'))));

	}

	/**
	 * Save issue settings.
	 */
	function execute($issueId = 0) {
		$press =& Request::getPress();
		$issueDao =& DAORegistry::getDAO('IssueDAO');

		if ($issueId) {
			$issue = $issueDao->getIssueById($issueId);
			$isNewIssue = false;
		} else {
			$issue =& new Issue();
			$isNewIssue = true;
		}
		$volume = $this->getData('volume');
		$number = $this->getData('number');
		$year = $this->getData('year');

		$showVolume = $this->getData('showVolume');
		$showNumber = $this->getData('showNumber');
		$showYear = $this->getData('showYear');
		$showTitle = $this->getData('showTitle');

		$issue->setPressId($press->getPressId());
		$issue->setTitle($this->getData('title'), null); // Localized
		$issue->setVolume(empty($volume) ? 0 : $volume);
		$issue->setNumber(empty($number) ? 0 : $number);
		$issue->setYear(empty($year) ? 0 : $year);
		if (!$isNewIssue) {
			$issue->setDatePublished($this->getData('datePublished'));
		}
		$issue->setDescription($this->getData('description'), null); // Localized
		$issue->setPublicIssueId($this->getData('publicIssueId'));
		$issue->setShowVolume(empty($showVolume) ? 0 : $showVolume);
		$issue->setShowNumber(empty($showNumber) ? 0 : $showNumber);
		$issue->setShowYear(empty($showYear) ? 0 : $showYear);
		$issue->setShowTitle(empty($showTitle) ? 0 : $showTitle);
		$issue->setCoverPageDescription($this->getData('coverPageDescription'), null); // Localized
		$issue->setCoverPageAltText($this->getData('coverPageAltText'), null); // Localized
		$showCoverPage = array_map(create_function('$arrayElement', 'return (int)$arrayElement;'), (array) $this->getData('showCoverPage'));
		foreach (array_keys($this->getData('coverPageDescription')) as $locale) {
			if (!array_key_exists($locale, $showCoverPage)) {
				$showCoverPage[$locale] = 0;
			}
		}
		$issue->setShowCoverPage($showCoverPage, null); // Localized

		$hideCoverPageArchives = array_map(create_function('$arrayElement', 'return (int)$arrayElement;'), (array) $this->getData('hideCoverPageArchives'));
		foreach (array_keys($this->getData('coverPageDescription')) as $locale) {
			if (!array_key_exists($locale, $hideCoverPageArchives)) {
				$hideCoverPageArchives[$locale] = 0;
			}
		}
		$issue->setHideCoverPageArchives($hideCoverPageArchives, null); // Localized

		$hideCoverPageCover = array_map(create_function('$arrayElement', 'return (int)$arrayElement;'), (array) $this->getData('hideCoverPageCover'));
		foreach (array_keys($this->getData('coverPageDescription')) as $locale) {
			if (!array_key_exists($locale, $hideCoverPageCover)) {
				$hideCoverPageCover[$locale] = 0;
			}
		}
		$issue->setHideCoverPageCover($hideCoverPageCover, null); // Localized

		$month = $this->getData('Date_Month');
		$day = $this->getData('Date_Day');
		$year = $this->getData('Date_Year');

		if ($this->getData('accessStatus')) {
			$issue->setAccessStatus($this->getData('accessStatus'));
			$issue->setOpenAccessDate(date('Y-m-d H:i:s',mktime(0,0,0,$month,$day,$year)));
		} else {
			$issue->setAccessStatus(1);
			$issue->setOpenAccessDate(Core::getCurrentDate());
		}

		// if issueId is supplied, then update issue otherwise insert a new one
		if ($issueId) {
			$issue->setIssueId($issueId);
			$issueDao->updateIssue($issue);
		} else {
			$issue->setPublished(0);
			$issue->setCurrent(0);

			$issueId = $issueDao->insertIssue($issue);
			$issue->setIssueId($issueId);
		}

		import('file.PublicFileManager');
		$publicFileManager =& new PublicFileManager();
		if ($publicFileManager->uploadedFileExists('coverPage')) {
			$press = Request::getPress();
			$originalFileName = $publicFileManager->getUploadedFileName('coverPage');
			$newFileName = 'cover_issue_' . $issueId . '_' . $this->getFormLocale() . '.' . $publicFileManager->getExtension($originalFileName);
			$publicFileManager->uploadPressFile($press->getPressId(), 'coverPage', $newFileName);
			$issue->setOriginalFileName($publicFileManager->truncateFileName($originalFileName, 127), $this->getFormLocale());
			$issue->setFileName($newFileName, $this->getFormLocale());

			// Store the image dimensions.
			list($width, $height) = getimagesize($publicFileManager->getPressFilesPath($press->getPressId()) . '/' . $newFileName);
			$issue->setWidth($width, $this->getFormLocale());
			$issue->setHeight($height, $this->getFormLocale());

			$issueDao->updateIssue($issue);
		}

		if ($publicFileManager->uploadedFileExists('styleFile')) {
			$press = Request::getPress();
			$originalFileName = $publicFileManager->getUploadedFileName('styleFile');
			$newFileName = 'style_' . $issueId . '.css';
			$publicFileManager->uploadPressFile($press->getPressId(), 'styleFile', $newFileName);
			$issue->setStyleFileName($newFileName);
			$issue->setOriginalStyleFileName($publicFileManager->truncateFileName($originalFileName, 127));
			$issueDao->updateIssue($issue);
		}

		return $issueId;
	}
}

?>
