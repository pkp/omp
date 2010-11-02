<?php

/**
 * @defgroup pages_production
 */
 
/**
 * @file pages/production/index.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_production
 * @brief Handle information requests. 
 *
 */



switch ($op) {
	case 'index':
	case 'selectProofreader':
	case 'selectDesigner':
	case 'notifyaAuthorProofreader':
	case 'thankAuthorProofreader':
	case 'editorInitiateProofreader':
	case 'editorCompleteProofreader':
	case 'notifyProofreader':
	case 'thankProofreader':
	case 'editorInitiateLayoutEditor':
	case 'editorCompleteLayoutEditor':
	case 'notifyDesigner':
	case 'thankLayoutEditorProofreader':
	case 'submission':
	case 'submitArtwork':
	case 'submissionArt':
	case 'productionAssignment':
	case 'deleteSelectedAssignments':
	case 'uploadLayoutFile':
	case 'removeArtworkFile':
	case 'addProductionAssignment':
	case 'uploadArtworkFile':
	case 'submissionLayout':
	case 'viewMetadata':
	case 'downloadFile':
	case 'viewFile':
	case 'notifyLayoutDesigner':
	case 'thankLayoutDesigner':
	case 'uploadGalley':
	case 'editGalley':
	case 'saveGalley':
	case 'orderGalley':
	case 'deleteGalley':
		define('HANDLER_CLASS', 'ProductionEditorHandler');
		import('pages.productionEditor.ProductionEditorHandler');
		break;
}

?>
