{**
 * layout.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the layout editing table.
 *
 * $Id$
 *}

{assign var=layoutFile value=$submission->getLayoutFile()}

<div id="layout">
<h3>{translate key="user.role.designers"}</h3>

{if !empty($layoutAssignments)}
<table class="data" width="100%">
{foreach from=$layoutAssignments item=layoutAssignment}
	<tr>
		<td>{$layoutAssignment->getDesignerFullName()}</td>
		<td>Cancel Assignment</td>
	</tr>
{/foreach}
</table>
{else}
<em>There are currently no assigned layout designers</em>
{/if}
<br />
<br />
<a href="{url op="assignLayoutEditor" path=$submission->getMonographId()}" class="action">{translate key="submission.layout.assignLayoutEditor"}</a>

<div class="separator"></div>

<h3>Layout Version</h3>
<form method="post" action="{url op="uploadLayoutFile"}"  enctype="multipart/form-data">
<input type="hidden" name="monographId" value="{$submission->getMonographId()}" />

<table>
	<tr>
		<td colspan="2">
			{translate key="common.file"}
		</td>
		<td>
			{if $layoutFile}
				<a href="{url op="downloadFile" path=$submission->getMonographId()|to_array:$layoutFile->getFileId()}" class="file">{$layoutFile->getFileName()|escape}</a>&nbsp;&nbsp;{$layoutFile->getDateModified()|date_format:$dateFormatShort}
			{else}
				{translate key="submission.layout.noLayoutFile"}
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2">
			{translate key="submission.layout.uploadLayoutVersion"}
		</td>
		<td>
			<input type="file" name="layoutFile" size="10" class="uploadField" />
			<input type="submit" value="{translate key="common.upload"}" class="button" />	
		</td>
	</tr>
</table>
</form>

<div class="separator"></div>
<h3>Layout Assignments</h3>
{if $layoutFile}
<table width="100%" class="info">
	<tr>
		<td width="28%">&nbsp;</td>
		<td width="18%" class="heading">{translate key="submission.request"}</td>
		<td width="16%" class="heading">{translate key="submission.underway"}</td>
		<td width="16%" class="heading">{translate key="submission.complete"}</td>
		<td width="22%" colspan="2" class="heading">{translate key="submission.acknowledge"}</td>
	</tr>
{if !empty($layoutAssignments)}
{foreach from=$layoutAssignments item=layoutAssignment}
	<tr>
		<td>{$layoutAssignment->getDesignerFullName()}</td>
		<td>
			{if $layoutAssignment->getDesignerId() && $layoutFile}
				{url|assign:"url" op="notifyLayoutDesigner" monographId=$submission->getMonographId() layoutAssignmentId=$layoutAssignment->getId()}
				{if $layoutAssignment->getDateUnderway()}
                                       	{translate|escape:"javascript"|assign:"confirmText" key="acquisitionsEditor.layout.confirmRenotify"}
                                       	{icon name="mail" onclick="return confirm('$confirmText')" url=$url}
                               	{else}
                                       	{icon name="mail" url=$url}
                               	{/if}
			{else}
				{icon name="mail" disabled="disable"}
			{/if}
			{$layoutAssignment->getDateNotified()|date_format:$dateFormatShort|default:""}
		</td>
		<td>
			{$layoutAssignment->getDateUnderway()|date_format:$dateFormatShort|default:"&mdash;"}
		</td>
		<td>
			{$layoutAssignment->getDateCompleted()|date_format:$dateFormatShort|default:"&mdash;"}
		</td>
		<td>
			{if $layoutAssignment->getDesignerId() &&  $layoutAssignment->getDateCompleted() && !$layoutAssignment->getDateAcknowledged()}
				{url|assign:"url" op="thankLayoutDesigner" monographId=$submission->getMonographId()}
				{icon name="mail" url=$url}
			{else}
				{icon name="mail" disabled="disable"}
			{/if}
			{$layoutAssignment->getDateAcknowledged()|date_format:$dateFormatShort|default:""}
		</td>
	</tr>
{/foreach}
{/if}
</table>

