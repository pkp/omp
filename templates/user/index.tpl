{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User index.
 *}
{strip}
{assign var="pageTitle" value="user.userHome"}
{include file="common/header.tpl"}
{/strip}

<h3>{translate key="user.myAccount"}</h3>
<ul class="plain">
	{if $hasOtherPresses}
		{if !$showAllPresses}
			<li>&#187; <a href="{url press="index" page="user"}">{translate key="user.showAllPresses"}</a></li>
		{/if}
	{/if}
	<li>&#187; <a href="{url page="user" op="profile"}">{translate key="user.editMyProfile"}</a></li>

	{if !$implicitAuth}
		<li>&#187; <a href="{url page="user" op="changePassword"}">{translate key="user.changeMyPassword"}</a></li>
	{/if}

	<li>&#187; <a href="{url page="login" op="signOut"}">{translate key="user.logOut"}</a></li>
	{call_hook name="Templates::User::Index::MyAccount"}
</ul>

{include file="common/footer.tpl"}

