{**
 * login.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User login form.
 *}

{assign var="helpTopicId" value="user.registerAndProfile"}
{assign var="registerOp" value="register"}
{assign var="registerLocaleKey" value="user.login.registerNewAccount"}

{strip}
{assign var="pageTitle" value="user.login"}
{include file="common/header.tpl"}
{/strip}

{if !$registerOp}
	{assign var="registerOp" value="register"}
{/if}
{if !$registerLocaleKey}
	{assign var="registerLocaleKey" value="user.login.registerNewAccount"}
{/if}

{if $loginMessage}
	<span class="instruct">{translate key="$loginMessage"}</span>
	<br />
	<br />
{/if}


{if $implicitAuth}
	<a id="implicitAuthLogin" href="{url page="login" op="implicitAuthLogin"}">Login</a>
{else}
	<script type="text/javascript">
		$(function() {ldelim}
			// Attach the form handler.
			$('#signinForm').pkpHandler('$.pkp.controllers.form.FormHandler');
		{rdelim});
	</script>

	<form class="pkp_form" id="signinForm" method="post" action="{$loginUrl}">
{/if}


{if $error}
	<span class="pkp_form_error">{translate key="$error" reason=$reason}</span>
	<br />
	<br />
{/if}

<input type="hidden" name="source" value="{$source|escape}" />

{if ! $implicitAuth}
	{fbvFormArea id="loginFields"}
		{fbvFormSection label="user.login" for="username"}
			{fbvElement type="text" id="username" value=$username|escape maxlength="32" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection label="user.password" for="password"}
			{fbvElement type="text" password=true id="password" value=$password|escape maxlength="32" size=$fbvStyles.size.MEDIUM}
			<a href="{url page="login" op="lostPassword"}">{translate key="user.login.forgotPassword"}</a>
		{/fbvFormSection}
		{if $showRemember}
			{fbvFormSection list=true}
				{fbvElement type="checkbox" label="user.login.rememberUsernameAndPassword" id="loginRemember" value=$remember}
			{/fbvFormSection}
		{/if}{* $showRemember *}
		{if !$hideRegisterLink}
			{url|assign:cancelUrl page="user" op=$registerOp}
			{fbvFormButtons cancelUrl=$cancelUrl cancelText=$registerLocaleKey submitText="user.login"}
		{else}
			{fbvFormButtons hideCancel=true submitText="user.login.resetPassword"}
		{/if}
	{/fbvFormArea}

{/if}{* !$implicitAuth *}

<script type="text/javascript">
	{if $username}$("#password").focus();
	{else}$("#username").focus();{/if}
</script>
</form>

{include file="common/footer.tpl"}
