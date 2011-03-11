{**
 * templates/controllers/modals/preparedEmails/form/emailTemplateForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to edit or create a prepared email
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#managePreparedEmailForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<form method="post" id="managePreparedEmailForm" action="{url op="updatePreparedEmail"}">
	{include file="common/formErrors.tpl"}

	{if $isNewTemplate}
		{fbvFormArea id="emailTemplateData"}
			<h3>{translate key="manager.emails.data"}</h3>
			{fbvFormSection title="common.name" required="true" for="emailKey"}
				{fbvTextInput name="emailKey" id="emailKey" maxlength="120"}
			{/fbvFormSection}
		{/fbvFormArea}
	{else}
		{fbvFormArea id="emailTemplateData"}
			<h3>{translate key="manager.emails.data"}</h3>
			{if $description}
				{fbvFormSection title="common.description"}
					<p>{$description|escape}</p>
				{/fbvFormSection}
			{/if}

			{fbvFormSection title="manager.emails.emailKey" for="emailKey"}
				{fbvTextInput name="emailKey" value=$emailKey id="emailKey" disabled=true}
				<input type="hidden" name="emailKey" value="{$emailKey|escape}" />
			{/fbvFormSection}
		{/fbvFormArea}
	{/if}

	{foreach from=$supportedLocales item=localeName key=localeKey}
		{fbvFormArea id="emailTemplateDetails"}
			<h3>{translate key="manager.emails.details"}</h3>
			{fbvFormSection title="email.subject" required="true" for="subject-$localeKey"}
				{fbvTextInput name="subject[$localeKey]" id="subject-$localeKey" value=$subject[$localeKey] maxlength="120"}
			{/fbvFormSection}

			{fbvFormSection title="email.body" required="true" for="body-$localeKey"}
				{fbvTextArea name="body[$localeKey]" id="body-$localeKey" value=$body[$localeKey] size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
		{/fbvFormArea}
	{/foreach}

	{include file="form/formButtons.tpl" submitText="common.save"}
</form>
