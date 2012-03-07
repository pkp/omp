{**
 * templates/controllers/grid/catalogEntry/form/formatForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Assigned Publication Format form.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#addPublicationFormatForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="addPublicationFormatForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.PublicationFormatGridHandler" op="updateFormat"}">
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="publicationFormatId" value="{$publicationFormatId|escape}" />
	{fbvFormArea id="addFormat"}
		{fbvFormSection title="grid.catalogEntry.publicationFormatTitle" for="title" required="true"}
			{fbvElement type="text" id="title" value=$title|escape multilingual="true" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection title="grid.catalogEntry.publicationFormatType" for="publicationFormat"  required="true" size=$fbvStyles.size.MEDIUM}
			{fbvElement type="select" from=$entryKeys selected=$entryKey id="entryKey" translate=false}
		{/fbvFormSection}
		{fbvFormSection for="publicationFormat" size=$fbvStyles.size.MEDIUM list=true}
			{if $isPhysicalFormat}{assign var="checked" value=true}{else}{assign var="checked" value=false}{/if}
			{fbvElement type="checkbox" label="grid.catalogEntry.isPhysicalFormat" id="isPhysicalFormat" checked=$checked}
		{/fbvFormSection}
		{fbvFormButtons}
	{/fbvFormArea}
</form>
