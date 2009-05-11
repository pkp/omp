{**
 * groups.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of groups in journal management.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="manager.groups"}
{assign var="pageId" value="manager.groups"}
{include file="common/header.tpl"}
{/strip}

<div id="groups">

<table width="100%" class="listing">
	<tr>
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td colspan="2" width="75%">{translate key="manager.groups.title"}</td>
		<td width="25%">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
{assign var="isFirstEditorialTeamEntry" value=1}
{iterate from=groups item=group}
	<tr valign="top">

		<td colspan="2">
			{url|assign:"url" page="manager" op="email" toGroup=$group->getId()}
			{$group->getLocalizedTitle()|escape}&nbsp;{icon name="mail" url=$url}
		</td>

		<td>
			<a href="{url op="addSignoffGroup" path=$reviewTypeId entityId=$group->getId()}" class="action">{translate key="common.add"}</a>
		</td>
	</tr>
	<tr>
		<td colspan="3" class="{if $groups->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $groups->wasEmpty()}
	<tr>
		<td colspan="3" class="nodata">{translate key="manager.groups.noneCreated"}</td>
	</tr>
	<tr>
		<td colspan="3" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td colspan="2" align="left">{page_info iterator=$groups}</td>
		<td colspan="1" align="right">{page_links anchor="groups" name="groups" iterator=$groups}</td>
	</tr>
{/if}
</table>

<!--<a href="{url op="createGroup"}" class="action">{translate key="manager.groups.create"}</a>-->
</div>

{include file="common/footer.tpl"}
