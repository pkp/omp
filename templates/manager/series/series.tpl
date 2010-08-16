<!-- templates/manager/series/series.tpl -->

{**
 * series.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of series in press management.
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
		<td width="25%">{translate key="series.abbreviation"}</td>
		<td width="15%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td class="headseparator" colspan="3">&nbsp;</td>
	</tr>
{iterate from=series item=series_item name=series}
	<tr valign="top">
		<td>{$series_item->getLocalizedTitle()|escape}</td>
		<td>{$series_item->getLocalizedAbbrev()|escape}</td>
		<td align="right" class="nowrap">
			<a href="{url op="editSeries" path=$series_item->getId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteSeries" path=$series_item->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="manager.series.confirmDelete"}')" class="action">{translate key="common.delete"}</a>&nbsp;|&nbsp;<a href="{url op="moveSeries" d=u seriesId=$series_item->getId()}">&uarr;</a>&nbsp;<a href="{url op="moveSeries" d=d seriesId=$series_item->getId()}">&darr;</a>
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

<!-- / templates/manager/series/series.tpl -->

