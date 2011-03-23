<?php

/**
 * @file classes/log/MonographEventLogEntry.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographEventLogEntry
 * @ingroup log
 * @see MonographEventLogDAO
 *
 * @brief Describes an entry in the monograph history log.
 */

import('classes.log.OmpEventLogEntry');

// Log entry event types. All types must be defined here
define('MONOGRAPH_LOG_DEFAULT',				0);

// General events 					0x10000000
define('MONOGRAPH_LOG_MONOGRAPH_SUBMIT', 		0x10000001);
define('MONOGRAPH_LOG_METADATA_UPDATE', 		0x10000002);
define('MONOGRAPH_LOG_SUPPFILE_UPDATE', 		0x10000003);
define('MONOGRAPH_LOG_MONOGRAPH_PUBLISH', 		0x10000006);
define('MONOGRAPH_LOG_MONOGRAPH_IMPORT',		0x10000007);

// Author events 					0x20000000
define('MONOGRAPH_LOG_AUTHOR_REVISION', 		0x20000001);

// Editor events 					0x30000000
define('MONOGRAPH_LOG_EDITOR_ASSIGN', 			0x30000001);
define('MONOGRAPH_LOG_EDITOR_UNASSIGN',	 		0x30000002);
define('MONOGRAPH_LOG_EDITOR_DECISION', 		0x30000003);
define('MONOGRAPH_LOG_EDITOR_FILE', 			0x30000004);
define('MONOGRAPH_LOG_EDITOR_ARCHIVE', 			0x30000005);
define('MONOGRAPH_LOG_EDITOR_RESTORE', 			0x30000006);
define('MONOGRAPH_LOG_EDITOR_EXPEDITE', 		0x30000007);

// Reviewer events 					0x40000000
define('MONOGRAPH_LOG_REVIEW_ASSIGN', 			0x40000001);
define('MONOGRAPH_LOG_REVIEW_UNASSIGN',		 	0x40000002);
define('MONOGRAPH_LOG_REVIEW_INITIATE', 		0x40000003);
define('MONOGRAPH_LOG_REVIEW_CANCEL', 			0x40000004);
define('MONOGRAPH_LOG_REVIEW_REINITIATE', 		0x40000005);
define('MONOGRAPH_LOG_REVIEW_ACCEPT', 			0x40000006);
define('MONOGRAPH_LOG_REVIEW_DECLINE', 			0x40000007);
define('MONOGRAPH_LOG_REVIEW_REVISION', 		0x40000008);
define('MONOGRAPH_LOG_REVIEW_RECOMMENDATION', 		0x40000009);
define('MONOGRAPH_LOG_REVIEW_RATE', 			0x40000010);
define('MONOGRAPH_LOG_REVIEW_SET_DUE_DATE', 		0x40000011);
define('MONOGRAPH_LOG_REVIEW_RESUBMIT', 		0x40000012);
define('MONOGRAPH_LOG_REVIEW_FILE', 			0x40000013);
define('MONOGRAPH_LOG_REVIEW_CLEAR', 			0x40000014);
define('MONOGRAPH_LOG_REVIEW_CONFIRM_BY_PROXY', 	0x40000015);
define('MONOGRAPH_LOG_REVIEW_RECOMMENDATION_BY_PROXY', 	0x40000016);
define('MONOGRAPH_LOG_REVIEW_FILE_BY_PROXY', 		0x40000017);

// Copyeditor events 					0x50000000
define('MONOGRAPH_LOG_COPYEDIT_ASSIGN', 		0x50000001);
define('MONOGRAPH_LOG_COPYEDIT_UNASSIGN',		0x50000002);
define('MONOGRAPH_LOG_COPYEDIT_INITIATE', 		0x50000003);
define('MONOGRAPH_LOG_COPYEDIT_REVISION', 		0x50000004);
define('MONOGRAPH_LOG_COPYEDIT_INITIAL', 		0x50000005);
define('MONOGRAPH_LOG_COPYEDIT_FINAL', 			0x50000006);
define('MONOGRAPH_LOG_COPYEDIT_SET_FILE',		0x50000007);
define('MONOGRAPH_LOG_COPYEDIT_COPYEDIT_FILE',		0x50000008);
define('MONOGRAPH_LOG_COPYEDIT_COPYEDITOR_FILE',	0x50000009);

