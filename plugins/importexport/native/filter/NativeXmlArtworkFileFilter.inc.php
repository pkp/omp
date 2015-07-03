<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlArtworkFileFilter.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlArtworkFileFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Native XML document to an artwork file.
 */

import('plugins.importexport.native.filter.NativeXmlMonographFileFilter');

class NativeXmlArtworkFileFilter extends NativeXmlMonographFileFilter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function NativeXmlArtworkFileFilter($filterGroup) {
		parent::NativeXmlMonographFileFilter($filterGroup);
	}


	//
	// Implement template methods from PersistableFilter
	//
	/**
	 * @copydoc PersistableFilter::getClassName()
	 */
	function getClassName() {
		return 'plugins.importexport.native.filter.NativeXmlArtworkFileFilter';
	}

	//
	// Override methods in NativeImportFilter
	//
	/**
	 * Return the plural element name
	 * @return string
	 */
	function getPluralElementName() {
		return 'artwork_files';
	}

	/**
	 * Get the singular element name
	 * @return string
	 */
	function getSingularElementName() {
		return 'artwork_file';
	}


	//
	// Extend functions in the parent class
	//
	/**
	 * Handle a child node of the submission file element; add new files, if
	 * any, to $submissionFiles
	 * @param $node DOMElement
	 * @param $stageId int SUBMISSION_FILE_...
	 * @param $submissionFiles array
	 */
	function handleChildElement($node, $stageId, &$submissionFiles) {
		switch ($node->tagName) {
			case 'caption':
				$submissionFiles[count($submissionFiles)-1]->setCaption($node->textContent);
				break;
			case 'credit':
				$submissionFiles[count($submissionFiles)-1]->setCredit($node->textContent);
				break;
			case 'copyright_owner':
				$submissionFiles[count($submissionFiles)-1]->setCopyrightOwner($node->textContent);
				break;
			case 'copyright_owner_contact':
				$submissionFiles[count($submissionFiles)-1]->setCopyrightOwnerContact($node->textContent);
				break;
			case 'permission_terms':
				$submissionFiles[count($submissionFiles)-1]->setPermissionTerms($node->textContent);
				break;
			default:
				return parent::handleChildElement($node, $stageId, $submissionFiles);
		}
	}
}

?>
