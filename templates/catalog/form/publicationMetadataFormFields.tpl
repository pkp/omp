{**
 * catalog/form/publicationMetadataFormFields.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 *}
{* generate a unique form id since this form is used on several tabs *}
{capture assign=publicationFormId}publicationMetadataEntryForm-{$assignedPublicationFormatId}{/capture}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#{$publicationFormId|escape:"javascript"}').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="{$publicationFormId|escape}" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="saveForm"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="Notification$publicationFormId"}

	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="tabPos" value="{$formParams.tabPos|escape}" />
	<input type="hidden" name="assignedPublicationFormatId" value="{$assignedPublicationFormatId|escape}" />
	<input type="hidden" name="displayedInTab" value="{$formParams.displayedInTab|escape}" />
	<input type="hidden" name="tab" value="publication" />

	{fbvFormArea id="productIdentifier"}
		{fbvFormSection title="monograph.publicationFormat.productIdentifierType" for="productIdentifierTypeCode"}
			{fbvElement type="text"  name="productIdentifier" id="productIdentifier" value=$productIdentifier maxlength="255" size=$fbvStyles.size.SMALL disabled=$readOnly inline="true"}
			{fbvElement type="select" from=$productIdentifierTypeCodes selected=$productIdentifierTypeCode translate=0 id="productIdentifierTypeCode" inline="true"}
		{/fbvFormSection}
	{/fbvFormArea}
	
	{fbvFormArea id="productComposition"}
		{fbvFormSection title="monograph.productComposition" for="productCompositionCode"}
			{fbvElement type="select" from=$productCompositionCodes selected=$productCompositionCode translate=0 id="productCompositionCode" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea  title="monograph.publicationFormat.price" border="true"}
		{fbvFormSection for="price" desc="monograph.publicationFormat.pricingInformation"}
			{fbvElement type="text" name="price" id="price" value=$price maxlength="255" size=$fbvStyles.size.SMALL disabled=$readOnly inline="true"}
			{fbvElement type="select" from=$currencyCodes selected=$currencyCode translate=0 id="currencyCode" inline="true"}
			{fbvElement type="select" label="monograph.publicationFormat.priceType" from=$priceTypeCodes selected=$priceTypeCode translate=0 id="priceTypeCode" defaultValue="" defaultLabel=""}
			{fbvElement type="select" label="monograph.publicationFormat.taxRate" from=$taxRateCodes selected=$taxRateCode translate=0 id="taxRateCode" defaultValue="" defaultLabel="" inline="true" size=$fbvStyles.size.MEDIUM}
			{fbvElement type="select" label="monograph.publicationFormat.taxType" from=$taxTypeCodes selected=$taxTypeCode translate=0 id="taxTypeCode" defaultValue="" defaultLabel="" inline="true" size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
	{/fbvFormArea}

	{* publicationFormatId is sanitized in CatalogEntryPublicationMetadataForm *}
	{include file="catalog/form/publicationFormat"|concat:$formatId:".tpl"}

	{fbvFormButtons id="publicationMetadataFormSubmit" submitText="common.save"}
</form>

