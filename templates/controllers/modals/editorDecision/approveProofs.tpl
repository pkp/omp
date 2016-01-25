{**
 * templates/controllers/modals/editorDecision/approveProofs.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Load the proofs grid.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the approve proofs handler.
		$('#approveProofsContainer').pkpHandler('$.pkp.controllers.modals.editorDecision.ApproveProofsHandler');
	{rdelim});
</script>

<div id="approveProofsContainer">
	{include file="controllers/tab/workflow/publicationFormat.tpl" submission=$submission representation=$representation stageId=$smarty.const.WORKFLOW_STAGE_ID_EDITING}
</div>
