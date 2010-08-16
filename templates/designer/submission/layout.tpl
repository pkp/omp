<!-- templates/designer/submission/layout.tpl -->

{**
 * layout.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the layout editor's layout editing table.
 *
 * $Id$
 *}
{assign var=galleys value=$submission->getGalleys()}

<div id="layout">

<h3>{translate key="common.bookFiles"}</h3>

<table width="100%" class="info">
<tr>
	<td width="25%" class="heading">{translate key="common.file"}</td>
	<td width="20%" class="heading">{translate key="common.type"}</td>
	<td width="20%" class="heading">{translate key="common.notes"}</td>
</tr>

<tr>
	<td>
		{if $layoutFile}
			<a href="{url op="downloadFile" path=$submission->getId()|to_array:$layoutFile->getFileId()}" class="file">{$layoutFile->getFileName()|escape}</a>&nbsp;&nbsp;{$layoutFile->getDateModified()|date_format:$dateFormatShort}
		{else}
			{translate key="common.none"}
		{/if}
	</td>
	<td>{translate key="production.layoutFile"}</td>
	<td>{icon name="comment" disabled="disable"}</td>
</tr>
</table>

<div class="separator"></div>

<h3>{translate key="submission.layout"}</h3>

<table width="100%" class="info">
	<tr>
		<td colspan="4" class="separator">&nbsp;</td>
	</tr>
	<tr>
		<td width="5%">&nbsp;</td>
		<td width="25%" class="heading">{translate key="submission.layout.galleyFormat"}</td>
		<td width="5%" class="heading">{translate key="common.notes"}</td>
		<td width="65%" class="heading">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="4" class="separator">&nbsp;</td>
	</tr>
	{foreach name=productionAssignments from=$submission->getProductionAssignments() item=productionAssignment}
	{assign var=assignmentId value=$productionAssignment->getId()}
	{assign var=productionSignoffs value=$productionAssignment->getSignoffs()}
	{assign var=designSignoff value=$productionSignoffs.PRODUCTION_DESIGN}
	{assign var=theseGalleys value=$galleys.$assignmentId}
	<tr>
		<td>{$smarty.foreach.productionAssignments.iteration}.</td>
		<td>{$productionAssignment->getLabel()|escape}</td>
		<td>{icon name="comment" disabled="disable"}</td>
		<td>	{if !$designSignoff->getDateCompleted()}
				<a href="{url op="completeDesign" monographId=$submission->getId() assignmentId=$productionAssignment->getId()}">{translate key="common.complete"}</a>
			{else}
				{translate key="common.complete"}
				{$designSignoff->getDateCompleted()|date_format:$dateFormatShort|default:""}
			{/if}
		</td>
	</tr>
	{foreach from=$theseGalleys item=galley}
	<tr valign="top">
		<td>&nbsp;</td>
		<td>
			<a href="{url op="downloadFile" path=$submission->getId()|to_array:$galley->getFileId()}">{$galley->getFileName()|escape}</a>
		</td>
		<td>{icon name="comment" disabled="disable"}</td>
		<td>
			{if !$designSignoff->getDateAcknowledged()}
			<a href="{url op="orderGalley" d=u monographId=$submission->getId() galleyId=$galley->getId()}" class="plain">&uarr;</a> <a href="{url op="orderGalley" d=d monographId=$submission->getId() galleyId=$galley->getId()}" class="plain">&darr;</a>&nbsp;|&nbsp;<a href="{url op="editGalley" path=$submission->getId()|to_array:$galley->getId()}" class="action">{translate key="common.edit"}</a>
			&nbsp;|&nbsp;<a href="{url op="deleteGalley" path=$submission->getId()|to_array:$galley->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="submission.layout.confirmDeleteGalley"}')" class="action">{translate key="common.delete"}</a>
			{else}&nbsp;{/if}
		</td>
		<td></td>
	</tr>
	{/foreach}
	{foreachelse}
	<tr>
		<td colspan="4" class="nodata">{translate key="common.none"}</td>
	</tr>
	{/foreach}
	<tr>
		<td colspan="4" class="separator">&nbsp;</td>
	</tr>
</table>

{translate key="submission.layout.layoutComments"}
{if $submission->getMostRecentLayoutComment()}
	{assign var="comment" value=$submission->getMostRecentLayoutComment()}
	<a href="javascript:openComments('{url op="viewLayoutComments" path=$submission->getId() anchor=$comment->getCommentId()}');" class="icon">{icon name="comment"}</a>{$comment->getDatePosted()|date_format:$dateFormatShort}
{else}
	<a href="javascript:openComments('{url op="viewLayoutComments" path=$submission->getId()}');" class="icon">{icon name="comment"}</a>{translate key="common.noComments"}
{/if}

{if $currentPress->getLocalizedSetting('layoutInstructions')}
&nbsp;&nbsp;
<a href="javascript:openHelp('{url op="instructions" path="layout"}')" class="action">{translate key="submission.layout.instructions"}</a>
{/if}
{if $currentPress->getSetting('provideRefLinkInstructions')}
&nbsp;&nbsp;
<a href="javascript:openHelp('{url op="instructions" path="referenceLinking"}')" class="action">{translate key="submission.layout.referenceLinking"}</a>
{/if}
{foreach name=templates from=$templates key=templateId item=template}
&nbsp;&nbsp;&nbsp;&nbsp;<a href="{url op="downloadLayoutTemplate" path=$templateId}" class="action">{$template.title|escape}</a>
{/foreach}
</div>

<br />

<form action="{url op="uploadGalley" path=$submission->getId()}" method="post" enctype="multipart/form-data">
<div class="newItemContainer">
	<h3>{translate key="production.uploadGalley"}</h3>
	<p>{translate key="production.uploadGalley.description"}</p>
	<table class="data">
	<tr valign="top">
		<td class="label">{translate key="production.assignment"}</td>
		<td class="value">
			<select name="productionAssignmentId">
			<option>{translate key="common.select"}</option>
			{foreach from=$submission->getProductionAssignments() item=productionAssignment}
				{assign var=productionSignoffs value=$productionAssignment->getSignoffs()}
				{assign var=designSignoff value=$productionSignoffs.PRODUCTION_DESIGN}
				{if !$designSignoff->getDateCompleted()}
				<option value="{$productionAssignment->getId()}">{$productionAssignment->getLabel()|escape}</option>
				{/if}
			{/foreach}
			</select>
		</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="common.file"}</td>
		<td class="value">
			{fbvFileInput id="galleyFile"}
		</td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td><input type="submit" value="{translate key="common.upload"}" class="button" /></td>
	</tr>
	</table>
</div>
</form>

<!-- / templates/designer/submission/layout.tpl -->

