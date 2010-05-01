<?php

/**
 * @defgroup pages_seriesEditor
 */
 
/**
 * @file pages/informationCenter/index.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_informationCenter
 * @brief Handle requests for information Center functions. 
 *
 */

// $Id$


switch ($op) {
	case 'viewInformationCenter':
	case 'viewNotes':
	case 'saveNote':
	case 'deleteNote':
	case 'viewNotify':
	case 'sendNotification':
	case 'viewHistory':
	case 'index':
		define('HANDLER_CLASS', 'InformationCenterHandler');
		import('pages.informationCenter.InformationCenterHandler');
		break;
}

?>
