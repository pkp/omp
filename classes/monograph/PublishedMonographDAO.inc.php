<?php

/**
 * @file classes/monograph/PublishedMonographDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublishedMonographDAO
 * @ingroup monograph
 * @see PublishedMonograph
 *
 * @brief Operations for retrieving and modifying PublishedMonograph objects.
 */

// $Id$


import('monograph.PublishedMonograph');

class PublishedMonographDAO extends DAO {
	var $monographDao;
	var $authorDao;
	var $galleyDao;
	var $suppFileDao;

 	/**
	 * Constructor.
	 */
	function PublishedMonographDAO() {
		parent::DAO();
		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->authorDao =& DAORegistry::getDAO('AuthorDAO');
//		$this->galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$this->suppFileDao =& DAORegistry::getDAO('SuppFileDAO');
	}

	// FIXME needs to be ported
}

?>
