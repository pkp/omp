{**
 * enrollment.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List enrolled users.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="manager.people.enrollment"}
{include file="common/header.tpl"}
{/strip}

<form name="disableUser" method="post" action="{url op="disableUser"}">
	<input type="hidden" name="reason" value=""/>
	<input type="hidden" name="userId" value=""/>
</form>

<script type="text/javascript">
{literal}
<!--
function toggleChecked() {
	var elements = document.people.elements;
	for (var i=0; i < elements.length; i++) {
		if (elements[i].name == 'bcc[]') {
			elements[i].checked = !elements[i].checked;
		}
	}
}

function confirmAndPrompt(userId) {
	var reason = prompt('{/literal}{translate|escape:"javascript" key="manager.people.confirmDisable"}{literal}');
	if (reason == null) return;

	document.disableUser.reason.value = reason;
	document.disableUser.userId.value = userId;

	document.disableUser.submit();
}
// -->
{/literal}
</script>

{if $contextRole && $contextRole->isCustomRole()}
	{assign var="isCustomRole" value=1}
{else}
	{assign var="isCustomRole" value=0}
{/if}
<h3>{$roleName|escape}</h3>
<form method="post" action="{url path=$roleSymbolic}">
	<select name="roleSymbolic" class="selectMenu">
		<option {if $roleSymbolic=='all'}selected="selected" {/if}value="all">{translate key="manager.people.allUsers"}</option>
	{foreach from=$roles item=role}
		<option {if ($roleSymbolic==$role->getPath() && !$isCustomRole) || ($isCustomRole && $contextRole->getId()==$role->getId())}selected="selected" {/if}value="{if $role->isCustomRole()}{$role->getId()|escape}{else}{$role->getPath()|escape}{/if}">{$role->getLocalizedPluralName()|escape}</option>
	{/foreach}
	</select>
	<select name="searchField" size="1" class="selectMenu">
		{html_options_translate options=$fieldOptions selected=$searchField}
	</select>
	<select name="searchMatch" size="1" class="selectMenu">
		<option value="contains"{if $searchMatch == 'contains'} selected="selected"{/if}>{translate key="form.contains"}</option>
		<option value="is"{if $searchMatch == 'is'} selected="selected"{/if}>{translate key="form.is"}</option>
	</select>
	<input type="text" size="10" name="search" class="textField" value="{$search|escape}" />&nbsp;<input type="submit" value="{translate key="common.search"}" class="button" />
</form>

<p>{foreach from=$alphaList item=letter}<a href="{if $isCustomRole}{url path=$roleSymbolic searchInitial=$letter customRoleId=$contextRole->getId()}{else}{url path=$roleSymbolic searchInitial=$letter}{/if}">{if $letter == $searchInitial}<strong>{$letter|escape}</strong>{else}{$letter|escape}{/if}</a> {/foreach}<a href="{if $isCustomRole}{url path=$roleSymbolic customRoleId=$contextRole->getId()}{else}{url path=$roleSymbolic}{/if}">{if $searchInitial==''}<strong>{translate key="common.all"}</strong>{else}{translate key="common.all"}{/if}</a></p>

{if not $contextRole}
<ul>
{foreach from=$roles item=role}
	{if $role->isCustomRole()}
		{url|assign:"rolePath" op="people" path=$role->getPath() customRoleId=$role->getId()}
	{else}
		{url|assign:"rolePath" op="people" path=$role->getPath()}
	{/if}
	<li><a href="{$rolePath|escape}">{$role->getLocalizedPluralName()|escape}</a></li>
{/foreach}
</ul>

<br />
{else}
<p><a href="{url path="all"}" class="action">{translate key="manager.people.allUsers"}</a></p>
{/if}

<form name="people" action="{url page="user" op="email"}" method="post">
<input type="hidden" name="redirectUrl" value="{url path=$roleSymbolic}"/>

<a name="users"></a>

<table width="100%" class="listing">
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="5%">&nbsp;</td>
		<td width="12%">{translate key="user.username"}</td>
		<td width="20%">{translate key="user.name"}</td>
		<td width="23%">{translate key="user.email"}</td>
		<td width="40%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	{iterate from=users item=user}
	{assign var=userExists value=1}
	<tr valign="top">
		<td><input type="checkbox" name="bcc[]" value="{$user->getEmail()|escape}"/></td>
		<td><a class="action" href="{url op="userProfile" path=$user->getId()}">{$user->getUsername()|escape|wordwrap:15:" ":true}</a></td>
		<td>{$user->getFullName()|escape}</td>
		<td class="nowrap">
			{assign var=emailString value=$user->getFullName()|concat:" <":$user->getEmail():">"}
			{url|assign:"redirectUrl" path=$roleSymbolic escape=false}
			{url|assign:"url" page="user" op="email" to=$emailString|to_array redirectUrl=$redirectUrl}
			{$user->getEmail()|truncate:15:"..."|escape}&nbsp;{icon name="mail" url=$url}
		</td>
		<td align="right">
			{if $contextRole}
			<a href="{url op="unEnroll" path=$contextRole->getId() userId=$user->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="manager.people.confirmUnenroll"}')" class="action">{translate key="manager.people.unenroll"}</a>&nbsp;|
			{/if}
			<a href="{url op="editUser" path=$user->getId()}" class="action">{translate key="common.edit"}</a>
			{if $thisUser->getId() != $user->getId()}
				|&nbsp;<a href="{url page="login" op="signInAsUser" path=$user->getId()}" class="action">{translate key="manager.people.signInAs"}</a>
				{if !$contextRole}|&nbsp;<a href="{url op="removeUser" path=$user->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="manager.people.confirmRemove"}')" class="action">{translate key="manager.people.remove"}</a>{/if}
				{if $user->getDisabled()}
					|&nbsp;<a href="{url op="enableUser" path=$user->getId()}" class="action">{translate key="manager.people.enable"}</a>
				{else}
					|&nbsp;<a href="javascript:confirmAndPrompt({$user->getId()})" class="action">{translate key="manager.people.disable"}</a>
				{/if}
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="5" class="{if $users->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $users->wasEmpty()}
	<tr>
		<td colspan="5" class="nodata">{translate key="manager.people.noneEnrolled"}</td>
	</tr>
	<tr>
		<td colspan="5" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td colspan="4" align="left">{page_info iterator=$users}</td>
		<td align="right">{page_links anchor="users" name="users" iterator=$users searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth roleSymbolic=$roleSymbolic searchInitial=$searchInitial}</td>
	</tr>
{/if}
</table>

{if $userExists}
	<p><input type="submit" value="{translate key="email.compose"}" class="button defaultButton"/>&nbsp;<input type="button" value="{translate key="common.selectAll"}" class="button" onclick="toggleChecked()" /></p>
{/if}
</form>

<a href="{if $contextRole}{url op="enrollSearch" path=$contextRole->getId()}{else}{url op="enrollSearch"}{/if}" class="action">{translate key="manager.people.enrollExistingUser"}</a> |
{url|assign:"enrollmentUrl" path=$roleSymbolic searchInitial=$searchInitial searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth searchInitial=$searchInitial}
<a href="{if $contextRole}{url op="createUser" flexibleRoleId=$contextRole->getId() source=$enrollmentUrl}{else}{url op="createUser" source=$enrollmentUrl}{/if}" class="action">{translate key="manager.people.createUser"}</a> | <a href="{if $contextRole}{url op="enrollSyncSelect" path=$contextRole->getPath()}{else}{url op="enrollSyncSelect"}{/if}" class="action">{translate key="manager.people.enrollSync"}</a>

{include file="common/footer.tpl"}
