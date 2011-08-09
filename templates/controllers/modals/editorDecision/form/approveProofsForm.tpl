{**
 * templates/controllers/modals/editorDecision/form/approveProofs.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to select proof files to approve
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#approveProofsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler', null);
	{rdelim});
</script>

<form class="pkp_form" id="approveProofsForm" method="post" action="{url op="saveApproveProofs"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="publicationFormatId" value="{$publicationFormatId}"}

	<!-- Available submission files -->
	{url|assign:approvedProofsUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.proof.SelectableProofFilesGridHandler" op="fetchGrid" monographId=$monographId stageId=$stageId publicationFormatId=$publicationFormatId escape=false}
	{load_url_in_div id="approvedProofGrid-$publicationFormatId" url=$approvedProofsUrl}

	{fbvFormButtons submitText="editor.monograph.decision.approveProofs"}
</form>