// Proofreader events 					0x60000000
define('MONOGRAPH_LOG_PROOFREAD_ASSIGN', 		0x60000001);
define('MONOGRAPH_LOG_PROOFREAD_UNASSIGN', 		0x60000002);
define('MONOGRAPH_LOG_PROOFREAD_INITIATE', 		0x60000003);
define('MONOGRAPH_LOG_PROOFREAD_REVISION', 		0x60000004);
define('MONOGRAPH_LOG_PROOFREAD_COMPLETE', 		0x60000005);

// Layout events 					0x70000000
define('MONOGRAPH_LOG_LAYOUT_ASSIGN', 			0x70000001);
define('MONOGRAPH_LOG_LAYOUT_UNASSIGN', 		0x70000002);
define('MONOGRAPH_LOG_LAYOUT_INITIATE', 		0x70000003);
define('MONOGRAPH_LOG_LAYOUT_GALLEY', 			0x70000004);
define('MONOGRAPH_LOG_LAYOUT_COMPLETE', 		0x70000005);

// Production events
define('MONOGRAPH_LOG_PRODUCTION_ASSIGN',		0x80000000);

// File events
define('MONOGRAPH_LOG_FILE_UPLOADED',			0x90000001);

class MonographEventLogEntry extends OmpEventLogEntry {
	/**
	 * Constructor.
	 */
	function MonographEventLogEntry() {
		parent::OmpEventLogEntry();
	}

	function setMonographId($monographId) {
		return $this->setMonographId($monographId);
	}

	function getMonographId() {
		return $this->getMonographId();
	}

