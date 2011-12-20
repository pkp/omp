{**
 * plugins/importexport/onix30/index.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List of monographs to potentially export
 *
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.onix30.selectMonograph"}
{assign var="pageCrumbTitle" value="plugins.importexport.onix30.selectMonograph"}
{include file="common/header.tpl"}
{/strip}

<br/>

<div id="monographs">
<table width="100%" class="listing">
	<tr>
		<td colspan="2" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="60%">{translate key="monograph.title"}</td>
		<td width="40%">{translate key="monograph.authors"}</td>
	</tr>
	<tr>
		<td colspan="2" class="headseparator">&nbsp;</td>
	</tr>
	
	{iterate from=monographs item=monograph}

	<tr valign="top">
		<td>
			<a href="{plugin_url path="exportMonograph"|to_array:$monograph->getId()}" class="action">{$monograph->getLocalizedTitle()|escape}</a>
		</td>
		<td>{$monograph->getAuthorString()|escape}</td>
	</tr>
	<tr>
		<td colspan="2" class="{if $monographs->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $monographs->wasEmpty()}
	<tr>
		<td colspan="2" class="nodata">{translate key="common.none"}</td>
	</tr>
	<tr>
		<td colspan="2" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td align="left">{page_info iterator=$monographs}</td>
		<td align="right">{page_links anchor="monographs" name="monographs" iterator=$monographs}</td>
	</tr>
{/if}
</table>
</div>
{include file="common/footer.tpl"}