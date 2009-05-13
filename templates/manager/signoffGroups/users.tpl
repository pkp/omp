{**
 * users.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List enrolled users.
 *
 * $Id$
 *}
 
 <div id="users">
	<h4>{translate key="manager.users"}</h4>
	<table width="100%" class="listing">
	<tr><td colspan="4" class="headseparator">&nbsp;</td></tr>
	<tr class="heading" valign="bottom">
		<td width="25%">{translate key="user.username"}</td>
		<td width="35%">{translate key="user.name"}</td>
		<td width="30%">{translate key="user.email"}</td>
		<td width="10%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr><td colspan="4" class="headseparator">&nbsp;</td></tr>
	{iterate from=users item=user}
	{assign var="userid" value=$user->getId()}
	<tr valign="top">
		<td><a class="action" href="{url op="userProfile" path=$userid}">{$user->getUsername()}</a></td>
		<td>{$user->getFullName(true)|escape}</td>
		<td class="nowrap">
			{assign var=emailString value="`$user->getFullName()` <`$user->getEmail()`>"}
			{url|assign:"url" page="user" op="email" to=$emailString|to_array}
			{$user->getEmail()|truncate:20:"..."|escape}&nbsp;{icon name="mail" url=$url}
		</td>
		<td align="right" class="nowrap">
			<a href="{url op="removeSignoffUser" path=$reviewType userId=$user->getId()}" class="action">{translate key="manager.reviewSignoff.remove"}</a>
		</td>
	</tr>
	<tr><td colspan="4" class="{if $users->eof()}end{/if}separator">&nbsp;</td></tr>
	{/iterate}
	{if $users->wasEmpty()}
		<tr>
		<td colspan="4" class="nodata">{translate key="common.none"}</td>
		</tr>
		<tr><td colspan="4" class="endseparator">&nbsp;</td></tr>
	{else}
		<tr>
			<td colspan="3" align="left">{page_info iterator=$users}</td>
			<td colspan="2" align="right">{page_links anchor="users" name="users" iterator=$users}</td>
		</tr>
	{/if}
	</table>
	
	<a href="{url op="selectSignoffUser" path=$reviewType}" class="action">{translate key="manager.reviewSignoff.addUser"}</a>
</div>