{**
 * layout.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the layout editing table.
 *
 * $Id$
 *}
{assign var=galleys value=$submission->getGalleys()}
{assign var=layoutFile value=$submission->getLayoutFile()}
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

<tr>
	<td colspan="3" class="separator">&nbsp;</td>
</tr>

{if $artworkCount > 0}
<tr>
	<td><a href="{url op="submissionArt" path=$submission->getId()}">{translate key="production.artworkFiles"}</a></td>
	<td>{translate key="manuscript.artwork"}</td>
	<td>{icon name="comment" disabled="disable"}</td>
</tr>

<tr>
	<td colspan="3" class="separator">&nbsp;</td>
</tr>

{/if}

</table>

<form method="post" action="{url op="uploadLayoutFile"}"  enctype="multipart/form-data">
	<input type="hidden" name="from" value="submissionEditing" />
	<input type="hidden" name="monographId" value="{$submission->getId()}" />
	{translate key="submission.layout.uploadLayoutVersion"}
	{fbvFileInput id="layoutFile" submit="submit"}
</form>

<div class="separator"></div>

<h3>{translate key="production.assignments"}</h3>
<p><a href="{url op="productionAssignment" path=$submission->getId()}">{translate key="production.assignment.new"}</a></p>
<form method="post" action="{url op="deleteSelectedAssignments"}">
<input type="hidden" name="monographId" value="{$submission->getId()}" />
<table class="listing" width="100%">
<tr>
<td width="5%">&nbsp;</td>
<td width="20%">{translate key="common.format"}</td>
<td width="8%">{translate key="common.notes"}</td>
<td width="22%">{translate key="common.action"}</td>
<td width="15%">{translate key="user.role.designer"}</td>
<td width="15%">{translate key="user.role.proofreader"}</td>
<td width="15%">{translate key="user.role.author"}</td>
</tr>
<tr>
	<td colspan="7" class="separator">&nbsp;</td>
</tr>
{assign var=productionAssignments value=$submission->getProductionAssignments()}

{foreach from=$productionAssignments item=productionAssignment}
{assign var=signoffs value=$productionAssignment->getSignoffs()}
{assign var=assignmentId value=$productionAssignment->getId()}
{assign var=theseGalleys value=$galleys.$assignmentId}
{assign var=proofSignoff value=$signoffs.PRODUCTION_PROOF_PROOFREADER}
{assign var=designSignoff value=$signoffs.PRODUCTION_DESIGN}
<tr valign="top">
	<td><input type="checkbox" name="selectedAssignments[]" value="{$assignmentId}" /></td>
	<td>
		{$productionAssignment->getLabel()|escape}
	</td>
	<td>{icon name="comment" disabled="disable"}</td>
	<td>
		<a href="{url op="productionAssignment" path=$submission->getId()|to_array:$productionAssignment->getId()}" class="action">{translate key="common.edit"}</a>
	</td>
	<td>
		{if !$designSignoff}
			<a href="{url op="selectDesigner" path=$submission->getId()|to_array:$assignmentId}">{translate key="common.assign"}</a>
		{elseif $designSignoff->getDateAcknowledged()}
			{translate key="common.complete"}
			{$designSignoff->getDateAcknowledged()|date_format:$dateFormatShort|default:""}
		{elseif $designSignoff->getDateCompleted()}
			{url|assign:"url" op="thankLayoutDesigner" monographId=$submission->getId() assignmentId=$assignmentId}
			{icon name="mail" url=$url}
			{$designSignoff->getDateCompleted()|date_format:$dateFormatShort|default:""}
		{elseif $designSignoff->getDateUnderway()}
			{translate key="common.underway"}
			{$designSignoff->getDateUnderway()|date_format:$dateFormatShort|default:""}
		{elseif $designSignoff->getDateNotified()}
			{url|assign:"url" op="notifyDesigner" monographId=$submission->getId() assignmentId=$assignmentId}
			{icon name="mail" url=$url}
			{$designSignoff->getDateNotified()|date_format:$dateFormatShort|default:""}
		{else}
			{url|assign:"url" op="notifyDesigner" monographId=$submission->getId() assignmentId=$assignmentId}
			{icon name="mail" url=$url}
		{/if}

	</td>
	<td>
		{if $proofSignoff}
			{$proofSignoff->getId()}
		{else}
			<a href="{url op="selectProofreader" path=$submission->getId()|to_array:$assignmentId}">{translate key="common.assign"}</a>
		{/if}
	</td>
	<td>{$proofSignoff}</td>
</tr>
{foreach from=$theseGalleys item=galley}
<tr valign="top">
	<td></td>
	<td>
		<a href="{url op="downloadFile" path=$submission->getId()|to_array:$galley->getFileId()}">{$galley->getFileName()|escape}</a>
	</td>
	<td>{icon name="comment" disabled="disable"}</td>
	<td>
		<a href="{url op="orderGalley" d=u monographId=$submission->getId() galleyId=$galley->getId()}" class="plain">&uarr;</a> <a href="{url op="orderGalley" d=d monographId=$submission->getId() galleyId=$galley->getId()}" class="plain">&darr;</a>&nbsp;|&nbsp;<a href="{url op="editGalley" path=$submission->getId()|to_array:$galley->getId()}" class="action">{translate key="common.edit"}</a>
		&nbsp;|&nbsp;<a href="{url op="deleteGalley" path=$submission->getId()|to_array:$galley->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="submission.layout.confirmDeleteGalley"}')" class="action">{translate key="common.delete"}</a>
	</td>
	<td></td>
	<td></td>
	<td></td>
</tr>
{/foreach}

{foreachelse}
<tr>
	<td colspan="7" class="nodata"><em>{translate key="common.none"}</em></td>
</tr>
{/foreach}
<tr>
	<td colspan="7" class="separator">&nbsp;</td>
</tr>
</table>

<input type="submit" class="button" name="deleteSelected" value="{translate key="common.delete"}" />
<input type="submit" class="button" value="{translate key="common.publish"}" disabled="disabled" />

</form>

<br />
{if count($productionAssignments)}
<form action="{url op="uploadGalley" path=$submission->getId()}" method="post" enctype="multipart/form-data">
<div class="newItemContainer">
	<h3>{translate key="production.uploadGalley"}</h3>
	<p>{translate key="production.uploadGalley.description}</p>
	<table class="data">
	<tr valign="top">
		<td class="label">{translate key="production.assignment"}</td>
		<td class="value">
			<select name="productionAssignmentId">
			<option>{translate key="common.select"}</option>
			{foreach from=$productionAssignments item=productionAssignment}
				<option value="{$productionAssignment->getId()}">{$productionAssignment->getLabel()|escape}</option>
			{/foreach}
			</select>
		</td>
	</tr>
	<tr valign="top">
		<td class="label">File</td>
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
{/if}

</div>
