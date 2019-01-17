<?php

/**
 * @defgroup oai_format_dc Dublin Core OAI format plugin
 */

/**
 * @file plugins/oaiMetadataFormats/dc/OAIMetadataFormat_DC.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_DC
 * @ingroup oai_format_dc
 * @see OAI
 *
 * @brief OAI metadata format class -- Dublin Core.
 */
import('lib.pkp.plugins.oaiMetadataFormats.dc.PKPOAIMetadataFormat_DC');

class OAIMetadataFormat_DC extends PKPOAIMetadataFormat_DC {

	/**
	 * @see lib/pkp/plugins/oaiMetadataFormats/dc/PKPOAIMetadataFormat_DC::toXml()
	 */
	function toXml($record, $format = null) {
		$publicationFormat = $record->getData('publicationFormat');
		return parent::toXml($publicationFormat, $format);
	}
}