	/**
	 * Return locale message key describing event type.
	 * @return string
	 */
	function getEventTitle() {
		switch ($this->getData('eventType')) {
			// General events
			case MONOGRAPH_LOG_MONOGRAPH_SUBMIT:
				return 'submission.event.general.monographSubmitted';
			case MONOGRAPH_LOG_METADATA_UPDATE:
				return 'submission.event.general.metadataUpdated';
			case MONOGRAPH_LOG_SUPPFILE_UPDATE:
				return 'submission.event.general.suppFileUpdated';
			case MONOGRAPH_LOG_MONOGRAPH_PUBLISH:
				return 'submission.event.general.monographPublished';

			// Author events
			case MONOGRAPH_LOG_AUTHOR_REVISION:
				return 'submission.event.author.authorRevision';

			// Editor events
			case MONOGRAPH_LOG_EDITOR_ASSIGN:
				return 'submission.event.editor.editorAssigned';
			case MONOGRAPH_LOG_EDITOR_UNASSIGN:
				return 'submission.event.editor.editorUnassigned';
			case MONOGRAPH_LOG_EDITOR_DECISION:
				return 'submission.event.editor.editorDecision';
			case MONOGRAPH_LOG_EDITOR_FILE:
				return 'submission.event.editor.editorFile';
			case MONOGRAPH_LOG_EDITOR_ARCHIVE:
				return 'submission.event.editor.submissionArchived';
			case MONOGRAPH_LOG_EDITOR_RESTORE:
				return 'submission.event.editor.submissionRestored';

			// Reviewer events
			case MONOGRAPH_LOG_REVIEW_ASSIGN:
				return 'submission.event.reviewer.reviewerAssigned';
			case MONOGRAPH_LOG_REVIEW_UNASSIGN:
				return 'submission.event.reviewer.reviewerUnassigned';
			case MONOGRAPH_LOG_REVIEW_INITIATE:
				return 'submission.event.reviewer.reviewInitiated';
			case MONOGRAPH_LOG_REVIEW_CANCEL:
				return 'submission.event.reviewer.reviewCancelled';
			case MONOGRAPH_LOG_REVIEW_REINITIATE:
				return 'submission.event.reviewer.reviewReinitiated';
			case MONOGRAPH_LOG_REVIEW_CONFIRM_BY_PROXY:
				return 'submission.event.reviewer.reviewAcceptedByProxy';
			case MONOGRAPH_LOG_REVIEW_ACCEPT:
				return 'submission.event.reviewer.reviewAccepted';
			case MONOGRAPH_LOG_REVIEW_DECLINE:
				return 'submission.event.reviewer.reviewDeclined';
			case MONOGRAPH_LOG_REVIEW_REVISION:
				return 'submission.event.reviewer.reviewRevision';
			case MONOGRAPH_LOG_REVIEW_RECOMMENDATION:
				return 'submission.event.reviewer.reviewRecommendation';
			case MONOGRAPH_LOG_REVIEW_RATE:
				return 'submission.event.reviewer.reviewerRated';
			case MONOGRAPH_LOG_REVIEW_SET_DUE_DATE:
				return 'submission.event.reviewer.reviewDueDate';
			case MONOGRAPH_LOG_REVIEW_RESUBMIT:
				return 'submission.event.reviewer.reviewResubmitted';
			case MONOGRAPH_LOG_REVIEW_FILE:
				return 'submission.event.reviewer.reviewFile';

			// Copyeditor events
			case MONOGRAPH_LOG_COPYEDIT_ASSIGN:
				return 'submission.event.copyedit.copyeditorAssigned';
			case MONOGRAPH_LOG_COPYEDIT_UNASSIGN:
				return 'submission.event.copyedit.copyeditorUnassigned';
			case MONOGRAPH_LOG_COPYEDIT_INITIATE:
				return 'submission.event.copyedit.copyeditInitiated';
			case MONOGRAPH_LOG_COPYEDIT_REVISION:
				return 'submission.event.copyedit.copyeditRevision';
			case MONOGRAPH_LOG_COPYEDIT_INITIAL:
				return 'submission.event.copyedit.copyeditInitialCompleted';
			case MONOGRAPH_LOG_COPYEDIT_FINAL:
				return 'submission.event.copyedit.copyeditFinalCompleted';
			case MONOGRAPH_LOG_COPYEDIT_SET_FILE:
				return 'submission.event.copyedit.copyeditSetFile';

			// Proofreader events
			case MONOGRAPH_LOG_PROOFREAD_ASSIGN:
				return 'submission.event.proofread.proofreaderAssigned';
			case MONOGRAPH_LOG_PROOFREAD_UNASSIGN:
				return 'submission.event.proofread.proofreaderUnassigned';
			case MONOGRAPH_LOG_PROOFREAD_INITIATE:
				return 'submission.event.proofread.proofreadInitiated';
			case MONOGRAPH_LOG_PROOFREAD_REVISION:
				return 'submission.event.proofread.proofreadRevision';
			case MONOGRAPH_LOG_PROOFREAD_COMPLETE:
				return 'submission.event.proofread.proofreadCompleted';

			// Layout events
			case MONOGRAPH_LOG_LAYOUT_ASSIGN:
				return 'submission.event.layout.layoutEditorAssigned';
			case MONOGRAPH_LOG_LAYOUT_UNASSIGN:
				return 'submission.event.layout.layoutEditorUnassigned';
			case MONOGRAPH_LOG_LAYOUT_INITIATE:
				return 'submission.event.layout.layoutInitiated';
			case MONOGRAPH_LOG_LAYOUT_GALLEY:
				return 'submission.event.layout.layoutGalleyCreated';
			case MONOGRAPH_LOG_LAYOUT_COMPLETE:
				return 'submission.event.layout.layoutComplete';

			// Production events
			case MONOGRAPH_LOG_PRODUCTION_ASSIGN:
				return 'submission.event.production.productionEditorAssigned';

			default:
				return parent::getEventTitle();
		}
	}
}

?>
