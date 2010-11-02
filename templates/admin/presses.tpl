{**
 * presses.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of presses in site administration.

 *}
{strip}
{assign var="pageTitle" value="press.presses"}
{include file="common/header.tpl"}
{/strip}

<br />

<a name="presses"></a>

<table width="100%" class="listing" id="adminPresses">
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="top" class="heading">
		<td width="35%">{translate key="manager.setup.pressName"}</td>
		<td width="35%">{translate key="press.path"}</td>
		<td width="10%">{translate key="common.order"}</td>
		<td width="20%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	{iterate from=presses item=press}
	<tr valign="top">
		<td><a class="action" href="{url press=$press->getPath() page="manager"}">{$press->getLocalizedName()|escape}</a></td>
		<td>{$press->getPath()|escape}</td>
		<td><a href="{url op="movePress" d=u pressId=$press->getId()}">&uarr;</a> <a href="{url op="movePress" d=d pressId=$press->getId()}">&darr;</a></td>
		<td align="right"><a href="{url op="editPress" path=$press->getId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a class="action" href="{url op="deletePress" path=$press->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="admin.presses.confirmDelete"}')">{translate key="common.delete"}</a></td>
	</tr>
	<tr>
		<td colspan="4" class="{if $presses->eof()}end{/if}separator">&nbsp;</td>
	</tr>
	{/iterate}
	{if $presses->wasEmpty()}
	<tr>
		<td colspan="4" class="nodata">{translate key="admin.presses.noneCreated"}</td>
	</tr>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	</tr>
	{else}
		<tr>
			<td colspan="2" align="left">{page_info iterator=$presses}</td>
			<td colspan="2" align="right">{page_links anchor="presses" name="presses" iterator=$presses}</td>
		</tr>
	{/if}
</table>

<p><a href="{url op="createPress"}" class="action">{translate key="admin.presses.create"}</a></p>

{include file="common/footer.tpl"}

