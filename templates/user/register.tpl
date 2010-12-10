{**
 * register.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User registration form.
 *}
{strip}
{assign var="pageTitle" value="user.register"}
{include file="common/header.tpl"}
{/strip}

{literal}
<script type="text/javascript">
	<!--
	$(document).ready(function(){
		$("#interestsTextOnly").hide();
		$("#interests").tagit({
			{/literal}{if $existingInterests}{literal}
			// This is the list of interests in the system used to populate the autocomplete
			availableTags: [{/literal}{foreach name=existingInterests from=$existingInterests item=interest}"{$interest|escape|escape:'javascript'}"{if !$smarty.foreach.existingInterests.last}, {/if}{/foreach}{literal}],{/literal}{/if}
			// There are no current interests for the user since they're just registering; Assign an empty list
			currentTags: []
		});
	});
	// -->
</script>
{/literal}

<form name="register" method="post" action="{url op="registerUser"}">

<p>{translate key="user.register.completeForm"}</p>

{if !$implicitAuth}
	{if !$existingUser}
		{url|assign:"url" page="user" op="register" existingUser=1}
		<p>{translate key="user.register.alreadyRegisteredOtherPress" registerUrl=$url}</p>
	{else}
		{url|assign:"url" page="user" op="register"}
		<p>{translate key="user.register.notAlreadyRegisteredOtherPress" registerUrl=$url}</p>
		<input type="hidden" name="existingUser" value="1"/>
	{/if}

	<h3>{translate key="user.profile"}</h3>

	{if $existingUser}
		<p>{translate key="user.register.loginToRegister"}</p>
	{/if}
{/if}{* !$implicitAuth *}

{if $source}
	<input type="hidden" name="source" value="{$source|escape}" />
{/if}

{fbvFormArea id="registration"}
{if count($formLocales) > 1 && !$existingUser}
	{fbvFormSection title="form.formLanguage" for="languageSelector"}
		{fbvCustomElement}
			{url|assign:"registerFormUrl" op="register"}
			{form_language_chooser form="register" url=$registerFormUrl}
			<p>{translate key="form.formLanguage.description"}</p>
		{/fbvCustomElement}
	{/fbvFormSection}
{/if}{* count($formLocales) > 1 && !$existingUser *}

