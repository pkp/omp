<?php

/**
 * @file plugins/metadata/mods34/filter/Mods34SchemaMonographAdapter.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Mods34SchemaMonographAdapter
 * @ingroup plugins_metadata_mods34_filter
 * @see Monograph
 * @see Mods34Schema
 *
 * @brief Class that inject/extract MODS schema compliant meta-data
 *  into/from a Monograph object.
 */

import('lib.pkp.plugins.metadata.mods34.filter.Mods34SchemaSubmissionAdapter');

class Mods34SchemaMonographAdapter extends Mods34SchemaSubmissionAdapter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function __construct(&$filterGroup) {
		// Configure the submission adapter
		parent::__construct($filterGroup);
	}


	//
	// Implement template methods from Filter
	//
	/**
	 * @see Filter::getClassName()
	 */
	function getClassName() {
		return 'plugins.metadata.mods34.filter.Mods34SchemaMonographAdapter';
	}


	//
	// Implement template methods from MetadataDataObjectAdapter
	//
	/**
	 * @see MetadataDataObjectAdapter::injectMetadataIntoDataObject()
	 * @param $mods34Description MetadataDescription
	 * @param $monograph Monograph
	 */
	function &injectMetadataIntoDataObject(&$mods34Description, &$monograph) {
		assert(is_a($monograph, 'Monograph'));
		$monograph =& parent::injectMetadataIntoDataObject($mods34Description, $monograph, 'classes.monograph.Author');

		// Publication date
		$publicationDate = $mods34Description->getStatement('originInfo/dateIssued[@keyDate="yes" @encoding="w3cdtf"]');
		if ($publicationDate) {
			$monograph->setDatePublished($publicationDate);
		}

		// ...
		// FIXME: go through MODS schema and see what context-specific
		// information needs to be added, e.g. from Press, press settings
		// or site settings.

		return $monograph;
	}

	/**
	 * @see MetadataDataObjectAdapter::extractMetadataFromDataObject()
	 * @param $monograph Monograph
	 */
	function extractMetadataFromDataObject(&$monograph) {
		assert(is_a($monograph, 'Monograph'));

		// Define the role of the author(s) of the monograph object
		// depending on the work type.
		if ($monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME) {
			// Marcrelator editor role
			$authorMarcrelatorRole = 'edt';
		} else {
			// Marcrelator author role
			$authorMarcrelatorRole = 'aut';
		}
		$mods34Description = parent::extractMetadataFromDataObject($monograph, $authorMarcrelatorRole);

		// Publication date
		$publicationDate = $monograph->getDatePublished();
		if ($publicationDate) {
			$mods34Description->addStatement('originInfo/dateIssued[@keyDate="yes" @encoding="w3cdtf"]', $publicationDate);
		}

		// ...
		// FIXME: go through MODS schema and see what context-specific
		// information needs to be added, e.g. from Press, press settings
		// or site settings.

		return $mods34Description;
	}
}

