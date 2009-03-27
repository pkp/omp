{**
 * submissionCategory.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of submissionCategory in journal management.
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
		<td width="25%">{translate key="submissionCategory.abbreviation"}</td>
		<td width="15%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td class="headseparator" colspan="3">&nbsp;</td>
	</tr>
{iterate from=submissionCategory item=category_item name=submissionCategory}
	<tr valign="top">
		<td>{$category_item->getAcquisitionsArrangementTitle()|escape}</td>
		<td>{$category_item->getAcquisitionsArrangementAbbrev()|escape}</td>
		<td align="right" class="nowrap">
			<a href="{url op="editSubmissionCategory" path=$category_item->getAcquisitionsArrangementId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteSubmissionCategory" path=$category_item->getAcquisitionsArrangementId()}" onclick="return confirm('{translate|escape:"jsparam" key="manager.submissionCategory.confirmDelete"}')" class="action">{translate key="common.delete"}</a>&nbsp;|&nbsp;<a href="{url op="moveSubmissionCategory" d=u arrangementId=$category_item->getAcquisitionsArrangementId()}">&uarr;</a>&nbsp;<a href="{url op="moveSubmissionCategory" d=d arrangementId=$category_item->getAcquisitionsArrangementId()}">&darr;</a>
		</td>
	</tr>
	<tr>
		<td colspan="3" class="{if $submissionCategory->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $submissionCategory->wasEmpty()}
	<tr>
		<td colspan="3" class="nodata">{translate key="manager.submissionCategory.noneCreated"}</td>
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
<a class="action" href="{url op="createSubmissionCategory"}">{translate key="manager.submissionCategory.create"}</a>
</div>

{include file="common/footer.tpl"}
