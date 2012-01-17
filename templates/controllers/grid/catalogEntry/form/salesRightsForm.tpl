{**
 * templates/controllers/grid/catalogEntry/form/salesRightsForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Sales Rights form.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#addSalesRightsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="addSalesRightsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.SalesRightsGridHandler" op="updateRights"}">
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="assignedPublicationFormatId" value="{$assignedPublicationFormatId|escape}" />
	<input type="hidden" name="salesRightsId" value="{$salesRightsId|escape}" />
	{fbvFormArea id="addRights"}
		{fbvFormSection title="grid.catalogEntry.salesRightsType" for="type" required="true"}
			{fbvElement type="select" from=$salesRights selected=$type id="type" translate=false}
		{/fbvFormSection}
		{fbvFormSection for="value" list="true" description="grid.catalogEntry.salesRightsROW.tip"}
		
			{if $ROWSetting}
				{assign var="checked" value=true}
			{else}
				{assign var="checked" value=false}
			{/if}

			{fbvElement type="checkbox" id="ROWSetting" checked=$checked list="true" label="grid.catalogEntry.salesRightsROW"}
		{/fbvFormSection}
		
		{fbvFormSection title="grid.catalogEntry.countries" for="countriesIncludedCode"}
			{fbvElement type="select" label="grid.catalogEntry.salesRightsIncluded" from=$countryCodes selected=$countriesIncluded translate=false id="countriesIncluded" name="countriesIncluded[]" multiple="multiple" size=$fbvStyles.size.MEDIUM defaultValue="" defaultLabel="" inline=true}
			{fbvElement type="select" label="grid.catalogEntry.salesRightsExcluded" from=$countryCodes selected=$countriesExcluded translate=false id="countriesExcluded" name="countriesExcluded[]" multiple="multiple" size=$fbvStyles.size.MEDIUM defaultValue="" defaultLabel="" inline=true}
		{/fbvFormSection}

		{fbvFormSection title="grid.catalogEntry.regions" for="countriesIncludedCode"}
			{fbvElement type="select" label="grid.catalogEntry.salesRightsIncluded" from=$regionCodes selected=$regionsIncluded translate=false id="regionsIncluded" name="regionsIncluded[]" multiple="multiple" size=$fbvStyles.size.MEDIUM defaultValue="" defaultLabel="" inline=true}
			{fbvElement type="select" label="grid.catalogEntry.salesRightsExcluded" from=$regionCodes selected=$regionsExcluded translate=false id="regionsExcluded" name="regionsExcluded[]" multiple="multiple" size=$fbvStyles.size.MEDIUM defaultValue="" defaultLabel="" inline=true}
		{/fbvFormSection}

		{fbvFormButtons}
	{/fbvFormArea}
</form>