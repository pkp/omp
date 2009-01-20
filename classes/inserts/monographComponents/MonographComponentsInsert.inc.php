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
 * @brief Monograph component (intro,chapters,etc) creator form insert.
 */

// $Id$

define('CREATE',0x1);
define('SUMMARY_ONLY',0x2);
define('AUTHORS_ONLY',0x4);
define('UPLOADS',0x8);

class MonographComponentsInsert
{
	var $template;
	var $form;
	var $options;
	var $monograph;

	function MonographComponentsInsert($form, &$monograph, $options = 0) {
		$this->template = 'inserts/monographComponents/monographComponentsInsert.tpl';
	//	$form->addCheck(new FormValidatorCustom($this, 'authors', 'required', 'author.submit.form.authorRequired', create_function('$authors', 'return count($authors) > 0;')));
	//	$form->addCheck(new FormValidatorArray($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', array('firstName', 'lastName', 'email')));
		$this->form = $form;
		$this->monograph = $monograph;
		$this->options = $options;
	}
	function listUserVars() {
		return array('newComponent','components','newAuthor','authors','primaryContact');
	}
	function initData() {

		if (isset($this->monograph)) {

			$authors =& $this->monograph->getAuthors();
			$formAuthors = array();
			for ($i=0, $count=count($authors); $i < $count; $i++) {
				$gnash[$authors[$i]->getAuthorId()] = $i;
				array_push(
					$formAuthors,
					array(
						'authorId' => $i,
						'firstName' => $authors[$i]->getFirstName(),
						'lastName' => $authors[$i]->getLastName(),
						'email' => $authors[$i]->getEmail()
					)
				);
				if ($authors[$i]->getPrimaryContact()) {
					$this->form->setData('primaryContact', $i);
				}
			}
			$this->form->_data['authors'] = $formAuthors;
			$components =& $this->monograph->getMonographComponents();
			$formComponents = array();

			import('monograph.Author');
			for ($i=0, $count=count($components); $i < $count; $i++) {
				$cas = array();
				foreach ($components[$i]->getMonographComponentAuthors() as $ca) {
					array_push($cas, array(
								'authorId' => $gnash[$ca->getAuthorId()],
								'email' => $ca->getEmail(),
								'firstName' => $ca->getFirstName(),
								'lastName' => $ca->getLastName()
							)
						);
				}
				array_push(
					$formComponents,
					array(
						'title' => $components[$i]->getMonographComponentTitle(),
						'authors' => $cas
					)
				);
			}


			$this->form->_data['components'] = $formComponents;
		}
	}
	function display() {
		
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('authors', $this->form->_data['authors']);
		$templateMgr->assign('components', $this->monograph->_data['components']);

		if ($this->options == 0) {
			$templateMgr->assign('componentCreation',1);
		} else {
			if ($this->options & CREATE) {
				$templateMgr->assign('componentCreation',1);
			}
			if ($this->options & AUTHORS_ONLY) {
				$templateMgr->assign('authors_only','1');
			}
			if ($this->options & SUMMARY_ONLY) {
				//get components/authors and template->assign(summaryonly)
			}
			if($this->options & UPLOADS == UPLOADS) {
				$templateMgr->assign('uploadComponents',1);
			}
		}

	}
	function getLocaleFieldNames() {
		$fields = array();
		return $fields;
	}
	function execute() {
		$components = $this->form->getData('components');
		$authors = $this->form->getData('authors');

		if($this->options & AUTHORS_ONLY) {
			$this->monograph->resetAuthors();
			foreach ($authors as $monographAuthor) {
				
				if ($monographAuthor['deleted']) continue;

				$author =& new Author();
				$author->setMonographId($this->monograph->getMonographId());
				$author->setAuthorId($monographAuthor['authorId']);
				$author->setFirstName($monographAuthor['firstName']);
				$author->setLastName($monographAuthor['lastName']);
				$author->setEmail($monographAuthor['email']);
				$author->setPrimaryContact($this->form->getData('primaryContact') == $monographAuthor['authorId'] ? PRIMARY_CONTACT : 0);
				
				$this->monograph->addAuthor($author);
			}

		} else { 

			$this->monograph->resetAuthors();
			foreach ($authors as $monographAuthor) {
				
				if ($monographAuthor['deleted']) continue;

				$author =& new Author();
				$author->setFirstName($monographAuthor['firstName']);
				$author->setAuthorId($monographAuthor['authorId']);
				$author->setLastName($monographAuthor['lastName']);
				$author->setEmail($monographAuthor['email']);
				$author->setPrimaryContact($this->form->getData('primaryContact') == $monographAuthor['authorId'] ? PRIMARY_CONTACT : 0);
				$author->setMonographId($this->monograph->getMonographId());
				$this->monograph->addAuthor($author);
			}


			import('monograph.MonographComponent');
			$componentsList = array();
			$j = 1;
			foreach ($components as $componentInfo) {
				$component =& new MonographComponent;
				$component->setMonographComponentTitle($componentInfo['title'], null);
				$component->setMonographId($this->monograph->getMonographId());
				$component->setSequence($j);
				if(count($component->getMonographComponentAuthors()))
					$component->setPrimaryContact($componentInfo['primaryContact']);
				
				if (isset($componentInfo['authors'])) {
					$i = 1;
					foreach ($componentInfo['authors'] as $componentAuthor) {
						// Create a new author
						$author =& new Author();
						$authorId = (int) $componentAuthor['authorId'];
						$author->setAuthorId($authorId);
						$author->setSequence($i);
						$component->addMonographComponentAuthor($author);
						$i++;
					}
				}
				array_push($componentsList, $component);
				$j++;
			}
			$this->monograph->setMonographComponents($componentsList);
		}

	}
	function processEvents() {
		$eventProcessed = false;
		$submitForm = $this->form;
		if (Request::getUserVar('addAuthor')) {
			// Add a sponsor
			$eventProcessed = true;
			$newAuthor =& $submitForm->getData('newAuthor');
			$skipNewEntry = false;

			foreach (array('firstName','lastName','email') as $field) {
				if (isset($newAuthor[$field]) && $newAuthor[$field] == '') {
					$skipNewEntry = true;
					break;
				}
			}
			$templateMgr =& TemplateManager::getManager();

			if (!$skipNewEntry){
				$authors = $submitForm->getData('authors');
				$authors = !isset($authors) ? array() : $authors;
				if (isset($newAuthor['isVolumeEditor'])) {
					$newAuthor['isVolumeEditor'] = 1;
					$templateMgr->assign('primaryContact',$newAuthor['authorId']);
				}
				array_push($authors, $newAuthor);
				$submitForm->setData('authors', $authors);
				$submitForm->setData('newAuthor',null);
			} else {
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('cannotAddAuthor',true);
				$templateMgr->assign_by_ref('newAuthor',$newAuthor);
			}
		} else if (Request::getUserVar('addComponent') && 1) {// && work is an edited volume
			$eventProcessed = true;
			$newComponent = $submitForm->getData('newComponent');
			$components = $submitForm->getData('components');
			$components = !isset($components) ? array() : $components;
			$authorlist = array();
			if (isset($newComponent['authors'])) {
				for ($i = 0,$count=count($newComponent['authors']);$i < $count;$i++) {
					$authorId = $newComponent['authors'][$i];
					$authorlist[$i]['authorId'] = $authorId;
				}
				$newComponent['authors'] =& $authorlist;
				$newComponent['primaryContact'] = $authorId;
			}
			array_push($components, $newComponent);
			$submitForm->setData('components', $components);
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
		} else if (Request::getUserVar('moveComponentAuthor')) {
			$eventProcessed = true;
			$moveAuthorDir = Request::getUserVar('moveAuthorDir');
			$moveAuthorDir = $moveAuthorDir == 'u' ? 'u' : 'd';
			$moveAuthorIndex = (int) Request::getUserVar('moveAuthorIndex');
			$moveComponentIndex = (int) Request::getUserVar('moveAuthorComponentIndex');
			$authors = $submitForm->getData('authors');
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
		} else if (Request::getUserVar('moveComponent')) {
			$eventProcessed = true;
			$moveComponentDir = Request::getUserVar('moveComponentDir');
			$moveComponentDir = $moveComponentDir == 'u' ? 'u' : 'd';
			$moveComponentIndex = (int) Request::getUserVar('moveComponentIndex');
			$components = $submitForm->getData('components');
			if (!(($moveComponentDir == 'u' && $moveComponentIndex <= 1) || ($moveComponentDir == 'd' && $moveComponentIndex >= count($components)))) {
				$tmpComponent = $components[$moveComponentIndex];
				if ($moveComponentDir == 'u') {
					$components[$moveComponentIndex] = $components[$moveComponentIndex - 1];
					$components[$moveComponentIndex - 1] = $tmpComponent;
				} else {
					$components[$moveComponentIndex] = $components[$moveComponentIndex + 1];
					$components[$moveComponentIndex + 1] = $tmpComponent;
				}
			}
			$submitForm->setData('components', $components);
		}
		return $eventProcessed;
	}
}

?>