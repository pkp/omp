{**
 * submissionCategory.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of submission categories in press management.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="submissionCategory.submissionCategories"}
{include file="common/header.tpl"}
{/strip}

<br/>

<div id="submissionCategory">
<table width="100%" class="listing">
	<tr>
		<td class="headseparator" colspan="3">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="60%">{translate key="submissionCategory.title"}</td>
		<td width="25%">{translate key="series.abbreviation"}</td>
		<td width="15%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td class="headseparator" colspan="3">&nbsp;</td>
	</tr>
{iterate from=submissionCategory item=category_item name=submissionCategory}
	<tr valign="top">
		<td>{$category_item->getLocalizedTitle()|escape}</td>
		<td>{$category_item->getLocalizedAbbrev()|escape}</td>
		<td align="right" class="nowrap">
			<a href="{url op="editSubmissionCategory" path=$category_item->getId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteSubmissionCategory" path=$category_item->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="manager.series.confirmDelete"}')" class="action">{translate key="common.delete"}</a>&nbsp;|&nbsp;<a href="{url op="moveSubmissionCategory" d=u seriesId=$category_item->getId()}">&uarr;</a>&nbsp;<a href="{url op="moveSubmissionCategory" d=d seriesId=$category_item->getId()}">&darr;</a>
		</td>
	</tr>
	<tr>
		<td colspan="3" class="{if $submissionCategory->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $submissionCategory->wasEmpty()}
	<tr>
		<td colspan="3" class="nodata">{translate key="manager.categories.noneCreated"}</td>
	</tr>
	<tr>
		<td colspan="3" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td align="left">{page_info iterator=$submissionCategory}</td>
		<td colspan="2" align="right">{page_links anchor="submissionCategory" name="submissionCategory" iterator=$submissionCategory}</td>
	</tr>
{/if}
</table>
<a class="action" href="{url op="createSubmissionCategory"}">{translate key="manager.categories.create"}</a>
</div>

{include file="common/footer.tpl"}

