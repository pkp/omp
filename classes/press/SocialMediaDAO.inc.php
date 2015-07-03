<?php

/**
 * @file classes/press/SocialMediaDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SocialMediaDAO
 * @ingroup press
 * @see SocialMedia
 *
 * @brief Operations for retrieving and modifying SocialMedia objects.
 */

import ('lib.pkp.classes.context.PKPSocialMediaDAO');
import ('classes.press.SocialMedia');

class SocialMediaDAO extends PKPSocialMediaDAO {
	/**
	 * Constructor
	 */
	function SocialMediaDAO() {
		parent::PKPSocialMediaDAO();
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return SocialMedia
	 */
	function newDataObject() {
		return new SocialMedia();
	}
}

?>
