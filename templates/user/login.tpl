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

{if $error}
	<span class="pkp_controllers_form_error">{translate key="$error" reason=$reason}</span>
	<br />
	<br />
{/if}

{if $implicitAuth}
	<a id="implicitAuthLogin" href="{url page="login" op="implicitAuthLogin"}">Login</a>
{else}
	<form class="pkp_form" id="signinForm" method="post" action="{$loginUrl}">
{/if}

<input type="hidden" name="source" value="{$source|escape}" />

{if ! $implicitAuth}
	{fbvFormArea id="loginFields"}
		{fbvFormSection id="login" title="user.username" for="username"}
			{fbvElement type="text" id="username" value=$username|escape size="20" maxlength="32" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
        {fbvFormSection id="login2" title="user.password" for="password"}
            {fbvElement type="text" password="true" id="password" value=$password|escape size="20" maxlength="32" size=$fbvStyles.size.MEDIUM}
        {/fbvFormSection}
		{if $showRemember}
			{fbvFormSection list='true'}
				{fbvElement type="checkbox" label="user.login.rememberUsernameAndPassword" id="loginRemember" value=$remember}
			{/fbvFormSection}
		{/if}{* $showRemember *}
		{fbvElement id="submitButton" type="submit" label="user.login"}
	{/fbvFormArea}

	<p>
		{if !$hideRegisterLink}&#187; <a href="{url page="user" op=$registerOp}">{translate key=$registerLocaleKey}</a><br />{/if}
		&#187; <a href="{url page="login" op="lostPassword"}">{translate key="user.login.forgotPassword"}</a>
	</p>
{/if}{* !$implicitAuth *}

<script type="text/javascript">
	{if $username}$("#password").focus();
	{else}$("#username").focus();{/if}
</script>
</form>

{include file="common/footer.tpl"}
