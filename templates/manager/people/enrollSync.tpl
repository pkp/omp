{**
 * enrollSync.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Synchronize user enrollment with another press.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="manager.people.enrollment"}
{include file="common/header.tpl"}
{/strip}

<h3>{translate key="manager.people.syncUsers"}</h3>

<p><span class="instruct">{translate key="manager.people.syncUserDescription"}</span></p>

<form method="post" action="{url op="enrollSync"}">

<table class="data" width="100%">
	<tr valign="top">
		<td width="20%" class="label"><label for="rolePath">{translate key="manager.people.enrollSyncRole"}</label></td>
		<td width="80%" class="value">
			{if $rolePath}
				<input type="hidden" name="rolePath" value="{$rolePath|escape}" />
				{translate key=$roleName}
			{else}
				<select name="rolePath" id="rolePath" size="1" class="selectMenu">
					<option value=""></option>
 					<option value="all">{translate key="manager.people.allUsers"}</option>
				{foreach from=$roles item=role}
					<option value="{$role->getPath()|escape}">{$role->getLocalizedName()|escape}</a></li>
				{/foreach}
				</select>
			{/if}
		</td>
	</tr>
	<tr valign="top">
		<td class="label"><label for="syncPress">{translate key="manager.people.enrollSyncPress"}</label></td>
		<td class="value">
			<select name="syncPress" id="syncPress" size="1" class="selectMenu">
				<option value=""></option>
				<option value="all">{translate key="manager.people.allPresses"}</option>
				{html_options options=$pressOptions}
			</select>
		</td>
	</tr>
</table>

<p><input type="submit" value="{translate key="manager.people.enrollSync"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="history.go(-1)" /></p>

</form>

{include file="common/footer.tpl"}
