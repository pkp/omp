{**
 * series.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of series in journal management.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="series.series"}
{include file="common/header.tpl"}
{/strip}

<br/>

<div id="series">
<table width="100%" class="listing">
	<tr>
		<td class="headseparator" colspan="3">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="60%">{translate key="series.title"}</td>
		<td width="25%">{translate key="acquisitionsArrangement.abbreviation"}</td>
		<td width="15%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td class="headseparator" colspan="3">&nbsp;</td>
	</tr>
{iterate from=series item=series_item name=series}
	<tr valign="top">
		<td>{$series_item->getAcquisitionsArrangementTitle()|escape}</td>
		<td>{$series_item->getAcquisitionsArrangementAbbrev()|escape}</td>
		<td align="right" class="nowrap">
			<a href="{url op="editSeries" path=$series_item->getAcquisitionsArrangementId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteSeries" path=$series_item->getAcquisitionsArrangementId()}" onclick="return confirm('{translate|escape:"jsparam" key="manager.acquisitionsArrangement.confirmDelete"}')" class="action">{translate key="common.delete"}</a>&nbsp;|&nbsp;<a href="{url op="moveSeries" d=u arrangementId=$series_item->getAcquisitionsArrangementId()}">&uarr;</a>&nbsp;<a href="{url op="moveSeries" d=d arrangementId=$series_item->getAcquisitionsArrangementId()}">&darr;</a>
		</td>
	</tr>
	<tr>
		<td colspan="3" class="{if $series->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $series->wasEmpty()}
	<tr>
		<td colspan="3" class="nodata">{translate key="manager.series.noneCreated"}</td>
	</tr>
	<tr>
		<td colspan="3" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td align="left">{page_info iterator=$series}</td>
		<td colspan="2" align="right">{page_links anchor="series" name="series" iterator=$series}</td>
	</tr>
{/if}
</table>
<a class="action" href="{url op="createSeries"}">{translate key="manager.series.create"}</a>
</div>

{include file="common/footer.tpl"}
