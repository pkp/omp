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

import('inserts.Insert');
import('inserts.contributors.ContributorInsert');

class MonographComponentsInsert extends Insert
{
	var $contributorInsert;
	var $monograph;

	function MonographComponentsInsert(&$monograph, $options = 0, $contributorOptions = 0) {
		parent::Insert($options);
		$this->contributorInsert = new ContributorInsert($monograph, $contributorOptions);
		$this->monograph =& $monograph;
	}
	function &listUserVars() {
		$returner = array_merge(array('components','newComponent'), $this->contributorInsert->listUserVars());
		return $returner;
	}
	function initData(&$form) {

		if (isset($this->monograph)) {

			$insertReturns =& $this->contributorInsert->initData($form);
			$idMap = $insertReturns['lookup'];

			$components =& $this->monograph->getMonographComponents();
			$formComponents = array();

			import('monograph.Author');
			for ($i=0, $count=count($components); $i < $count; $i++) {
				$cas = array();
				foreach ($components[$i]->getMonographComponentAuthors() as $ca) {
					array_push($cas, array(
								'authorId' => $idMap[$ca->getId()],
								'email' => $ca->getEmail(),
								'firstName' => $ca->getFirstName(),
								'lastName' => $ca->getLastName()
							)
						);
				}
				array_push(
					$formComponents,
					array (
						'title' => $components[$i]->getTitle(null),
						'authors' => $cas
					)
				);
			}
			$returner = array ('components' => $formComponents, 
						'contributors'=>$insertReturns['contributors'],
						'newContributor'=>$insertReturns['newContributor'], 'primaryContact'=>$insertReturns['primaryContact']
					);
			return $returner;
		}
		return array();
	}
	function display(&$form) {
		
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('components', $form->getData('components'));
		$this->contributorInsert->display($form);
	}

	function execute(&$form, &$monograph) {
		$this->contributorInsert->execute($form, $monograph);

		$components = $form->getData('components');

		import('monograph.MonographComponent');
		$componentsList = array();
		$j = 1;
		if (isset($components))
		foreach ($components as $componentInfo) {
			$component = new MonographComponent;
			$component->setTitle($componentInfo['title'], null);
			$component->setMonographId($monograph->getMonographId());
			$component->setSequence($j);
			if(count($component->getMonographComponentAuthors()))
				$component->setPrimaryContact($componentInfo['primaryContact']);
			
			if (isset($componentInfo['authors'])) {
				$i = 1;
				foreach ($componentInfo['authors'] as $componentAuthor) {
					// Create a new author
					$author = new Author();
					$authorId = (int) $componentAuthor['authorId'];
					$author->setId($authorId);
					$author->setSequence($i);
					$component->addMonographComponentAuthor($author);
					$i++;
				}
			}
			array_push($componentsList, $component);
			$j++;
		}
		$monograph->setMonographComponents($componentsList);

	}
	function processEvents(&$form) {
		$eventProcessed = false;
		$eventProcessed = $this->contributorInsert->processEvents($form);

		if (Request::getUserVar('addComponent')) {
			$eventProcessed = true;
			$newComponent = $form->getData('newComponent');
			$components = $form->getData('components');
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
			$form->setData('components', $components);
		} else if ($deleteComponent = Request::getUserVar('deleteComponent')) {
			// Remove a component
			$eventProcessed = true;
			list($removeComponentIndex) = array_keys($deleteComponent);
			$removeComponentIndex = (int) $removeComponentIndex;
			$components = $form->getData('components');

			if (isset($components[$removeComponentIndex])) {
				$components[$removeComponentIndex]['deleted'] = (int) true;
				if (count($components) <= 2) {
					unset($components[$removeComponentIndex]);
				}
			}
			$form->setData('components', $components);
		} else if ($removeAuthor = Request::getUserVar('removeComponentAuthor')) {
			// Remove a component author
			$eventProcessed = true;
			list($removeComponentIndex) = array_keys($removeAuthor);
			list($componentAuthorIndex) = array_keys($removeAuthor[$removeComponentIndex]);
			$removeComponentIndex = (int) $removeComponentIndex;
			$componentAuthorIndex = (int) $componentAuthorIndex;
			$components = $form->getData('components');

			if (isset($components[$removeComponentIndex]['authors'][$componentAuthorIndex])) {
				$components[$removeComponentIndex]['authors'][$componentAuthorIndex]['removed'] = (int) true;
			}
			$form->setData('components', $components);
		} else if (Request::getUserVar('moveComponentAuthor')) {
			$eventProcessed = true;
			$moveComponentAuthorDir = Request::getUserVar('moveComponentAuthorDir');
			$moveComponentAuthorDir = $moveComponentAuthorDir == 'u' ? 'u' : 'd';
			$moveComponentAuthorIndex = (int) Request::getUserVar('moveComponentAuthorIndex');
			$componentIndex = (int) Request::getUserVar('moveComponentAuthorComponentIndex');
			$components = $form->getData('components');
			$componentAuthors = isset($components[$componentIndex]) && isset($components[$componentIndex]['authors']) ? $components[$componentIndex]['authors'] : null;

			if (isset($componentAuthors) && !(($moveComponentAuthorDir == 'u' && $moveComponentAuthorIndex <= 0) || ($moveComponentAuthorDir == 'd' && $moveComponentAuthorIndex >= count($componentAuthors)-1))) {
				$tmpAuthor = $componentAuthors[$moveComponentAuthorIndex];
				if ($moveComponentAuthorDir == 'u') {
					$componentAuthors[$moveComponentAuthorIndex] = $componentAuthors[$moveComponentAuthorIndex - 1];
					$componentAuthors[$moveComponentAuthorIndex - 1] = $tmpAuthor;
				} else {
					$componentAuthors[$moveComponentAuthorIndex] = $componentAuthors[$moveComponentAuthorIndex + 1];
					$componentAuthors[$moveComponentAuthorIndex + 1] = $tmpAuthor;
				}
			}
			$components[$componentIndex]['authors'] = $componentAuthors;
			$form->setData('components', $components);

		} else if (Request::getUserVar('moveComponent')) {
			$eventProcessed = true;
			$moveComponentDir = Request::getUserVar('moveComponentDir');
			$moveComponentDir = $moveComponentDir == 'u' ? 'u' : 'd';
			$moveComponentIndex = (int) Request::getUserVar('moveComponentIndex');
			$components = $form->getData('components');
			if (!(($moveComponentDir == 'u' && $moveComponentIndex <= 0) || ($moveComponentDir == 'd' && $moveComponentIndex >= count($components)-1))) {
				$tmpComponent = $components[$moveComponentIndex];
				if ($moveComponentDir == 'u') {
					$components[$moveComponentIndex] = $components[$moveComponentIndex - 1];
					$components[$moveComponentIndex - 1] = $tmpComponent;
				} else {
					$components[$moveComponentIndex] = $components[$moveComponentIndex + 1];
					$components[$moveComponentIndex + 1] = $tmpComponent;
				}
			}
			$form->setData('components', $components);
		}
		return $eventProcessed;
	}
}

?>