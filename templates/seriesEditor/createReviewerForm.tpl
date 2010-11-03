{**
 * createReviewerForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form for editors to create reviewers.
 *}
{strip}
{assign var="pageTitle" value="seriesEditor.review.createReviewer"}
{include file="common/header.tpl"}
{/strip}

{literal}
<script type="text/javascript">
	$(document).ready(function(){
		$("#interestsTextOnly").hide();
		$("#interests").tagit({
			availableTags: [{/literal}{$existingInterests}{literal}]
			{/literal}{if $existingInterests}{literal} availableTags: [{/literal}{foreach name=existingInterests from=$existingInterests item=interest}"{$interest|escape|escape:"javascript"}"{if !$smarty.foreach.existingInterests.last}, {/if}{/foreach}{literal}],{/literal}{/if}
		});
	});
</script>
{/literal}

<form method="post" name="reviewerForm" action="{url op="createReviewer" path=$monographId|to_array:"create"}">

{include file="common/formErrors.tpl"}

<script type="text/javascript">
{literal}
// <!--

	function generateUsername() {
		var req = makeAsyncRequest();

		if (document.reviewerForm.lastName.value == "") {
			alert("{/literal}{translate key="manager.people.mustProvideName"}{literal}");
			return;
		}

		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				document.reviewerForm.username.value = req.responseText;
			}
		}
		sendAsyncRequest(req, '{/literal}{url op="suggestUsername" firstName="REPLACE1" lastName="REPLACE2" escape=false}{literal}'.replace('REPLACE1', escape(document.reviewerForm.firstName.value)).replace('REPLACE2', escape(document.reviewerForm.lastName.value)), null, 'get');
	}


// -->
{/literal}
</script>

{fbvFormArea id="createReviewerForm"}
{if count($formLocales) > 1}
	{fbvFormSection title="form.formLanguage" for="languageSelector"}
		{fbvCustomElement}
			{url|assign:"setupFormUrl" op="setup" path="1"}
			{form_language_chooser form="setupForm" url=$setupFormUrl}
			<p>{translate key="form.formLanguage.description"}</p>
		{/fbvCustomElement}
	{/fbvFormSection}
{/if}{* count($formLocales) > 1 *}

	{fbvFormSection title="common.name" required="true"}
		{fbvElement type="text" label="user.salutation" id="salutation" value=$salutation size=$fbvStyles.size.SMALL}
		{fbvElement type="text" label="user.firstName" id="firstName" value=$firstName required="true"}
		{fbvElement type="text" label="user.middleName" id="middleName" value=$middleName}
		{fbvElement type="text" label="user.lastName" id="lastName" value=$lastName required="true"}
		{fbvElement type="text" label="user.initials" id="initials" value=$initials size=$fbvStyles.size.SMALL}
	{/fbvFormSection}

	{fbvFormSection title="user.gender" for="gender" float=$fbvStyles.positions.LEFT}
		{fbvElement type="select" from=$genderOptions selected=$gender id="gender" translate="true"}
	{/fbvFormSection}

	{fbvFormSection title="user.accountInformation" required="true"}
		{fbvElement type="text" label="user.username" id="username" value=$username required="true"} {fbvButton value="common.suggest" onclick="generateUsername()"}
	{/fbvFormSection}

	{fbvFormSection title="user.sendPassword" for="sendNotify"}
			{fbvElement type="checkbox" id="sendNotify" value="1" label="manager.people.createUserSendNotify" checked=$sendNotify}
	{/fbvFormSection}

	{fbvFormSection title="user.affiliation" for="affiliation" float=$fbvStyles.float.LEFT}
		{fbvElement type="textarea" id="affiliation" value=$affiliation size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}

	{fbvFormSection title="user.mailingAddress" for="mailingAddress" float=$fbvStyles.float.RIGHT}
		{fbvElement type="textarea" id="mailingAddress" value=$mailingAddress size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}

	{fbvFormSection title="user.email" for="email" required="true" float=$fbvStyles.float.LEFT}
		{fbvElement type="text" id="email" value=$email size=$fbvStyles.size.LARGE} {if $privacyStatement}<a class="action" href="#privacyStatement">{translate key="user.register.privacyStatement"}</a>{/if}
	{/fbvFormSection}

	{fbvFormSection title="user.url" for="userUrl" float=$fbvStyles.float.RIGHT}
		{fbvElement type="text" id="userUrl" value=$userUrl size=$fbvStyles.size.LARGE}
	{/fbvFormSection}

	{fbvFormSection title="user.phone" for="phone" float=$fbvStyles.float.LEFT}
		{fbvElement type="text" id="phone" value=$phone}
	{/fbvFormSection}

	{fbvFormSection title="user.fax" for="fax" float=$fbvStyles.float.RIGHT}
		{fbvElement type="text" id="fax" value=$fax}
	{/fbvFormSection}

	{fbvFormSection title="user.interests" for="interests"}
		<ul id="interests"></ul><br /><textarea name="interests" id="interestsTextOnly" rows="5" cols="40" class="textArea">{foreach name=currentInterests from=$interestsKeywords item=interest}{$interest|urldecode}{if !$smarty.foreach.currentInterests.last}, {/if}{/foreach}</textarea>
	{/fbvFormSection}

	{fbvFormSection title="common.country" for="country"}
		{fbvElement type="select" from=$countries selected=$country translate=0 id="country" defaultValue="" defaultLabel=""}
	{/fbvFormSection}

	{fbvFormSection title="user.biography" for="biography" float=$fbvStyles.float.LEFT}
		{fbvElement type="textarea" id="biography" name="biography[$formLocale]" value=$biography[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}

	{if count($availableLocales) > 1}
	{fbvFormSection title="user.workingLanguages" layout=$fbvStyles.layout.THREE_COLUMNS group="true"}
		{foreach from=$availableLocales key=localeKey item=localeName}
			{assign var="controlId" value=userLocales-$localeKey}
			{if in_array($localeKey, $userLocales)}
				{fbvElement type="checkbox" name="userLocales[]" id=$controlId value="1" label=$localeName translate="false" checked="checked"}
			{else}
				{fbvElement type="checkbox" name="userLocales[]" id=$controlId value="1" label=$localeName translate="false"}
			{/if}
		{/foreach}
	{/fbvFormSection}
	{/if}{* count($availableLocales) > 1 *}

{/fbvFormArea}

{url|assign:"url" op="selectReviewer" path=$monographId escape=false}
<p>{fbvButton type="submit" label="common.save"} {fbvButton label="common.cancel" onclick="document.location.href='$url'"}</p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{include file="common/footer.tpl"}

