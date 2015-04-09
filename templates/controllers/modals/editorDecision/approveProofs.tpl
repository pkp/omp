{**
 * templates/controllers/modals/editorDecision/approveProofs.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	{include file="controllers/tab/workflow/publicationFormat.tpl" submission=$submission publicationFormat=$publicationFormat}
</div>
