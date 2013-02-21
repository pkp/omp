{**
 * templates/controllers/grid/files/proof/form/approvedProofForm.tpl
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to control pricing of approved proofs for direct sales.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#approvedProofForm').pkpHandler('$.pkp.controllers.grid.files.proof.form.ApprovedProofFormHandler',
			{ldelim}
				salesType: '{$salesType|escape:"javascript"}'
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="approvedProofForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.files.proof.ApprovedProofFilesGridHandler" op="saveApprovedProof"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="approvedProofFormNotification"}
	{fbvFormArea id="approvedProofInfo"}
		<input type="hidden" name="fileId" value="{$fileId|escape}" />
		<input type="hidden" name="monographId" value="{$monographId|escape}" />
		<input type="hidden" name="publicationFormatId" value="{$publicationFormatId|escape}" />

		{fbvFormSection for="priceType" list=true description="payment.directSales.price.description"}
			{foreach from=$salesTypes key=salesTypeKey item=salesType}
				{fbvElement type="radio" name="salesType" value=$salesTypeKey label=$salesType id=$salesTypeKey}
			{/foreach}
		{/fbvFormSection}

		{fbvFormSection for="price" size=$fbvStyles.size.MEDIUM inline=true}
			{translate|assign:"priceLabel" key="payment.directSales.priceCurrency" currency=$currentPress->getSetting('currency')}
			{fbvElement type="text" id="price" label=$priceLabel subLabelTranslate=false value=$price maxlength="255"}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons id="saveApprovedProofForm" submitText="common.save"}
</form>
