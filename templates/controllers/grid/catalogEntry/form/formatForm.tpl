{**
 * templates/controllers/grid/catalogEntry/form/formatForm.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="representationId" value="{$representationId|escape}" />
	{fbvFormArea id="addFormat" class="border" title="grid.catalogEntry.publicationFormatDetails"}
		{fbvFormSection for="title"}
			{fbvElement type="text" required="true" id="name" label="common.name" value=$name multilingual="true" size=$fbvStyles.size.MEDIUM inline=true}
			{fbvElement type="select" label="grid.catalogEntry.publicationFormatType" from=$entryKeys selected=$entryKey id="entryKey" translate=false size=$fbvStyles.size.MEDIUM inline=true}
		{/fbvFormSection}
		{fbvFormSection for="publicationFormat" size=$fbvStyles.size.MEDIUM list=true}
			{fbvElement type="checkbox" label="grid.catalogEntry.physicalFormat" id="isPhysicalFormat" checked=$isPhysicalFormat}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
