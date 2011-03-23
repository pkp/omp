{**
 * enrollment.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List enrolled users.
 *}
{strip}
{assign var="pageTitle" value="manager.people.enrollment"}
{include file="common/header.tpl"}
{/strip}

<form id="disableUser" method="post" action="{url op="disableUser"}">
	<input type="hidden" name="reason" value=""/>
	<input type="hidden" name="userId" value=""/>
</form>

<script type="text/javascript">
{literal}
<!--
function toggleChecked() {
	var elements = document.getElementById('people').elements;
	for (var i=0; i < elements.length; i++) {
		if (elements[i].name == 'bcc[]') {
			elements[i].checked = !elements[i].checked;
		}
	}
}

function confirmAndPrompt(userId) {
	var reason = prompt('{/literal}{translate|escape:"javascript" key="manager.people.confirmDisable"}{literal}');
	if (reason == null) return;

	var disableUserForm = document.getElementById('disableUser');
	disableUserForm.reason.value = reason;
	disableUserForm.userId.value = userId;

	disableUserForm.submit();
}
// -->
{/literal}
</script>
{if $userGroup}
	{assign var=userGroupId value=$userGroup->getId()}
	<h3>{$userGroup->getLocalizedName()|escape}</h3>
{else}
	{assign var=userGroupId value='all'}
	<h3>{translate key="manager.people.allUsers"}</h3>
{/if}
<form method="post" action="{url path=$userGroupId}">
	<select name="userGroupId" class="selectMenu">
		<option {if !$userGroup}selected="selected" {/if}value="all">{translate key="manager.people.allUsers"}</option>
		{html_options options=$userGroupOptions selected=$userGroupId}
	</select>
	<select name="searchField" size="1" class="selectMenu">
		{html_options_translate options=$fieldOptions selected=$searchField}
	</select>
	<select name="searchField" size="1" class="selectMenu">
		{html_options_translate options=$searchOptions selected=$searchMatch}
	</select>
	<input type="text" id="searchInput" name="search" size=10 value="{$search|escape}" /> &nbsp;<input type="submit" value="{translate key="common.search"}" class="button" />
</form>

<p>{foreach from=$alphaList item=letter}<a href="{url path=$userGroupId searchInitial=$letter}">{if $letter == $searchInitial}<strong>{$letter|escape}</strong>{else}{$letter|escape}{/if}</a> {/foreach}<a href="{url path=$userGroupId}">{if $searchInitial==''}<strong>{translate key="common.all"}</strong>{else}{translate key="common.all"}{/if}</a></p>

{if not $userGroup}
<ul>
	{foreach from=$userGroupOptions item=name key=id}
		<li><a href="{url path=$id}">{$name}</a></li>
	{/foreach}
</ul>

<br />
{else}
<p><a href="{url path="all"}" class="action">{translate key="manager.people.allUsers"}</a></p>
{/if}

<form id="people" action="{url page="user" op="email"}" method="post">
<input type="hidden" name="redirectUrl" value="{url path=$userGroupId}"/>

<div id="users">
<table width="100%" class="pkp_listing">
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="5%">&nbsp;</td>
		<td width="12%">{sort_heading key="user.username" sort="username"}</td>
		<td width="20%">{sort_heading key="user.name" sort="name"}</td>
		<td width="23%">{sort_heading key="user.email" sort="email"}</td>
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
			{url|assign:"redirectUrl" path=$userGroupId escape=false}
			{url|assign:"url" page="user" op="email" to=$emailString|to_array redirectUrl=$redirectUrl}
			{$user->getEmail()|truncate:15:"..."|escape}&nbsp;{icon name="mail" url=$url}
		</td>
		<td align="right">
			{if $userGroup}
			<a href="{url op="unEnroll" path=$userGroupId userId=$user->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="manager.people.confirmUnenroll"}')" class="action">{translate key="manager.people.unenroll"}</a>&nbsp;|
			{/if}
			<a href="{url op="editUser" path=$user->getId()}" class="action">{translate key="common.edit"}</a>
			{if $thisUser->getId() != $user->getId()}
				|&nbsp;<a href="{url page="login" op="signInAsUser" path=$user->getId()}" class="action">{translate key="manager.people.signInAs"}</a>
				{if !$userGroup}|&nbsp;<a href="{url op="removeUser" path=$user->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="manager.people.confirmRemove"}')" class="action">{translate key="manager.people.remove"}</a>{/if}
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
		<td align="right">{page_links anchor="users" name="users" iterator=$users searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth userGroupId=$userGroupId searchInitial=$searchInitial sort=$sort sortDirection=$sortDirection}</td>
	</tr>
{/if}
</table>

{if $userExists}
	<p><input type="submit" value="{translate key="email.compose"}" class="button defaultButton"/>&nbsp;<input type="button" value="{translate key="common.selectAll"}" class="button" onclick="toggleChecked()" />  <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url page="manager" escape=false}'" /></p>
{/if}
</div>
</form>

<a href="{url op="enrollSearch" path=$userGroupId}" class="action">{translate key="manager.people.enrollExistingUser"}</a> |
{url|assign:"enrollmentUrl" path=$userGroupId searchInitial=$searchInitial searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth searchInitial=$searchInitial}
<a href="{if $userGroup}{url op="createUser" userGroupId=$userGroupId source=$enrollmentUrl}{else}{url op="createUser" source=$enrollmentUrl}{/if}" class="action">{translate key="manager.people.createUser"}</a> | <a href="{url op="enrollSyncSelect" path=$userGroupId}" class="action">{translate key="manager.people.enrollSync"}</a>
{include file="common/footer.tpl"}

