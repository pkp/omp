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
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="50%">{translate key="monograph.title"}</td>
		<td width="20%">{translate key="monograph.authors"}</td>
		<td width="30%">{translate key="monograph.publicationFormats"}</td>
	</tr>
	<tr>
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
	
	{iterate from=monographs item=monograph}

	<tr valign="top">
		<td>
			{$monograph->getLocalizedTitle()|escape}
		</td>
		<td>{$monograph->getAuthorString()|escape}</td>
		<td>{assign var="formats" value=$monograph->getAssignedPublicationFormats()}
			{foreach from=$formats item=format name="formats"}
					<a href="{plugin_url path="exportMonograph"|to_array:$format->getAssignedPublicationFormatId()}" class="action">{$format->getLocalizedTitle()|escape}{if !$smarty.foreach.formats.last}</a>, {/if}
			{foreachelse}
				{translate key="plugins.importexport.onix30.noFormats"}
			{/foreach}
		</td>
	</tr>
	<tr>
		<td colspan="3" class="{if $monographs->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $monographs->wasEmpty()}
	<tr>
		<td colspan="3" class="nodata">{translate key="common.none"}</td>
	</tr>
	<tr>
		<td colspan="3" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td align="left">{page_info iterator=$monographs}</td>
		<td></td>
		<td align="right">{page_links anchor="monographs" name="monographs" iterator=$monographs}</td>
	</tr>
{/if}
</table>
</div>
{include file="common/footer.tpl"}