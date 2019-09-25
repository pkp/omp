<?php

/**
 * @file classes/publication/Publication.inc.php
 *
 * Copyright (c) 2016-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Publication
 * @ingroup publication
 * @see PublicationDAO
 *
 * @brief Class for Publication.
 */
import('lib.pkp.classes.publication.PKPPublication');

class Publication extends PKPPublication {

	/**
	 * Get a string indicating all editors of an edited volume
	 * @return string
	 */
	public function getEditorString() {
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION);
		$authors = $this->getData('authors');
		$editorNames = [];
		foreach ($authors as $author) {
			if ($author->getIsVolumeEditor()) {
				$editorNames[] = __('submission.editorName', array('editorName' => $author->getFullName()));
			}
		}

		// Spaces are stripped from the locale strings, so we have to add the
		// space in here.
		return join(__('common.commaListSeparator') . ' ', $editorNames);
	}
}