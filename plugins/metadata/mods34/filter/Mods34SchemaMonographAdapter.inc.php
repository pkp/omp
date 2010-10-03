<?php

/**
 * @file plugins/metadata/mods34/filter/Mods34SchemaMonographAdapter.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Mods34SchemaMonographAdapter
 * @ingroup plugins_metadata_mods_filter
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
	 */
	function Mods34SchemaMonographAdapter() {
		// Configure the submission adapter
		parent::Mods34SchemaSubmissionAdapter(ASSOC_TYPE_MONOGRAPH);
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
	 * @param $modsDescription MetadataDescription
	 * @param $monograph Monograph
	 * @param $replace boolean whether to replace the existing monograph
	 */
	function &injectMetadataIntoDataObject(&$modsDescription, &$monograph, $replace) {
		assert(is_a($monograph, 'Monograph'));
		$monograph =& parent::injectMetadataIntoDataObject($modsDescription, $monograph, $replace, 'classes.monograph.Author');

		// Publication date
		$publicationDate = $modsDescription->getStatement('originInfo/dateIssued[@keyDate="yes" @encoding="w3cdtf"]');
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
	function &extractMetadataFromDataObject(&$monograph) {
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
		$modsDescription =& parent::extractMetadataFromDataObject($monograph, $authorMarcrelatorRole);

		// Publication date
		$publicationDate = $monograph->getDatePublished();
		if ($publicationDate) {
			$modsDescription->addStatement('originInfo/dateIssued[@keyDate="yes" @encoding="w3cdtf"]', $publicationDate);
		}

		// ...
		// FIXME: go through MODS schema and see what context-specific
		// information needs to be added, e.g. from Press, press settings
		// or site settings.

		return $modsDescription;
	}
}
?>
