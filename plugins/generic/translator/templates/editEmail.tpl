{**
 * templates/editEmail.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Email editor dialog
 *}
{assign var=saveFormId value="saveLocaleFile"|uniqid}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#{$saveFormId}').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>
<form id="{$saveFormId}" action="{url op="save" locale=$locale emailKey=$emailKey}" method="post" class="pkp_form">
	{* Reference area *}
	{fbvFormArea id="referenceArea-"|uniqid title="plugins.generic.translator.file.reference" class="border"}
		{fbvFormSection}
			{fbvElement type="text" id="referenceSubject" readonly=true value=$referenceSubject label="common.subject" size=$fbvStyles.size.LARGE}
			{fbvElement type="textarea" id="referenceBody" readonly=true value=$referenceBody label="email.body"}
		{/fbvFormSection}
	{/fbvFormArea}

	{* Content area *}
	{fbvFormArea id="contentArea-"|uniqid title="plugins.generic.translator.file.translation" class="border"}
		{fbvFormSection}
			{fbvElement type="text" id="emailSubject" value=$emailSubject label="common.subject" size=$fbvStyles.size.LARGE}
			{fbvElement type="textarea" id="emailBody" value=$emailBody label="email.body"}
		{/fbvFormSection}
	{/fbvFormArea}

	{* Form buttons *}
	{fbvElement type="submit" class="submitFormButton" id="submitFormButton-"|uniqid label="common.save"}
</form>
