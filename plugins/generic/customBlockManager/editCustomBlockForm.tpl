{**
 * plugins/generic/customBlockManager/editCustomBlockForm.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form for editing a custom sidebar block
 *
 *}

<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#customBlockForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>
{url|assign:actionUrl router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.customBlockManager.controllers.grid.CustomBlockGridHandler" op="updateCustomBlock" existingBlockName=$blockName escape=false}
<form class="pkp_form" id="customBlockForm" method="post" action="{$actionUrl}">
{fbvFormArea id="cusotmBlocksFormArea" class="border"}
	{fbvFormSection}
		{if $blockName}
			{assign var="readonly" value=true}
		{/if}
		{fbvElement type="text" label="plugins.generic.customBlockManager.blockName" id="blockName" value=$blockName readonly=$readonly maxlength="40" inline=true size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}
	{fbvFormSection label="plugins.generic.customBlock.content" for="blockContent"}
		{fbvElement type="textarea" multilingual=true name="blockContent" id="blockContent" value=$blockContent rich=true height=$fbvStyles.height.TALL}
	{/fbvFormSection}
{/fbvFormArea}
{fbvFormButtons submitText="common.save"}
</form>

