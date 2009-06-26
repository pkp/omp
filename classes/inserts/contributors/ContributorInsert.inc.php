<?php

/**
 * @file inserts/monographComponents/MonographComponentsInsert.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographComponentsInsert
 * @ingroup inserts
 * 
 * @brief Form insert for contributors.
 */

// $Id$

import('inserts.Insert');

class ContributorInsert extends Insert
{
	var $options;
	var $monograph;

	function ContributorInsert(&$monograph, $options = 0) {
		parent::Insert($options);
		$this->monograph =& $monograph;
	}
	function &listUserVars() {
		$returner = array(
			'newContributor', 
			'newContributorId', 
			'contributors', 
			'primaryContact'
		);
		return $returner;
	}
	function &initData() {
		$contributors = array();
		$returner = array();

		if (isset($this->monograph)) {

			$authors =& $this->monograph->getAuthors();
			$primaryContact = 0;
			$idMap = array();
			$i = 0;
			foreach ($authors as $author) {
				$idMap[$author->getId()] = $i;

				$authorArray = array(
							'firstName' => $author->getFirstName(),
							'middleName' => $author->getMiddleName(),
							'lastName' => $author->getLastName(), 
							'fullName' => $author->getFullName(), 
							'affiliation' => $author->getAffiliation(),
							'email' => $author->getEmail(),
							'url' => $author->getUrl(),
							'country' => $author->getCountry(),
							'authorId' => $i,
							'biography' => $author->getBiography(null),
							'contributionType' => $author->getContributionType()
						);
				if ($author->getPrimaryContact()) {
					$primaryContact = $i;
				}
				array_push($contributors, $authorArray);
				$i++;
			}

			$returner = array(
				'newContributor' => null, 
				'newContributorId' => $i, 
				'contributors' => $contributors, 
				'primaryContact'=> $primaryContact, 
				'workType' => $this->monograph->getWorkType(), 
				'lookup' => $idMap
			);
		}
		return $returner;
	}
	function display(&$form) {
		$templateMgr =& TemplateManager::getManager();

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();

		$templateMgr->assign('countries', $countries);
		$templateMgr->assign('workType', $this->monograph->getWorkType());
		$templateMgr->assign('contributors', $form->getData('contributors'));
		$templateMgr->assign('primaryContact', $form->getData('primaryContact'));
		$templateMgr->assign('newContributorId', $form->getData('newContributorId'));
	}
	function execute(&$form, &$monograph) {

		$contributors = $form->getData('contributors');

		$monograph->resetAuthors();
		if(is_array($contributors))
		foreach ($contributors as $formAuthor) {
			if ($formAuthor['deleted']) continue;
			$author = new Author();
			$author->setMonographId($monograph->getMonographId());
			$author->setId($formAuthor['authorId']);
			$author->setFirstName($formAuthor['firstName']);
			$author->setMiddleName($formAuthor['middleName']);
			$author->setLastName($formAuthor['lastName']);
			$author->setAffiliation($formAuthor['affiliation']);
			$author->setCountry($formAuthor['country']);
			$author->setUrl($formAuthor['url']);
			$author->setEmail($formAuthor['email']);
			$author->setBiography($formAuthor['biography'], null);
			$author->setPrimaryContact($form->getData('primaryContact') == $formAuthor['authorId'] ? PRIMARY_CONTACT : 0);

			if (!isset($formAuthor['contributionType'])) $author->setContributionType(AUTHOR);
			else $author->setContributionType($formAuthor['contributionType']);

			$monograph->addAuthor($author);
		}

	}
	function processEvents(&$form) {
		$eventProcessed = false;
		$submitForm =& $form;

		if (Request::getUserVar('addContributor')) {

			$eventProcessed = true;
			$newContributor = $submitForm->getData('newContributor');

			$formError = false;
			$formErrors = array();
			foreach (array('firstName','lastName','email') as $field) {
				if (isset($newContributor[$field]) && $newContributor[$field] == '') {
					$formError = true;
					array_push($formErrors, 'inserts.contributors.newContributor.formError.'.$field);
				}
			}
			$templateMgr =& TemplateManager::getManager();

			if (!$formError){
				$contributors = $submitForm->getData('contributors');
				$contributors = !isset($contributors) ? array() : $contributors;
				$newContributor['authorId'] = $submitForm->getData('newContributorId');

				if ($this->monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME && $newContributor['contributionType'] == VOLUME_EDITOR) {
					$newList = array();
					$tmpContributors = $contributors;
					foreach ($contributors as $contributor) {
						if (isset($contributor['contributionType']) && $contributor['contributionType'] == VOLUME_EDITOR) {
							$contributor[$contributor['authorId']]['isVolumeEditor'] = (int) true;
							array_push($newList, array_shift($tmpContributors));
						} else {
							break;
						}
					}

					if (count($newList) < 1) {
						$submitForm->setData('primaryContact', $newContributor['authorId']);
					}

					array_push($newList, $newContributor);
					$contributors = array_merge($newList, $tmpContributors);
				} else {

					if (count($contributors) < 1 && $this->monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME) {
						$submitForm->setData('primaryContact', $newContributor['authorId']);
					}
					array_push($contributors, $newContributor);
				}

				$submitForm->setData('newContributorId', $newContributor['authorId'] + 1);
				$submitForm->setData('contributors', $contributors);
				$submitForm->setData('newContributor', null);
			} else {
				$templateMgr->assign('inserts_ContributorInsert_isError', true);
				$templateMgr->assign('inserts_ContributorInsert_errors', $formErrors);
				$submitForm->setData('newContributor', $newContributor);
			}
		} else if (($updateId = Request::getUserVar('updateContributorInfo'))) {
			$eventProcessed = true;
			$contributors = $submitForm->getData('contributors');
			list($updateId) = array_keys($updateId);
			$updateId = (int) $updateId;

			// volume editor list maintenance
			if ($this->monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME) {
				$primaryContact = $submitForm->getData('primaryContact');
				$primaryContactFound = false;
				$newList = array();
				$tmpContributors = $contributors;
				foreach ($contributors as $contributor) {
					if (isset($contributor['contributionType']) && $contributor['contributionType'] == VOLUME_EDITOR) {
						$contributors[$contributor['authorId']]['isVolumeEditor'] = (int) true;
						$newList[$contributor['authorId']] = $contributor;
						if ($contributor['authorId'] == $primaryContact) {
							$primaryContactFound = true;
						}
						unset($tmpContributors[$contributor['authorId']]);
					}
				}

				$contributors = array_merge($newList, $tmpContributors);

				if (!$primaryContactFound && isset($contributors[0]) && $contributors[0]['contributionType'] == VOLUME_EDITOR) {
					$submitForm->setData('primaryContact', $contributors[0]['authorId']);
				}

			}
			$submitForm->setData('contributors',$contributors);
		} else if ($deleteContributor = Request::getUserVar('deleteContributor')) {
			// Delete a contributor
			$eventProcessed = true;
			list($deleteContributor) = array_keys($deleteContributor);
			$deleteContributor = (int) $deleteContributor;
			$contributors = $submitForm->getData('contributors');
			$primaryContact = $submitForm->getData('primaryContact');

			if (isset($contributors[$deleteContributor])) {
				Registry::set('inserts_ContributorInsert_removeAuthor', $deleteContributor);
				unset($contributors[$deleteContributor]);
			}

			$keyMap = array_keys($contributors);
			if ($primaryContact == $deleteContributor && isset($contributors[$keyMap[0]])) {
				$submitForm->setData('primaryContact', $contributors[$keyMap[0]]['authorId']);
			}

			$submitForm->setData('contributors', $contributors);
		} else if (Request::getUserVar('moveContributor')) {
			// Move a contributor up/down
			$eventProcessed = true;
			$moveContributorDir = Request::getUserVar('moveContributorDir');
			$moveContributorDir = $moveContributorDir == 'u' ? 'u' : 'd';
			$moveContributorIndex = (int) Request::getUserVar('moveContributorIndex');
			$contributors = $submitForm->getData('contributors');

			$keyMap = array_keys($contributors);

			if (!(($moveContributorDir == 'u' && $moveContributorIndex <= 0) || ($moveContributorDir == 'd' && $moveContributorIndex >= count($contributors) - 1))) {
				$tmpContributor = $contributors[$keyMap[$moveContributorIndex]];
				$primaryContact = $submitForm->getData('primaryContact');
				if ($moveContributorDir == 'u') {
					$contributors[$keyMap[$moveContributorIndex]] = $contributors[$keyMap[$moveContributorIndex - 1]];
					$contributors[$keyMap[$moveContributorIndex - 1]] = $tmpContributor;
				} else {
					if ((isset($contributors[$keyMap[$moveContributorIndex + 1]]['contributionType']) &&
						$contributors[$keyMap[$moveContributorIndex + 1]]['contributionType'] == VOLUME_EDITOR) ||
							$this->monograph->getWorkType() != WORK_TYPE_EDITED_VOLUME) {

						$contributors[$keyMap[$moveContributorIndex]] = $contributors[$keyMap[$moveContributorIndex + 1]];
						$contributors[$keyMap[$moveContributorIndex + 1]] = $tmpContributor;
					}
				}
			}
			$submitForm->setData('contributors', $contributors);
		}

		if ($eventProcessed) {
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('scrollToAuthor', true);
		}

		return $eventProcessed;
	}
}

?>