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
		$returner = array_merge(array('components','newComponent', 'deletedComponents'), $this->contributorInsert->listUserVars());
		return $returner;
	}
	function &initData() {
		$returner = array();

		if (isset($this->monograph)) {

			$insertReturns =& $this->contributorInsert->initData();
			$idMap = $insertReturns['lookup'];
			unset($insertReturns['lookup']);

			$components =& $this->monograph->getComponents();
			$formComponents = array();

			import('monograph.Author');
			for ($i=0, $count=count($components); $i < $count; $i++) {
				$componentAuthors = array();
				$primaryContactIndex = 0;
				foreach ($components[$i]->getAuthors() as $componentAuthor) {
					array_push($componentAuthors, array(
						'pivotId' => $idMap[$componentAuthor->getId()],
						'email' => $componentAuthor->getEmail(),
						'firstName' => $componentAuthor->getFirstName(),
						'lastName' => $componentAuthor->getLastName()
						)
					);
					if ($components[$i]->getPrimaryContact() == $componentAuthor->getId()) {
						$primaryContactIndex = $idMap[$componentAuthor->getId()];
					}
				}
				array_push(
					$formComponents,
					array (
						'title' => $components[$i]->getTitle(null),
						'componentId' => $components[$i]->getId(),
						'authors' => $componentAuthors,
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

		import('monograph.MonographComponent');
		import('monograph.Author');

		$components = $form->getData('components');

		$i = 0;
		foreach ($components as $componentData) {
			if ($componentData['componentId'] > 0) {
				// Update an existing component
				$component =& $monograph->getComponent($componentData['componentId']);
				$isExistingComponent = true;
			} else {
				// Create a new component
				$component = new MonographComponent();
				$isExistingComponent = false;
			}
			$component->setTitle($componentData['title'], null);
			$component->setPrimaryContact(isset($componentData['primaryContact']) ? $componentData['primaryContact'] : 0);
			$component->setMonographId($monograph->getMonographId());
			$component->setSequence($i+1);

			if (!empty($componentData['authors'])) {
				$j = 1;
				$authorList = array();
				foreach ($componentData['authors'] as $authorData) {
					// Create a new author
					$author = new Author();
					$author->setId((int) $authorData['pivotId']);
					$author->setSequence($j);
					array_push($authorList, $author);
					unset($author);
					$j++;
				}
				$component->setAuthors($authorList);
			} else {
				$component->setAuthors(array());
			}
			if (!$isExistingComponent) {
				$monograph->addComponent($component);
 			}
			unset($component);
			$i++;
		}

		// Remove deleted components
		$deletedComponents = explode(':', $form->getData('deletedComponents'));
		for ($i=0, $count=count($deletedComponents); $i < $count; $i++) {
			$monograph->removeComponent($deletedComponents[$i]);
		}
		return $idMap;
	}
	function processEvents(&$form) {
		$eventProcessed = false;
		$eventProcessed = $contributorEventProcessed = $this->contributorInsert->processEvents($form);

		if (Request::getUserVar('addComponent')) {
			$eventProcessed = true;
			$newComponent = $form->getData('newComponent');
			$components = $form->getData('components');
			$components = !isset($components) ? array() : $components;
			$authorlist = array();
			if (isset($newComponent['authors'])) {
				for ($i = 0,$count=count($newComponent['authors']); $i<$count; $i++) {
					$newComponent['authors'][$i]['pivotId'] = $newComponent['authors'][$i];
				}
				$keyMap = array_keys($newComponent['authors']);
				$newComponent['primaryContact'] = $newComponent['authors'][$keyMap[0]];
			}
			array_push($components, $newComponent);
			$form->setData('components', $components);
		} else if ($deleteComponent = Request::getUserVar('deleteComponent')) {
			// Remove a component
			$eventProcessed = true;
			list($removeComponentIndex) = array_keys($deleteComponent);
			$removeComponentIndex = (int) $removeComponentIndex;
			$components = $form->getData('components');

			if (isset($components[$removeComponentIndex]) && !empty($components[$removeComponentIndex]['componentId'])) {
				$deletedComponents = explode(':', $form->getData('deletedComponents'));
				array_push($deletedComponents, $components[$removeComponentIndex]['componentId']);
				$form->setData('deletedComponents', join(':', $deletedComponents));
			}
			array_splice($components, $removeComponentIndex, 1);

			$form->setData('components', $components);
		} else if ($removeAuthor = Request::getUserVar('removeComponentAuthor')) {
			// Remove a component author
			$eventProcessed = true;
			list($removeComponentIndex) = array_keys($removeAuthor);
			list($componentAuthorIndex) = array_keys($removeAuthor[$removeComponentIndex]);
			$removeComponentIndex = (int) $removeComponentIndex;
			$componentAuthorIndex = (int) $componentAuthorIndex;
			$components = $form->getData('components');

			$pivotId = null;
			$pivotId = $components[$removeComponentIndex]['authors'][$componentAuthorIndex]['pivotId'];

			if (isset($pivotId)) {
				unset($components[$removeComponentIndex]['authors'][$componentAuthorIndex]);
				if ($components[$removeComponentIndex]['primaryContact'] == $pivotId) {
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

		if (($removeAuthor = Registry::get('inserts_ContributorInsert_removeAuthor')) !== null) {
			$eventProcessed = true;
			$components = $form->getData('components');
			$updatedComponents = array();
			foreach ($components as $component) {
				if (isset($component['authors'][$removeAuthor])) {
					unset($component['authors'][$removeAuthor]);
					if ($component['primaryContact'] == $removeAuthor) {
						$keyMap = array_keys($component['authors']);
						$component['primaryContact'] = isset($keyMap[0]) ? $keyMap[0] : 0;
					}
				}
				array_push($updatedComponents, $component);
			}
			$form->setData('components', $updatedComponents);
		}

		if (!$contributorEventProcessed && $eventProcessed) {
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('scrollToComponents', true);
		}

		return $eventProcessed;
	}
}

?>