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

class ContributorInsert
{
	var $template;
	var $form;
	var $options;
	var $monograph;
	var $formData;

	function ContributorInsert(&$work, &$form, $options = 0) {
		$this->template = 'inserts/contributors/ContributorInsert.tpl';
	//	$form->addCheck(new FormValidatorCustom($this, 'authors', 'required', 'author.submit.form.authorRequired', create_function('$authors', 'return count($authors) > 0;')));
	//	$form->addCheck(new FormValidatorArray($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', array('firstName', 'lastName', 'email')));
		$this->form =& $form;
		$this->formData = array();
		$this->monograph =& $work;
		$this->options = $options;
	}
	function listUserVars() {
		return array('newContributor','contributors','primaryContact');
	}
	function &initData(&$form) {
		$contributors = array();
		if (isset($this->monograph)) {

			$authors =& $this->monograph->getAuthors();
			$i = 0;
			$gnash = array();
			$primaryContact = 0;
			foreach ($authors as $author) {
				$gnash[$author->getId()] = $i;

				$authorArray = array(
							'firstName' => $author->getFirstName(),
							'middleName' => $author->getMiddleName(),
							'lastName' => $author->getLastName(),
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

//			$form->setData('contributors', $contributors);
//			$form->setData('newContributor', null);

		}
		$returner = array('contributors' => $contributors, 'newContributor' => null, 'primaryContact' => $primaryContact, 'lookup'=>$gnash);
		return $returner;
	}
	function display(&$form) {
		$templateMgr =& TemplateManager::getManager();
		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();
		$templateMgr->assign('countries', $countries);

		$templateMgr->assign('monographType', $this->monograph->getWorkType());

		$templateMgr->assign('contributors', $form->getData('contributors'));
	}
	function getLocaleFieldNames() {
		$fields = array();
		return $fields;
	}
	function execute(&$form, &$monograph) {

		$authors = $form->getData('contributors');

		$monograph->resetAuthors();
		if(is_array($authors))
		foreach ($authors as $formAuthor) {
			if ($formAuthor['deleted']) continue;
			$author =& new Author();
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
			$newAuthor = $submitForm->getData('newContributor');

			$formError = false;

			foreach (array('firstName','lastName','email') as $field) {
				if (isset($newAuthor[$field]) && $newAuthor[$field] == '') {
					$formError = true;
					break;
				}
			}
			$templateMgr =& TemplateManager::getManager();

			if (!$formError){
				$authors = $submitForm->getData('contributors');
				$authors = !isset($authors) ? array() : $authors;

				array_push($authors, $newAuthor);
				$submitForm->setData('contributors', $authors);
				$submitForm->setData('newContributor', null);
			} else {
				$templateMgr->assign('isError', true);
				$templateMgr->assign('errors', array('author.submit.form.authorRequiredFields'));
				$submitForm->setData('newContributor', $newAuthor);
			}
		} else if (($updateId = Request::getUserVar('updateContributorInfo'))) {
			$eventProcessed = true;
			$authors = $submitForm->getData('authors');
			list($updateId) = array_keys($updateId);
			$updateId = (int) $updateId;
			if (isset($authors[$updateId]['isVolumeEditor']))
				$authors[$updateId]['isVolumeEditor'] = 1;
			$submitForm->setData('authors',$authors);
		} else if ($deleteAuthor = Request::getUserVar('deleteAuthor')) {
			// Delete an author
			$eventProcessed = true;
			list($deleteAuthor) = array_keys($deleteAuthor);
			$deleteAuthor = (int) $deleteAuthor;
			$authors = $submitForm->getData('authors');
			if (isset($authors[$deleteAuthor])) {
				$authors[$deleteAuthor]['deleted']=1;
			}
			$submitForm->setData('authors', $authors);
		} else if (Request::getUserVar('moveAuthor')) {
			// Move an author up/down
			$eventProcessed = true;
			$moveAuthorDir = Request::getUserVar('moveAuthorDir');
			$moveAuthorDir = $moveAuthorDir == 'u' ? 'u' : 'd';
			$moveAuthorIndex = (int) Request::getUserVar('moveAuthorIndex');
			$authors = $submitForm->getData('authors');
			if (!(($moveAuthorDir == 'u' && $moveAuthorIndex <= 0) || ($moveAuthorDir == 'd' && $moveAuthorIndex >= count($authors) - 1))) {
				$tmpAuthor = $authors[$moveAuthorIndex];
				$primaryContact = $submitForm->getData('primaryContact');
				if ($moveAuthorDir == 'u') {
					$authors[$moveAuthorIndex] = $authors[$moveAuthorIndex - 1];
					$authors[$moveAuthorIndex - 1] = $tmpAuthor;
					if ($primaryContact == $moveAuthorIndex) {
						$submitForm->setData('primaryContact', $moveAuthorIndex - 1);
					} else if ($primaryContact == ($moveAuthorIndex - 1)) {
						$submitForm->setData('primaryContact', $moveAuthorIndex);
					}
				} else {
					$authors[$moveAuthorIndex] = $authors[$moveAuthorIndex + 1];
					$authors[$moveAuthorIndex + 1] = $tmpAuthor;
					if ($primaryContact == $moveAuthorIndex) {
						$submitForm->setData('primaryContact', $moveAuthorIndex + 1);
					} else if ($primaryContact == ($moveAuthorIndex + 1)) {
						$submitForm->setData('primaryContact', $moveAuthorIndex);
					}
				}
			}
			$submitForm->setData('authors', $authors);
		}
		return $eventProcessed;
	}
}

?>
