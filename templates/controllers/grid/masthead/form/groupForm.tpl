{**
 * groupForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Group form under press management.
 *
 * $Id$
 *}
{if $group}
	<ul class="menu">
		<li class="current"><a href="#">{translate key="manager.groups.editTitle"}</a></li>
		<li><a href="javascript:replaceModalWithUrl('{$baseUrl}/index.php/dev/$$$call$$$/grid/masthead/masthead-row/group-membership?rowId={$group->getId()}', '{translate key="manager.groups.membership"}')">{translate key="manager.groups.membership"}</a></li>
	</ul>
{/if}

<a ></a>

<br/>

<form name="groupForm" method="post" action="{$baseUrl}/index.php/dev/$$$call$$$/grid/masthead/masthead-row/update-group">
{if $group}
	<input type="hidden" name="groupId" value="{$group->getId()}"/>
{/if}

{include file="common/formErrors.tpl"}
<table class="data" width="100%">
{if count($formLocales) > 1}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{if $group}{url|assign:"groupFormUrl" op="editGroup" path=$group->getId()}
			{else}{url|assign:"groupFormUrl" op="createGroup"}
			{/if}
			{form_language_chooser form="groupForm" url=$groupFormUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{/if}
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="title" required="true" key="manager.groups.title"}</td>
	<td width="80%" class="value"><input type="text" name="title[{$formLocale|escape}]" value="{$title[$formLocale]|escape}" size="35" maxlength="80" id="title" class="textField" /></td>
</tr>

<tr valign="top">
	<td width="20%" class="label">{translate key="common.type"}</td>
	<td width="80%" class="value">
		{foreach from=$groupContextOptions item=groupContextOptionKey key=groupContextOptionValue}
			<input type="radio" name="context" value="{$groupContextOptionValue|escape}" {if $context == $groupContextOptionValue}checked="checked" {/if} id="context-{$groupContextOptionValue|escape}" />&nbsp;
			{fieldLabel name="context-`$groupContextOptionValue`" key=$groupContextOptionKey}<br />
		{/foreach}
	</td>
</tr>
</table>
</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