{if !$implicitAuth}
	{fbvFormSection title="user.accountInformation" required="true"}
		{fbvElement type="text" label="user.username" id="username" value=$username required="true"}
		{fbvElement type="text" label="user.password" id="password" value=$password required="true" password="true"}
		{if !$existingUser}
			{fbvElement type="text" label="user.repeatPassword" id="password2" value=$password2 required="true" password="true"}
		{/if}{* !$existingUser *}
	{/fbvFormSection}

  {if !$existingUser}
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

	{fbvFormSection title="user.phone" for="phone" float=$fbvStyles.float.LEFT}
		{fbvElement type="text" id="phone" value=$phone}
	{/fbvFormSection}

	{fbvFormSection title="user.fax" for="fax" float=$fbvStyles.float.RIGHT}
		{fbvElement type="text" id="fax" value=$fax}
	{/fbvFormSection}

	{fbvFormSection title="user.email" for="email" required="true" float=$fbvStyles.float.LEFT}
		{fbvElement type="text" id="email" value=$email size=$fbvStyles.size.LARGE} {if $privacyStatement}<a class="action" href="#privacyStatement">{translate key="user.register.privacyStatement"}</a>{/if}
	{/fbvFormSection}

	{fbvFormSection title="user.confirmEmail" for="confirmEmail" required="true" float=$fbvStyles.float.LEFT}
		{fbvElement type="text" id="confirmEmail" value=$confirmEmail size=$fbvStyles.size.LARGE}
	{/fbvFormSection}

	{fbvFormSection title="user.url" for="userUrl" float=$fbvStyles.float.RIGHT}
		{fbvElement type="text" id="userUrl" value=$userUrl size=$fbvStyles.size.LARGE}
	{/fbvFormSection}

	{fbvFormSection title="user.affiliation" for="affiliation" float=$fbvStyles.float.LEFT}
		{fbvElement type="textarea" id="affiliation" value=$affiliation size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}<br/>
		<span class="instruct">{translate key="user.affiliation.description"}</span>
	{/fbvFormSection}

	{fbvFormSection title="user.mailingAddress" for="mailingAddress" float=$fbvStyles.float.RIGHT}
		{fbvElement type="textarea" id="mailingAddress" value=$mailingAddress size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}

	{fbvFormSection title="user.biography" for="biography" float=$fbvStyles.float.LEFT}
		{fbvElement type="textarea" id="biography" name="biography[$formLocale]" value=$biography[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}

	{fbvFormSection title="user.signature" for="signature" float=$fbvStyles.float.RIGHT}
		{fbvElement type="textarea" id="signature" name="signature[$formLocale]" value=$signature[$formLocale] size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}

	{fbvFormSection title="common.country" for="country"}
		{fbvElement type="select" from=$countries selected=$country translate=0 id="country" defaultValue="" defaultLabel=""}
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

	{fbvFormSection title="user.sendPassword" layout=$fbvStyles.layout.ONE_COLUMN}
		{if $sendPassword}
			{fbvElement type="checkbox" id="sendPassword" value="1" label="user.sendPassword.description" checked="checked"}
		{else}
			{fbvElement type="checkbox" id="sendPassword" value="1" label="user.sendPassword.description"}
		{/if}
	{/fbvFormSection}


  {/if} {* !$existingUser *}
{/if}{* !$implicitAuth *}

  {if $allowRegReader || $allowRegReader === null || $allowRegAuthor || $allowRegAuthor === null || $allowRegReviewer || $allowRegReviewer === null}
	{fbvFormSection title="user.register.registerAs" layout=$fbvStyles.layout.ONE_COLUMN group="true"}
	{if $allowRegReader || $allowRegReader === null}
		{if $registerAsReader}
			{fbvElement type="checkbox" id="registerAsReader" value="1" label="user.register.readerDescription" checked="checked"}
		{else}
			{fbvElement type="checkbox" id="registerAsReader" value="1" label="user.register.readerDescription"}
		{/if}
	{/if}
	{if $allowRegAuthor || $allowRegAuthor === null}
		{if $registerAsAuthor}
			{fbvElement type="checkbox" id="registerAsAuthor" value="1" label="user.register.authorDescription" checked="checked"}
		{else}
			{fbvElement type="checkbox" id="registerAsAuthor" value="1" label="user.register.authorDescription"}
		{/if}
	{/if}
	{assign var="divEnded" value=0}
	{if $allowRegReviewer || $allowRegReviewer === null}
		{if $existingUser}
			{assign var="regReviewerLabel" value="user.register.reviewerDescriptionNoInterests"}
		{else}
			{assign var="regReviewerLabel" value="user.register.reviewerDescription"}
		{/if}

		{if $registerAsReviewer}
			{fbvElement type="checkbox" id="registerAsReviewer" value="1" label=$regReviewerLabel checked="checked"}
		{else}
			{fbvElement type="checkbox" id="registerAsReviewer" value="1" label=$regReviewerLabel}
		{/if}

	{/if}
	{/fbvFormSection}
	<div id="reviewerInterestsContainer" style="margin-left:40px;">
		<label class="desc">{translate key="user.register.reviewerInterests"}</label>
		<ul id="interests"><li></li></ul>
		<textarea name="interests" id="interestsTextOnly" rows="5" cols="40" class="textArea">{foreach name=currentInterests from=$interestsKeywords item=interest}{$interest|escape}{if !$smarty.foreach.currentInterests.last}, {/if}{/foreach}</textarea>
	</div>
	<br />
  {/if}
{if !$implicitAuth}
	{if !existingUser}
  {if $captchaEnabled}
  <li>
	{fieldLabel name="captcha" required="true" key="common.captchaField" class="desc"}
	<span>
		<img src="{url page="user" op="viewCaptcha" path=$captchaId}" alt="{translate key="common.captchaField.altText"}" /><br />
		<p>{translate key="common.captchaField.description"}</p>
		<input name="captcha" id="captcha" value="" size="20" maxlength="32" class="field text" />
		<input type="hidden" name="captchaId" value="{$captchaId|escape:"quoted"}" />
	</span>
  </li>
  {/if}{* $captchaEnabled *}

	{/if} {* !$existingUser *}
{/if}{* !$implicitAuth *}
{/fbvFormArea}
{url|assign:"url" page="index" escape=false}
<p>{fbvButton type="submit" label="user.register"} {fbvButton label="common.cancel" onclick="document.location.href='$url'"}</p>

{if ! $implicitAuth}
	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
{/if}{* !$implicitAuth *}

<div id="privacyStatement">
{if $privacyStatement}
	<h3>{translate key="user.register.privacyStatement"}</h3>
	<p>{$privacyStatement|nl2br}</p>
{/if}
</div>

</form>

{include file="common/footer.tpl"}

