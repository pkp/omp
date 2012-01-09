{**
 * catalog/form/publicationMetadataFormFields.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
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
		{fbvFormSection}
			<!--  Product Identification Codes -->
			{assign var="divId" value="identificationCodeGridContainer"|concat:$assignedPublicationFormatId|escape}
			{url|assign:identGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.catalogEntry.IdentificationCodeGridHandler" op="fetchGrid" monographId=$monographId assignedPublicationFormatId=$assignedPublicationFormatId escape=false}
			{load_url_in_div id="$divId" url="$identGridUrl"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="productComposition" title="monograph.publicationFormat.productComposition" border="true"}
		{fbvFormSection for="productCompositionCode"}
			{fbvElement type="select" from=$productCompositionCodes selected=$productCompositionCode translate=false id="productCompositionCode" defaultValue="" defaultLabel="" inline=true}
			{fbvElement type="select" from=$productFormCodes selected=$productFormCode translate=false id="productFormCode" inline=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea  title="monograph.publicationFormat.price" border="true"}
		{fbvFormSection for="price" desc="monograph.publicationFormat.pricingInformation"}
			{fbvElement type="text" name="price" id="price" value=$price maxlength="255" size=$fbvStyles.size.SMALL disabled=$readOnly inline="true"}
			{fbvElement type="select" from=$currencyCodes selected=$currencyCode translate=false id="currencyCode" inline="true"}
			{fbvElement type="select" label="monograph.publicationFormat.priceType" from=$priceTypeCodes selected=$priceTypeCode translate=false id="priceTypeCode" defaultValue="" defaultLabel=""}
			{fbvElement type="select" label="monograph.publicationFormat.taxRate" from=$taxRateCodes selected=$taxRateCode translate=false id="taxRateCode" defaultValue="" defaultLabel="" inline="true" size=$fbvStyles.size.MEDIUM}
			{fbvElement type="select" label="monograph.publicationFormat.taxType" from=$taxTypeCodes selected=$taxTypeCode translate=false id="taxTypeCode" defaultValue="" defaultLabel="" inline="true" size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="countriesIncluded"}
		{fbvFormSection title="monograph.publicationFormat.productRegion" for="countriesIncludedCode"}
			{fbvElement type="select" from=$countriesIncludedCodes selected=$countriesIncludedCode translate=false id="countriesIncludedCode" name="countriesIncludedCode[]" multiple="multiple" inline=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{if $formatId eq "1"}
		{include file="catalog/form/publicationFormat1.tpl"}
	{elseif $formatId eq "2"}
		{include file="catalog/form/publicationFormat2.tpl"}
	{elseif $format eq "3"}
		{include file="catalog/form/publicationFormat3.tpl"}
	{else}
		{* noop - space for more formats *}
	{/if}

	{fbvFormButtons id="publicationMetadataFormSubmit" submitText="common.save"}
</form>

