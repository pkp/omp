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

import('inserts.contributors.ContributorInsert');

class MonographComponentsInsert
{
	var $template;
	var $form;
	var $options;
	var $monograph;
	var $contributorInsert;
	function MonographComponentsInsert($monograph, $form, $options = 0) {
		$this->template = 'inserts/monographComponents/monographComponentsInsert.tpl';
	//	$form->addCheck(new FormValidatorCustom($this, 'authors', 'required', 'author.submit.form.authorRequired', create_function('$authors', 'return count($authors) > 0;')));
	//	$form->addCheck(new FormValidatorArray($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', array('firstName', 'lastName', 'email')));
		$this->form = $form;
		$this->monograph = $monograph;
		$this->options = $options;
		$this->contributorInsert =& new ContributorInsert($monograph, $form);
	}
	function listUserVars() {
		$vars = array('newComponent', 'components', 'primaryContact');
		return array_merge($vars, $this->contributorInsert->listUserVars());
	}
	function initData() {

		if (isset($this->monograph)) {

			$gnash = $this->contributorInsert->initData();

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

		$templateMgr->assign('components', $this->form->getData('components'));
		$this->contributorInsert->display();
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

		$this->contributorInsert->execute();

		import('monograph.MonographComponent');
		$componentsList = array();
		$j = 1;
		if (isset($components))
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
	function processEvents() {
		$eventProcessed = false;
		$submitForm = $this->form;
		$eventProcessed = $this->contributorInsert->processEvents();
		if (Request::getUserVar('addComponent') && 1) {// && work is an edited volume
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