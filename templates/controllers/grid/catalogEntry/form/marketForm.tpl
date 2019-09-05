{**
 * templates/controllers/grid/catalogEntry/form/marketForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Sales Rights form.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#marketForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="marketForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.MarketsGridHandler" op="updateMarket"}">
	{csrf}
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="publicationId" value="{$publicationId|escape}" />
	<input type="hidden" name="representationId" value="{$representationId|escape}" />
	<input type="hidden" name="marketId" value="{$marketId|escape}" />

	<!-- Collect a Date for this Market (availability, stock, re-issue, etc) -->
	{fbvFormArea id="dateArea" class="border"}
		{fbvFormSection title="grid.catalogEntry.dateValue" for="date" required="true"}
			{fbvElement type="text" id="date" value=$date size=$fbvStyles.size.SMALL inline="true" required="true"}
			{fbvElement type="select" label="grid.catalogEntry.dateFormat" from=$publicationDateFormats selected=$dateFormat id="dateFormat" translate=false size=$fbvStyles.size.SMALL inline="true"}
			{fbvElement type="select" label="grid.catalogEntry.dateRole" from=$publicationDateRoles selected=$dateRole id="dateRole" translate=false inline="true" size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
	{/fbvFormArea}

	<!-- Agent assignment for this Territory -->
	{fbvFormArea id="agentArea" class="border"}
		{fbvFormSection for="assignedAgent" description="grid.catalogEntry.agentTip"}
			{fbvElement type="select" label="grid.catalogEntry.agent" from=$availableAgents selected=$agentId size=$fbvStyles.size.MEDIUM id="agentId" translate=false inline="true" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
	{/fbvFormArea}

	<!-- Supplier assignment for this Territory -->
	{fbvFormArea id="supplierArea" class="border"}
		{fbvFormSection for="assignedSupplier"}
			{fbvElement type="select" label="grid.catalogEntry.supplier" from=$availableSuppliers selected=$supplierId size=$fbvStyles.size.MEDIUM id="supplierId" translate=false inline="true" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="marketArea"}
		{include file="controllers/grid/catalogEntry/form/countriesAndRegions.tpl"}
	{/fbvFormArea}

	{fbvFormArea id="pricing" class="border"}
		{fbvFormSection for="price" desc="monograph.publicationFormat.pricingInformation" title="monograph.publicationFormat.price" required="true"}
			{fbvElement type="text" name="price" id="price" value=$price maxlength="255" size=$fbvStyles.size.SMALL inline="true" required="true"}
			{fbvElement type="select" from=$currencyCodes selected=$currencyCode translate=false id="currencyCode" size=$fbvStyles.size.SMALL inline="true"}
			{fbvElement type="select" label="monograph.publicationFormat.priceType" from=$priceTypeCodes selected=$priceTypeCode translate=false id="priceTypeCode" defaultValue="" defaultLabel="" inline="true" size=$fbvStyles.size.SMALL}
			{fbvElement type="select" label="monograph.publicationFormat.taxRate" from=$taxRateCodes selected=$taxRateCode translate=false id="taxRateCode" defaultValue="" defaultLabel="" inline="true" size=$fbvStyles.size.SMALL}
			{fbvElement type="select" label="monograph.publicationFormat.taxType" from=$taxTypeCodes selected=$taxTypeCode translate=false id="taxTypeCode" defaultValue="" defaultLabel="" inline="true" size=$fbvStyles.size.SMALL}
			{fbvElement type="text" label="monograph.publicationFormat.discountAmount" name="discount" id="discount" value=$discount maxlength="255" size=$fbvStyles.size.SMALL inline="true"}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
