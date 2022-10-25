{**
 * templates/controllers/grid/catalogEntry/form/salesRightsForm.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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
	{csrf}
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="publicationId" value="{$publicationId|escape}" />
	<input type="hidden" name="representationId" value="{$representationId|escape}" />
	<input type="hidden" name="salesRightsId" value="{$salesRightsId|default:""|escape}" />
	{fbvFormArea id="addRights"}
		{fbvFormSection title="grid.catalogEntry.salesRightsType" for="type" required="true"}
			{fbvElement type="select" from=$salesRights selected=$type id="type" translate=false required="true"}
		{/fbvFormSection}
		{fbvFormSection for="value" list="true" description="grid.catalogEntry.salesRightsROW.tip"}

			{if $ROWSetting}
				{assign var="checked" value=true}
			{else}
				{assign var="checked" value=false}
			{/if}

			{fbvElement type="checkbox" id="ROWSetting" checked=$checked list="true" label="grid.catalogEntry.salesRightsROW"}
		{/fbvFormSection}

		{include file="controllers/grid/catalogEntry/form/countriesAndRegions.tpl"}

		{fbvFormButtons}
	{/fbvFormArea}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
