{**
 * selectMergeUser.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List users so the site administrator can choose users to merge.
 *}
{strip}
{assign var="pageTitle" value="admin.mergeUsers"}
{include file="common/header.tpl"}
{/strip}

<p>{if $oldUserId != ''}{translate key="admin.mergeUsers.into.description"}{else}{translate key="admin.mergeUsers.from.description"}{/if}</p>

<h3>{translate key=$roleName}</h3>
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#selectUserForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>
<form class="pkp_form" method="post" id="selectUserForm" action="{url path=$roleSymbolic oldUserId=$oldUserId}">
	{fbvFormSection for="roleSymbolic"}
		{fbvElement type="select" id="roleSymbolic" from=$roleSymbolicOptions selected=$roleSymbolic size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	{fbvFormSection for="searchField"}
		{fbvElement type="select" id="searchField" from=$fieldOptions selected=$searchField inline="true" size=$fbvStyles.size.SMALL}
		{fbvElement type="select" id="searchMatch" from=$searchMatchOptions selected=$searchMatch inline="true" size=$fbvStyles.size.SMALL}
	{/fbvFormSection}
	{fbvFormSection for="search"}
		{fbvElement type="text" id="search" value=$search size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	{fbvFormSection size=$fbvStyles.size.MEDIUM}
		{fbvFormButtons hideCancel=true submitText="common.search"}
	{/fbvFormSection}

</form>

<p>{foreach from=$alphaList item=letter}<a href="{url path=$roleSymbolic oldUserId=$oldUserId searchInitial=$letter}">{if $letter == $searchInitial}<strong>{$letter|escape}</strong>{else}{$letter|escape}{/if}</a> {/foreach}<a href="{url path=$roleSymbolic oldUserId=$oldUserId}">{if $searchInitial==''}<strong>{translate key="common.all"}</strong>{else}{translate key="common.all"}{/if}</a></p>

{if not $roleId}
<ul>
	<li><a href="{url path="manager" oldUserId=$oldUserId}">{translate key="user.role.managers"}</a></li>
	<li><a href="{url path="seriesEditor" oldUserId=$oldUserId}">{translate key="user.role.seriesEditors"}</a></li>
	<li><a href="{url path="reviewer" oldUserId=$oldUserId}">{translate key="user.role.reviewers"}</a></li>
	<li><a href="{url path="author" oldUserId=$oldUserId}">{translate key="user.role.authors"}</a></li>
	<li><a href="{url path="reader" oldUserId=$oldUserId}">{translate key="user.role.readers"}</a></li>
</ul>

<br />
{else}
<p><a href="{url path="all" oldUserId=$oldUserId}" class="action">{translate key="admin.mergeUsers.allUsers"}</a></p>
{/if}

<a name="users"></a>

<table width="100%" class="pkp_listing">
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="29%">{translate key="user.username"}</td>
		<td width="29%">{translate key="user.name"}</td>
		<td width="29%">{translate key="user.email"}</td>
		<td width="13%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	{iterate from=users item=user}
	{assign var=userExists value=1}
	<tr valign="top">
		<td>{$user->getUsername()|escape|wordwrap:15:" ":true}</td>
		<td>{$user->getFullName()|escape}</td>
		<td>{$user->getEmail()|escape}</td>
		<td align="right">
			{if $oldUserId != ''}
				{if $oldUserId != $user->getId()}
					<a href="#" onclick="confirmAction('{url oldUserId=$oldUserId newUserId=$user->getId()}', '{translate|escape:"jsparam" key="admin.mergeUsers.confirm" oldUsername=$oldUsername newUsername=$user->getUsername()}')" class="action">{translate key="admin.mergeUsers.mergeUser"}</a>
				{/if}
			{elseif $thisUser->getId() != $user->getId()}
				<a href="{url oldUserId=$user->getId()}" class="action">{translate key="admin.mergeUsers.mergeUser"}</a>
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="4" class="{if $users->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $users->wasEmpty()}
	<tr>
		<td colspan="4" class="nodata">{translate key="admin.mergeUsers.noneEnrolled"}</td>
	</tr>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td colspan="2" align="left">{page_info iterator=$users}</td>
		<td colspan="2" align="right">{page_links anchor="users" name="users" iterator=$users searchInitial=$searchInitial searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth roleSymbolic=$roleSymbolic oldUserId=$oldUserId}</td>
	</tr>
{/if}
</table>

{include file="common/footer.tpl"}

