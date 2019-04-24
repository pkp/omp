{**
 * templates/controllers/grid/files/proof/form/approvedProofForm.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to control pricing of approved proofs for direct sales.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#approvedProofForm').pkpHandler('$.pkp.controllers.grid.files.proof.form.ApprovedProofFormHandler',
			{ldelim}
				salesType: {$salesType|json_encode}
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="approvedProofForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.PublicationFormatGridHandler" op="saveApprovedProof"}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="approvedProofFormNotification"}
	{fbvFormArea id="approvedProofInfo"}
		<input type="hidden" name="fileId" value="{$fileId|escape}" />
		<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
		<input type="hidden" name="submissionVersion" value="{$submissionVersion|escape}" />

		{include file="controllers/grid/files/proof/form/approvedProofFormFields.tpl"}
	{/fbvFormArea}
	{fbvFormButtons id="saveApprovedProofForm" submitText="common.save"}
</form>