{/if}
<br />
{translate key="submission.layout.layoutComments"}
{if $submission->getMostRecentLayoutComment()}
	{assign var="comment" value=$submission->getMostRecentLayoutComment()}
	<a href="javascript:openComments('{url op="viewLayoutComments" path=$submission->getMonographId() anchor=$comment->getCommentId()}');" class="icon">{icon name="comment"}</a>{$comment->getDatePosted()|date_format:$dateFormatShort}
{else}
	<a href="javascript:openComments('{url op="viewLayoutComments" path=$submission->getMonographId()}');" class="icon">{icon name="comment"}</a>{translate key="common.noComments"}
{/if}

{if $currentPress->getLocalizedSetting('layoutInstructions')}
&nbsp;&nbsp;
<a href="javascript:openHelp('{url op="instructions" path="layout"}')" class="action">{translate key="submission.layout.instructions"}</a>
{/if}
{if $currentPress->getSetting('provideRefLinkInstructions')}
&nbsp;&nbsp;
<a href="javascript:openHelp('{url op="instructions" path="referenceLinking"}')" class="action">{translate key="submission.layout.referenceLinking"}</a>
{/if}
</div>

<div class="separator"></div>

<h3>Galleys and Other Files</h3>
<table width="100%" class="info">
	<tr>
		<td colspan="2">{translate key="submission.layout.galleyFormat"}</td>
		<td colspan="2" class="heading">{translate key="common.file"}</td>
		<td class="heading">{translate key="common.order"}</td>
		<td class="heading">{translate key="common.action"}</td>
		<td class="heading">{translate key="submission.views"}</td>
	</tr>
	{foreach name=galleys from=$submission->getGalleys() item=galley}
	<tr>
		<td width="2%">{$smarty.foreach.galleys.iteration}.</td>
		<td width="26%">{$galley->getGalleyLabel()|escape} &nbsp; <a href="{url op="proofGalley" path=$submission->getMonographId()|to_array:$galley->getGalleyId()}" class="action">{translate key="submission.layout.viewProof"}</a></td>
		<td colspan="2"><a href="{url op="downloadFile" path=$submission->getMonographId()|to_array:$galley->getFileId()}" class="file">{$galley->getFileName()|escape}</a>&nbsp;&nbsp;{$galley->getDateModified()|date_format:$dateFormatShort}</td>
		<td><a href="{url op="orderGalley" d=u monographId=$submission->getMonographId() galleyId=$galley->getGalleyId()}" class="plain">&uarr;</a> <a href="{url op="orderGalley" d=d monographId=$submission->getMonographId() galleyId=$galley->getGalleyId()}" class="plain">&darr;</a></td>
		<td>
			<a href="{url op="editGalley" path=$submission->getMonographId()|to_array:$galley->getGalleyId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteGalley" path=$submission->getMonographId()|to_array:$galley->getGalleyId()}" onclick="return confirm('{translate|escape:"jsparam" key="submission.layout.confirmDeleteGalley"}')" class="action">{translate key="common.delete"}</a>
		</td>
		<td>{$galley->getViews()|escape}</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7" class="nodata">{translate key="common.none"}</td>
	</tr>
	{/foreach}
	<tr>
		<td colspan="7" class="separator">&nbsp;</td>
	</tr>
	<tr>
		<td width="28%" colspan="2">{translate key="submission.supplementaryFiles"}</td>
		<td width="34%" colspan="2" class="heading">{translate key="common.file"}</td>
		<td width="16%" class="heading">{translate key="common.order"}</td>
		<td width="16%" colspan="2" class="heading">{translate key="common.action"}</td>
	</tr>
	{foreach name=suppFiles from=$submission->getSuppFiles() item=suppFile}
	<tr>
		<td width="2%">{$smarty.foreach.suppFiles.iteration}.</td>
		<td width="26%">{$suppFile->getSuppFileTitle()}</td>
		<td colspan="2"><a href="{url op="downloadFile" path=$submission->getMonographId()|to_array:$suppFile->getFileId()}" class="file">{$suppFile->getFileName()|escape}</a>&nbsp;&nbsp;{$suppFile->getDateModified()|date_format:$dateFormatShort}</td>
		<td><a href="{url op="orderSuppFile" d=u monographId=$submission->getMonographId() suppFileId=$suppFile->getSuppFileId()}" class="plain">&uarr;</a> <a href="{url op="orderSuppFile" d=d monographId=$submission->getMonographId() suppFileId=$suppFile->getSuppFileId()}" class="plain">&darr;</a></td>
		<td colspan="2">
			<a href="{url op="editSuppFile" from="submissionEditing" path=$submission->getMonographId()|to_array:$suppFile->getSuppFileId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteSuppFile" from="submissionEditing" path=$submission->getMonographId()|to_array:$suppFile->getSuppFileId()}" onclick="return confirm('{translate|escape:"jsparam" key="submission.layout.confirmDeleteSupplementaryFile"}')" class="action">{translate key="common.delete"}</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7" class="nodata">{translate key="common.none"}</td>
	</tr>
	{/foreach}
	<tr>
		<td colspan="7" class="separator">&nbsp;</td>
	</tr>
