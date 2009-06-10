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
	function &initData() {
		$returner = array();

		if (isset($this->monograph)) {

			$insertReturns =& $this->contributorInsert->initData();
			$idMap = $insertReturns['lookup'];
			unset($insertReturns['lookup']);

			$components =& $this->monograph->getMonographComponents();
			$formComponents = array();

			import('monograph.Author');
			for ($i=0, $count=count($components); $i < $count; $i++) {
				$cas = array();
				$primaryContactIndex = 0;
				foreach ($components[$i]->getMonographComponentAuthors() as $ca) {
					array_push($cas, array(
								'authorId' => $idMap[$ca->getId()],
								'email' => $ca->getEmail(),
								'firstName' => $ca->getFirstName(),
								'lastName' => $ca->getLastName()
							)
						);
					if ($components[$i]->getPrimaryContact() == $ca->getId()) {
						$primaryContactIndex = $idMap[$ca->getId()];
					}
				}
				array_push(
					$formComponents,
					array (
						'title' => $components[$i]->getTitle(null),
						'authors' => $cas,
						'primaryContact' => $primaryContactIndex
					)
				);
			}
			$insertReturns['components'] = $formComponents;
			$insertReturns['workType'] = $this->monograph->getWorkType();
			$returner = $insertReturns;
		}
		return $returner;
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
			$component->setPrimaryContact(isset($componentInfo['primaryContact']) ? $componentInfo['primaryContact'] : 0);
			$component->setMonographId($monograph->getMonographId());
			$component->setSequence($j);
			if (isset($componentInfo['authors'])) {
				$i = 1;
				foreach ($componentInfo['authors'] as $componentAuthor) {
					// Create a new author
					$author = new Author();
					$author->setId((int) $componentAuthor['authorId']);
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
				for ($i = 0,$count=count($newComponent['authors']); $i<$count; $i++) {
					$newComponent['authors'][$i]['authorId'] = $newComponent['authors'][$i];
				}
				$keyMap = array_keys($newComponent['authors']);
				$newComponent['primaryContact'] = $newComponent['authors'][0]['authorId'];
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
				unset($components[$removeComponentIndex]);
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

			$componentInfo = null;
			$componentInfo = $components[$removeComponentIndex]['authors'][$componentAuthorIndex];

			if (isset($componentInfo)) {
				unset($components[$removeComponentIndex]['authors'][$componentAuthorIndex]);
				if ($components[$removeComponentIndex]['primaryContact'] == $componentInfo) {
					$keyMap = array_keys($components[$removeComponentIndex]['authors']);
					$components[$removeComponentIndex]['primaryContact'] = isset($keyMap[0]) ? $keyMap[0] : 0;
				}
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

			$keyMap = array_keys($componentAuthors);

			if (isset($componentAuthors) && !(($moveComponentAuthorDir == 'u' && $moveComponentAuthorIndex <= 0) || ($moveComponentAuthorDir == 'd' && $moveComponentAuthorIndex >= count($componentAuthors)-1))) {
				$tmpAuthor = $componentAuthors[$keyMap[$moveComponentAuthorIndex]];
				if ($moveComponentAuthorDir == 'u') {
					$componentAuthors[$keyMap[$moveComponentAuthorIndex]] = $componentAuthors[$keyMap[$moveComponentAuthorIndex - 1]];
					$componentAuthors[$keyMap[$moveComponentAuthorIndex - 1]] = $tmpAuthor;
				} else {
					$componentAuthors[$keyMap[$moveComponentAuthorIndex]] = $componentAuthors[$keyMap[$moveComponentAuthorIndex + 1]];
					$componentAuthors[$keyMap[$moveComponentAuthorIndex + 1]] = $tmpAuthor;
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

		$registry =& Registry::getRegistry();
		if (isset($registry['inserts_ContributorInsert_removeAuthor'])) {
			$eventProcessed = true;
			$removeAuthor = Registry::get('inserts_ContributorInsert_removeAuthor');
			$components = $form->getData('components');

			for ($i=0,$count=count($components); $i<$count; $i++) {
				if (isset($components[$i]['authors'][$removeAuthor])) {
					$authorId = $components[$i]['authors'][$removeAuthor];
					unset($components[$i]['authors'][$removeAuthor]);
					if ($components[$i]['primaryContact'] == $authorId) {
						$keyMap = array_keys($components[$i]['authors']);
						$components[$i]['primaryContact'] = isset($keyMap[0]) ? $keyMap[0] : 0;
					}

				}
			}
			$form->setData('components', $components);
		}
		return $eventProcessed;
	}
}

?>