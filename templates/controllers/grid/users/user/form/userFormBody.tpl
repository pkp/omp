{**
 * userFormBody.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form body for creating/editing a user.
 *}
<div id="userFormContainer">

<div id="userDetails" class="full left">

{if $userId}
<h3>{translate key="grid.user.userDetails"}</h3>
{else}
<h3>{translate key="grid.user.step1"}</h3>
{/if}

<form id="userForm" method="post" action="{url op="updateUser"}" onsubmit="enablePasswordFields()">

{if $userId}
	<input type="hidden" id="userId" name="userId" value="{$userId|escape}" />
{/if}

{include file="common/formErrors.tpl"}

{if count($formLocales) > 1}
{fbvFormArea id="locales"}
	{fbvFormSection title="form.formLanguage" for="languageSelector"}
		{fbvCustomElement}
			{if $userId}
				{url|assign:"userFormUrl" router=$smarty.const.ROUTE_COMPONENT component="grid.users.user.UserGridHandler" op="editUser" path=$userId escape=false}
			{else}
				{url|assign:"userFormUrl" router=$smarty.const.ROUTE_COMPONENT component="grid.users.user.UserGridHandler" op="addUser" escape=false}
			{/if}
			{form_language_chooser form="userForm" url=$userFormUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		{/fbvCustomElement}
	{/fbvFormSection}
{/fbvFormArea}
{/if} {* count($formLocales) > 1 *}


<div id="userFormCompactLeftContainer" class="half left">
{fbvFormArea id="userFormCompactLeft"}

{fbvFormSection title="user.firstName" required="true" for="firstName"}
	{fbvTextInput name="firstName" id="firstName" value=$firstName maxlength="40"}
{/fbvFormSection}

{fbvFormSection title="user.middleName" for="middleName"}
	{fbvTextInput name="middleName" id="middleName" value=$middleName maxlength="40"}
{/fbvFormSection}

{fbvFormSection title="user.lastName" required="true" for="lastName"}
	{fbvTextInput name="lastName" id="lastName" value=$lastName maxlength="90"}
{/fbvFormSection}

{fbvFormSection title="user.email" required="true" for="email"}
	{fbvTextInput name="email" id="email" value=$email maxlength="90"}
{/fbvFormSection}

{if !$implicitAuth && !$userId}
	{fbvFormSection title="grid.user.notifyUser" for="sendNotify"}
		{if $sendNotify}
			{assign var="checked" value="1"}
		{else}
			{assign var="checked" value=""}
		{/if}
		{fbvCheckbox name="sendNotify" id="sendNotify" checked=$checked label="grid.user.notifyUserDescription" translate="true"}
	{/fbvFormSection}
{/if} {* !$implicitAuth && !$userId *}

{if $authSourceOptions}
	{fbvFormSection title="grid.user.authSource" for="authId"}
		{fbvSelect name="authId" id="authId" defaultLabel="" defaultValue="" from=$authSourceOptions translate="true" selected=$authId}
	{/fbvFormSection}
{/if}
{/fbvFormArea}
</div>

<div id="userFormCompactRightContainer" class="half right">
{fbvFormArea id="userFormCompactRight"}
{if !$userId}
	{fbvFormSection title="user.username" required="true" for="username"}
		{fbvTextInput name="username" id="username" value=$username maxlength="32"}
		&nbsp;&nbsp;{fbvButton label="common.suggest" class="default" onclick="generateUsername()"}
		<br />
		<span class="instruct">{translate key="user.register.usernameRestriction"}</span>
	{/fbvFormSection}
{else}
	{fbvFormSection title="user.username" suppressId="true"}
	{$username|escape}
	{/fbvFormSection}
{/if}

{if !$implicitAuth}
	{fbvFormSection title="user.password" required=$passwordRequired for="password"}
		{fbvTextInput name="password" id="password" password="true" value=$password maxlength="32"}
		<br />
		<span class="instruct">{translate key="user.register.passwordLengthRestriction" length=$minPasswordLength}</span>
	{/fbvFormSection}

	{fbvFormSection title="user.repeatPassword" required=$passwordRequired for="password2"}
		{fbvTextInput name="password2" id="password2" password="true" value=$password2 maxlength="32"}
	{/fbvFormSection}

	{if $userId}
		{fbvFormSection suppressId="true"}
			{translate key="user.register.passwordLengthRestriction" length=$minPasswordLength}
			<br />
			{translate key="user.profile.leavePasswordBlank"}
		{/fbvFormSection}
	{else}
		{fbvFormSection title="grid.user.generatePassword" for="generatePassword"}
			{if $generatePassword}
				{assign var="checked" value="1"}
			{else}
				{assign var="checked" value=""}
			{/if}
			{fbvCheckbox name="generatePassword" id="generatePassword" checked=$checked onclick="setGenerateRandom(this.checked)" label="grid.user.generatePasswordDescription" translate="true"}
		{/fbvFormSection}
	{/if}{* $userId *}
	{fbvFormSection title="grid.user.mustChangePassword" for="mustChangePassword"}
		{if $mustChangePassword}
			{assign var="checked" value="1"}
		{else}
			{assign var="checked" value=""}
		{/if}
		{fbvCheckbox name="mustChangePassword" id="mustChangePassword" checked=$checked label="grid.user.mustChangePasswordDescription" translate="true"}
	{/fbvFormSection}
{/if}{* !$implicitAuth *}
{/fbvFormArea}
</div>

<div class="left full toggleDetailContainer">
	<ul>
		<li id="toggleFormMore" style="display: none;">
			<a id="toggleMore" href="#"><span class="toggleDetail moreDetail"></span>{translate key="grid.user.moreDetails"}</a>
		</li>
		<li id="toggleFormLess" style="display: none;">
			<a id="toggleLess" href="#"><span class="toggleDetail lessDetail"></span>{translate key="grid.user.lessDetails"}</a>
		</li>
	</ul>
</div>

<div id="userFormExtendedContainer" class="full left">
{fbvFormArea id="userFormExtended"}

{fbvFormSection title="user.salutation" for="salutation"}
	{fbvTextInput name="salutation" id="salutation" value=$salutation maxlength="40"}
{/fbvFormSection}

{fbvFormSection title="user.initials" for="initials"}
	{fbvTextInput name="initials" id="initials" value=$initials maxlength="5"}
	<br />
	<span class="instruct">{translate key="user.initialsExample"}</span>
{/fbvFormSection}

{fbvFormSection title="user.gender" for="gender" suppressId="true"}
	{fbvSelect name="gender" id="gender" defaultLabel="" defaultValue="" from=$genderOptions translate="true" selected=$gender}
{/fbvFormSection}

{fbvFormSection title="user.affiliation" for="affiliation"}
	{fbvTextArea name="affiliation[$formLocale]" id="affiliation" value=$affiliation[$formLocale] size=$fbvStyles.size.SMALL}
	<br />
	<span class="instruct">{translate key="user.affiliation.description"}</span>
{/fbvFormSection}

{fbvFormSection title="user.signature" for="signature"}
	{fbvTextArea name="signature[$formLocale]" id="signature" value=$signature[$formLocale] size=$fbvStyles.size.SMALL}
{/fbvFormSection}

{fbvFormSection title="user.url" for="userUrl"}
	{fbvTextInput name="userUrl" id="userUrl" value=$userUrl maxlength="90" size=$fbvStyles.size.MEDIUM}
{/fbvFormSection}

{fbvFormSection title="user.phone" for="phone" float=$fbvStyles.float.LEFT}
	{fbvTextInput name="phone" id="phone" value=$phone maxlength="24"}
{/fbvFormSection}

{fbvFormSection title="user.fax" for="fax" float=$fbvStyles.float.RIGHT}
	{fbvTextInput name="fax" id="fax" value=$fax maxlength="24"}
{/fbvFormSection}

{fbvFormSection title="user.interests" for="interests"}
	<ul id="interests"><li></li></ul><span class="interestDescription">{fieldLabel for="interests" key="user.interests.description"}</span><br />
	<textarea name="interests" id="interestsTextOnly" class="textArea small">
		{foreach name=currentInterests from=$interestsKeywords item=interest}
			{$interest|escape}
			{if !$smarty.foreach.currentInterests.last}, {/if}
		{/foreach}
	</textarea>
{/fbvFormSection}

{fbvFormSection title="common.mailingAddress" for="mailingAddress"}
	{fbvTextArea name="mailingAddress" id="mailingAddress" value=$mailingAddress size=$fbvStyles.size.SMALL}
{/fbvFormSection}

{fbvFormSection title="common.country" for="country"}
	{fbvSelect name="country" id="country" defaultLabel="" defaultValue="" from=$countries selected=$country translate="0"}
{/fbvFormSection}

{fbvFormSection title="user.biography" for="biography"}
	{fbvTextArea name="biography[$formLocale]" id="biography" value=$biography[$formLocale] size=$fbvStyles.size.SMALL}
	<br />
	<span class="instruct">{translate key="user.biography.description"}</span>
{/fbvFormSection}

{if count($availableLocales) > 1}
	{fbvFormSection title="user.workingLanguages"}
		{foreach from=$availableLocales key=localeKey item=localeName}
			{if $userLocales && in_array($localeKey, $userLocales)}
				{assign var="checked" value="true"}
			{else}
				{assign var="checked" value="false"}
			{/if}
			{fbvCheckbox name="userLocales[]" id="userLocales-$localeKey" value="$localeKey" checked=$checked label="$localeName" }
		{/foreach}
	{/fbvFormSection}
{/if}

{fbvFormSection title="user.gossip" for="gossip"}
	{fbvTextArea name="gossip[$formLocale]" id="gossip" value=$gossip[$formLocale] size=$fbvStyles.size.SMALL}
{/fbvFormSection}

{/fbvFormArea}
</div>

</form>

</div>

{if $userId}
<div id="userRoles" class="full left">

<h3>{translate key="grid.user.userRoles"}</h3>

<div id="userRolesContainer" class="full left">
{url|assign:userRolesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.UserUserGroupListbuilderHandler" op="fetch" userId=$userId title="grid.user.addRoles" escape=false}
{load_url_in_div id="userRolesContainer" url=$userRolesUrl}
</div>
</div>
{/if}


{if $generatePassword}
{literal}
	<script type="text/javascript">
		<!--
		setGenerateRandom(1);
		// -->
	</script>
{/literal}
{/if}
</div>