</table>
<form method="post" action="{url op="uploadLayoutFile"}"  enctype="multipart/form-data">
	<input type="hidden" name="from" value="submissionEditing" />
	<input type="hidden" name="monographId" value="{$submission->getMonographId()}" />
	{translate key="submission.uploadFileTo"} <input type="radio" name="layoutFileType" id="layoutFileTypeGalley" value="galley" /><label for="layoutFileTypeGalley">{translate key="submission.galley"}</label>, <input type="radio" name="layoutFileType" id="layoutFileTypeSupp" value="supp" /><label for="layoutFileTypeSupp">{translate key="monograph.suppFilesAbbrev"}</label>
	<input type="file" name="layoutFile" size="10" class="uploadField" />
	<input type="submit" value="{translate key="common.upload"}" class="button" />
</form>
<!--<table width="100%" class="info">
	<tr>
		<td width="28%" colspan="2">&nbsp;</td>
		<td width="18%" class="heading">{translate key="submission.request"}</td>
		<td width="16%" class="heading">{translate key="submission.underway"}</td>
		<td width="16%" class="heading">{translate key="submission.complete"}</td>
		<td width="22%" colspan="2" class="heading">{translate key="submission.acknowledge"}</td>
	</tr>
	<tr><td></td><td></td>
		<td>
			{if $useLayoutEditors}
				{$layoutAssignment->getDateUnderway()|date_format:$dateFormatShort|default:"&mdash;"}
			{else}
				{translate key="common.notApplicableShort"}
			{/if}
		</td>
		<td>
			{if $useLayoutEditors}
				{$layoutAssignment->getDateCompleted()|date_format:$dateFormatShort|default:"&mdash;"}
			{else}
				{translate key="common.notApplicableShort"}
			{/if}
		</td>
		<td colspan="2">
			{if $useLayoutEditors}
				{if $layoutAssignment->getEditorId() &&  $layoutAssignment->getDateCompleted() && !$layoutAssignment->getDateAcknowledged()}
					{url|assign:"url" op="thankLayoutEditor" monographId=$submission->getMonographId()}
					{icon name="mail" url=$url}
				{else}
					{icon name="mail" disabled="disable"}
				{/if}
				{$layoutAssignment->getDateAcknowledged()|date_format:$dateFormatShort|default:""}
			{else}
				{translate key="common.notApplicableShort"}
			{/if}
		</td>
	</tr>
	<tr valign="top">
		<td colspan="6">
			{translate key="common.file"}
			{if $layoutFile}
				<a href="{url op="downloadFile" path=$submission->getMonographId()|to_array:$layoutFile->getFileId()}" class="file">{$layoutFile->getFileName()|escape}</a>&nbsp;&nbsp;{$layoutFile->getDateModified()|date_format:$dateFormatShort}
			{else}
				{translate key="submission.layout.noLayoutFile"}
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="7" class="separator">&nbsp;</td>
	</tr>

	<tr>
		<td colspan="2">{translate key="submission.layout.galleyFormat"}</td>
		<td colspan="2" class="heading">{translate key="common.file"}</td>
		<td class="heading">{translate key="common.order"}</td>
		<td class="heading">{translate key="common.action"}</td>
		<td class="heading">{translate key="submission.views"}</td>
	</tr>
	{foreach name=galleys from=$submission->getGalleys() item=galley}
	<tr>
		<td width="2%">{$smarty.foreach.galleys.iteration}.</td>
		<td width="26%">{$galley->getGalleyLabel()|escape} &nbsp; <a href="{url op="proofGalley" path=$submission->getMonographId()|to_array:$galley->getGalleyId()}" class="action">{translate key="submission.layout.viewProof"}</a></td>
		<td colspan="2"><a href="{url op="downloadFile" path=$submission->getMonographId()|to_array:$galley->getFileId()}" class="file">{$galley->getFileName()|escape}</a>&nbsp;&nbsp;{$galley->getDateModified()|date_format:$dateFormatShort}</td>
		<td><a href="{url op="orderGalley" d=u monographId=$submission->getMonographId() galleyId=$galley->getGalleyId()}" class="plain">&uarr;</a> <a href="{url op="orderGalley" d=d monographId=$submission->getMonographId() galleyId=$galley->getGalleyId()}" class="plain">&darr;</a></td>
		<td>
			<a href="{url op="editGalley" path=$submission->getMonographId()|to_array:$galley->getGalleyId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteGalley" path=$submission->getMonographId()|to_array:$galley->getGalleyId()}" onclick="return confirm('{translate|escape:"jsparam" key="submission.layout.confirmDeleteGalley"}')" class="action">{translate key="common.delete"}</a>
		</td>
		<td>{$galley->getViews()|escape}</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7" class="nodata">{translate key="common.none"}</td>
	</tr>
	{/foreach}
	<tr>
		<td colspan="7" class="separator">&nbsp;</td>
	</tr>
	<tr>
		<td width="28%" colspan="2">{translate key="submission.supplementaryFiles"}</td>
		<td width="34%" colspan="2" class="heading">{translate key="common.file"}</td>
		<td width="16%" class="heading">{translate key="common.order"}</td>
		<td width="16%" colspan="2" class="heading">{translate key="common.action"}</td>
	</tr>
	{foreach name=suppFiles from=$submission->getSuppFiles() item=suppFile}
	<tr>
		<td width="2%">{$smarty.foreach.suppFiles.iteration}.</td>
		<td width="26%">{$suppFile->getSuppFileTitle()}</td>
		<td colspan="2"><a href="{url op="downloadFile" path=$submission->getMonographId()|to_array:$suppFile->getFileId()}" class="file">{$suppFile->getFileName()|escape}</a>&nbsp;&nbsp;{$suppFile->getDateModified()|date_format:$dateFormatShort}</td>
		<td><a href="{url op="orderSuppFile" d=u monographId=$submission->getMonographId() suppFileId=$suppFile->getSuppFileId()}" class="plain">&uarr;</a> <a href="{url op="orderSuppFile" d=d monographId=$submission->getMonographId() suppFileId=$suppFile->getSuppFileId()}" class="plain">&darr;</a></td>
		<td colspan="2">
			<a href="{url op="editSuppFile" from="submissionEditing" path=$submission->getMonographId()|to_array:$suppFile->getSuppFileId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteSuppFile" from="submissionEditing" path=$submission->getMonographId()|to_array:$suppFile->getSuppFileId()}" onclick="return confirm('{translate|escape:"jsparam" key="submission.layout.confirmDeleteSupplementaryFile"}')" class="action">{translate key="common.delete"}</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7" class="nodata">{translate key="common.none"}</td>
	</tr>
	{/foreach}
	<tr>
		<td colspan="7" class="separator">&nbsp;</td>
	</tr>
</table>

<form method="post" action="{url op="uploadLayoutFile"}"  enctype="multipart/form-data">
	<input type="hidden" name="from" value="submissionEditing" />
	<input type="hidden" name="monographId" value="{$submission->getMonographId()}" />
	{translate key="submission.uploadFileTo"} <input type="radio" name="layoutFileType" id="layoutFileTypeSubmission" value="submission" checked="checked" /><label for="layoutFileTypeSubmission">{translate key="submission.layout.layoutVersion"}</label>, <input type="radio" name="layoutFileType" id="layoutFileTypeGalley" value="galley" /><label for="layoutFileTypeGalley">{translate key="submission.galley"}</label>, <input type="radio" name="layoutFileType" id="layoutFileTypeSupp" value="supp" /><label for="layoutFileTypeSupp">{translate key="monograph.suppFilesAbbrev"}</label>
	<input type="file" name="layoutFile" size="10" class="uploadField" />
	<input type="submit" value="{translate key="common.upload"}" class="button" />
</form>
-->
