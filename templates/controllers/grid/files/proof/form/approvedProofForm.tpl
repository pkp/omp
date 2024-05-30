{**
 * templates/controllers/grid/files/proof/form/approvedProofForm.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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

<form class="pkp_form" id="approvedProofForm" method="post" action="{url router=PKP\core\PKPApplication::ROUTE_COMPONENT component="grid.catalogEntry.PublicationFormatGridHandler" op="saveApprovedProof"}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="approvedProofFormNotification"}
	{fbvFormArea id="approvedProofInfo"}
		<input type="hidden" name="submissionFileId" value="{$submissionFileId|escape}" />
		<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
		<input type="hidden" name="representationId" value="{$representationId|escape}" />
		<input type="hidden" name="publicationId" value="{$publicationId|escape}" />

		{include file="controllers/grid/files/proof/form/approvedProofFormFields.tpl"}
	{/fbvFormArea}
	{fbvFormButtons id="saveApprovedProofForm" submitText="common.save"}
</form>
