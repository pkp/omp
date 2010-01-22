<?php

/**
 * @file inserts/monographComponents/MonographComponentsInsert.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
			'contributors', 
			'primaryContact',
			'deletedContributors'
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
					'pivotId' => $i,
					'contributorId' => $author->getId(),
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
				'nextPivotId' => $i, 
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

		$newContributor = $form->getData('newContributor');
		$templateMgr->assign('nextPivotId', $newContributor['pivotId']);
	}
	function execute(&$form, &$monograph) {
		import('monograph.Author');
		$contributors = $form->getData('contributors');

		$i = 0;
		foreach ($contributors as $contributor) {
			if (!empty($contributor['contributorId'])) {
				$contributorId = $contributor['contributorId'];
				// Update an existing contributor
				$author =& $monograph->getAuthor($contributorId);
				$isExistingContributor = true;
			} else {
				// Create a new contributor
				$author = new Author();
				$isExistingContributor = false;
			}

			$author->setData('pivotId', $contributor['pivotId']);
			$author->setMonographId($monograph->getMonographId());
			$author->setFirstName($contributor['firstName']);
			$author->setMiddleName($contributor['middleName']);
			$author->setLastName($contributor['lastName']);
			$author->setAffiliation($contributor['affiliation']);
			$author->setCountry($contributor['country']);
			$author->setEmail($contributor['email']);
			$author->setUrl($contributor['url']);
			$author->setBiography($contributor['biography'], null);
			$author->setPrimaryContact($form->getData('primaryContact') == $contributor['pivotId'] ? PRIMARY_CONTACT : 0);
			$author->setSequence($i+1);

			if (!isset($contributor['contributionType'])) $author->setContributionType(CONTRIBUTION_TYPE_AUTHOR);
			else $author->setContributionType($contributor['contributionType']);

			if (!$isExistingContributor) {
				$monograph->addAuthor($author);
			}
			unset($author);
			$i++;
		}

		// Remove deleted contributors
		$deletedContributors = explode(':', $form->getData('deletedContributors'));
		for ($i=0, $count=count($deletedContributors); $i < $count; $i++) {
			$monograph->removeAuthor($deletedContributors[$i]);
		}
	}
	function processEvents(&$form) {
		$eventProcessed = false;

		if (Request::getUserVar('addContributor')) {

			$eventProcessed = true;
			$newContributor = $form->getData('newContributor');

			$formError = false;
			$formErrors = array();
			foreach (array('firstName','lastName','email') as $field) {
				if (isset($newContributor[$field]) && $newContributor[$field] == '') {
					$formError = true;
					array_push($formErrors, 'inserts.contributors.newContributor.formError.'.$field);
				}
			}

			if (!$formError){
				$contributors = $form->getData('contributors');
				$contributors = !isset($contributors) ? array() : $contributors;

				if ($this->monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME && $newContributor['contributionType'] == CONTRIBUTION_TYPE_VOLUME_EDITOR) {
					$newList = array();
					$tmpContributors = $contributors;
					foreach ($contributors as $contributor) {
						if (!empty($contributor['contributionType']) && $contributor['contributionType'] == CONTRIBUTION_TYPE_VOLUME_EDITOR) {
							$contributor[$contributor['pivotId']]['isVolumeEditor'] = (int) true;
							array_push($newList, array_shift($tmpContributors));
						} else {
							break;
						}
					}

					if (count($newList) < 1) {
						$form->setData('primaryContact', $newContributor['pivotId']);
					}

					array_push($newList, $newContributor);
					$contributors = array_merge($newList, $tmpContributors);
				} else {

					if (count($contributors) < 1 && $this->monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME) {
						$form->setData('primaryContact', $newContributor['pivotId']);
					}
					array_push($contributors, $newContributor);
				}

				$form->setData('nextPivotId', $newContributor['pivotId'] + 1);
				$form->setData('contributors', $contributors);
				$form->setData('newContributor', null);
			} else {
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('inserts_ContributorInsert_isError', true);
				$templateMgr->assign('inserts_ContributorInsert_errors', $formErrors);
				$form->setData('newContributor', $newContributor);
			}
		} else if (($updateId = Request::getUserVar('updateContributorInfo'))) {
			$eventProcessed = true;
			$contributors = $form->getData('contributors');
			list($updateId) = array_keys($updateId);
			$updateId = (int) $updateId;

			// volume editor list maintenance
			if ($this->monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME) {
				$primaryContact = $form->getData('primaryContact');
				$primaryContactFound = false;
				$newList = array();
				$tmpContributors = $contributors;
				foreach ($contributors as $contributor) {
					if (isset($contributor['contributionType']) && $contributor['contributionType'] == CONTRIBUTION_TYPE_VOLUME_EDITOR) {
						$contributors[$contributor['pivotId']]['isVolumeEditor'] = (int) true;
						$newList[$contributor['pivotId']] = $contributor;
						if ($contributor['pivotId'] == $primaryContact) {
							$primaryContactFound = true;
						}
						unset($tmpContributors[$contributor['pivotId']]);
					}
				}

				$contributors = array_merge($newList, $tmpContributors);

				if (!$primaryContactFound && isset($contributors[0]) && $contributors[0]['contributionType'] == CONTRIBUTION_TYPE_VOLUME_EDITOR) {
					$form->setData('primaryContact', $contributors[0]['pivotId']);
				}
			}
			$form->setData('contributors',$contributors);
		} else if ($deleteContributor = Request::getUserVar('deleteContributor')) {
			// Delete a contributor
			$eventProcessed = true;
			list($deleteContributor) = array_keys($deleteContributor);
			$deleteContributor = (int) $deleteContributor;
			$contributors = $form->getData('contributors');
			$primaryContact = $form->getData('primaryContact');

			if (isset($contributors[$deleteContributor])) {
				if (!empty($contributors[$deleteContributor]['contributorId'])) {
					$deletedContributors = explode(':', $form->getData('deletedContributors'));
					array_push($deletedContributors, $contributors[$deleteContributor]['contributorId']);
					$form->setData('deletedContributors', join(':', $deletedContributors));
				}
				Registry::set('inserts_ContributorInsert_removeAuthor', $deleteContributor);
				unset($contributors[$deleteContributor]);
			}

			$keyMap = array_keys($contributors);
			if ($primaryContact == $deleteContributor && isset($contributors[$keyMap[0]])) {
				$form->setData('primaryContact', $contributors[$keyMap[0]]['pivotId']);
			}

			$form->setData('contributors', $contributors);
		} else if (Request::getUserVar('moveContributor')) {
			// Move a contributor up/down
			$eventProcessed = true;
			$moveContributorDir = Request::getUserVar('moveContributorDir');
			$moveContributorDir = $moveContributorDir == 'u' ? 'u' : 'd';
			$moveContributorIndex = (int) Request::getUserVar('moveContributorIndex');
			$contributors = $form->getData('contributors');
			$newContributorList = array();

			$keyMap = array_keys($contributors);

			if (!(($moveContributorDir == 'u' && $moveContributorIndex <= 0) || ($moveContributorDir == 'd' && $moveContributorIndex >= count($contributors) - 1))) {
				$tmpContributor = $contributors[$keyMap[$moveContributorIndex]];
				$primaryContact = $form->getData('primaryContact');
				if ($moveContributorDir == 'u') {
					$tmpUpperValue = $keyMap[$moveContributorIndex - 1];
					$keyMap[$moveContributorIndex - 1] = $keyMap[$moveContributorIndex];
					$keyMap[$moveContributorIndex] = $tmpUpperValue;
					foreach ($keyMap as $key => $value) {
						$newContributorList[$value] = $contributors[$keyMap[$key]];
					}
				} else {
					if ((isset($contributors[$keyMap[$moveContributorIndex + 1]]['contributionType']) &&
						$contributors[$keyMap[$moveContributorIndex + 1]]['contributionType'] == CONTRIBUTION_TYPE_VOLUME_EDITOR) ||
							$this->monograph->getWorkType() != WORK_TYPE_EDITED_VOLUME) {
						$tmpLowerValue = $keyMap[$moveContributorIndex + 1];
						$keyMap[$moveContributorIndex + 1] = $keyMap[$moveContributorIndex];
						$keyMap[$moveContributorIndex] = $tmpLowerValue;
						foreach ($keyMap as $key => $value) {
							$newContributorList[$value] = $contributors[$keyMap[$key]];
						}
					}
				}
				$form->setData('contributors', $newContributorList);
			} else {
				$form->setData('contributors', $contributors);
			}
			
		}

		if ($eventProcessed) {
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('scrollToAuthor', true);
		}

		return $eventProcessed;
	}
}

?>