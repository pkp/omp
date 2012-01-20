{**
 * templates/controllers/grid/settings/formats/form/formatForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Publication Format form.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#addPublicationFormatForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="addPublicationFormatForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.formats.PublicationFormatsGridHandler" op="updateFormat"}">
	<input type="hidden" name="formatId" value="{$formatId|escape}" />
	{fbvFormArea id="addFormat"}
		{fbvFormSection title="manager.setup.publicationFormat.name" for="title" required="true"}
			{fbvElement type="text" id="name" value=$name|escape multilingual="true" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.publicationFormat.code" for="entryKey" required="true" size=$fbvStyles.size.MEDIUM}
			{fbvElement type="select" from=$formatCodes selected=$entryKey id="entryKey" translate=false}
		{/fbvFormSection}
		{fbvFormSection for="enabled" size=$fbvStyles.size.MEDIUM list=true}

			{if $enabled}
				{assign var="checked" value=true}
			{else}
				{assign var="checked" value=false}
			{/if}

			{fbvElement type="checkbox" label="manager.setup.publicationFormat.enabled" checked=$checked id="enabled" list=true}
		{/fbvFormSection}
		{fbvFormSection for="physicalFormat" size=$fbvStyles.size.MEDIUM list=true}

			{if $physicalFormat}
				{assign var="checked" value=true}
			{else}
				{assign var="checked" value=false}
			{/if}

			{fbvElement type="checkbox" label="manager.setup.publicationFormat.physicalFormat" checked=$checked id="physicalFormat" list=true}
		{/fbvFormSection}
		{fbvFormButtons}
	{/fbvFormArea}
</form>