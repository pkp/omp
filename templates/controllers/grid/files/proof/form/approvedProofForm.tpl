{**
 * templates/controllers/grid/files/proof/form/approvedProofForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to control pricing of approved proofs for direct sales.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#approvedProofForm').pkpHandler('$.pkp.controllers.grid.files.proof.form.ApprovedProofFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="approvedProofForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.files.proof.ApprovedProofFilesGridHandler" op="saveApprovedProof"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="approvedProofFormNotification"}
	{fbvFormArea id="approvedProofInfo"}
		<input type="hidden" name="fileId" value="{$fileId|escape}" />
		<input type="hidden" name="monographId" value="{$monographId|escape}" />
		<input type="hidden" name="publicationFormatId" value="{$publicationFormatId|escape}" />

		{fbvFormSection for="priceType" list=true size=$fbvStyles.size.MEDIUM description="payment.directSales.price.description"}
			{fbvElement type="radio" name="salesType" id="notAvailable" value="notAvailable" label="payment.directSales.notAvailable"}
			{fbvElement type="radio" name="salesType" id="directSales" value="directSales" label="payment.directSales.directSales"}
			{fbvElement type="radio" name="salesType" id="openAccess" value="openAccess" label="payment.directSales.openAccess"}
		{/fbvFormSection}

		{fbvFormSection for="price" size=$fbvStyles.size.MEDIUM inline=true}
			{translate|assign:"priceLabel" key="payment.directSales.priceCurrency" currency=$currentPress->getSetting('pressCurrency')}
			{fbvElement type="text" id="price" label=$priceLabel subLabelTranslate=false value=$price maxlength="255"}
		{/fbvFormSection}

		{fbvFormSection}
			{* Fixme: This is to make the price element smaller *}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons id="saveApprovedProofForm" submitText="common.save"}
</form>
