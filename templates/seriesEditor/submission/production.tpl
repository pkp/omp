{**
 * copyedit.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the copyediting table.
 *}
<div id="copyedit">
<h3>{translate key="submission.productionEditor"}</h3>

<table width="100%" class="data">
	<tr>
		<td width="20%" class="label">{translate key="user.role.productionEditor"}</td>
		{if $submission->getUserIdBySignoffType('SIGNOFF_PRODUCTION')}<td width="20%" class="value">{$productionEditor->getFullName()|escape}</td>{/if}
		<td class="value"><a href="{url op="assignProductionEditor" path=$submission->getId()}" class="action">{translate key="editor.monograph.assignProductionEditor"}</a></td>
	</tr>
</table>
{if $productionEditor}
<table width="100%" class="info">
	<tr>
		<td width="28%" colspan="2"><a href="{url op="viewMetadata" path=$submission->getId()}" class="action">{translate key="submission.reviewMetadata"}</a></td>
		<td width="18%" class="heading">{translate key="submission.request"}</td>
		<td width="18%" class="heading">{translate key="submission.underway"}</td>
		<td width="18%" class="heading">{translate key="submission.complete"}</td>
		<td width="18%" class="heading">{translate key="submission.acknowledge"}</td>
	</tr>
	<tr>
		<td width="2%">1.</td>
		{assign var="productionSignoff" value=$submission->getSignoff('SIGNOFF_PRODUCTION')}
		<td width="26%">{translate key="submission.production.production"}</td>
		<td>
			{if $submission->getUserIdBySignoffType('SIGNOFF_PRODUCTION') && $initialProductionFile}
				{url|assign:"url" op="notifyProductionEditor" monographId=$submission->getId()}
				{if $initialCopyeditSignoff->getDateUnderway()}
					{translate|escape:"javascript"|assign:"confirmText" key="seriesEditor.copyedit.confirmRenotify"}
					{icon name="mail" onclick="return confirm('$confirmText')" url=$url}
				{else}
					{icon name="mail" url=$url}
				{/if}
			{else}
				{icon name="mail" disabled="disable"}
			{/if}
			{$productionSignoff->getDateNotified()|date_format:$dateFormatShort|default:""}
		</td>
		<td>
			{$productionSignoff->getDateUnderway()|date_format:$dateFormatShort|default:"&mdash;"}
		</td>
		<td>
			{$productionSignoff->getDateCompleted()|date_format:$dateFormatShort|default:"&mdash;"}
		</td>
		<td>
			{if $submission->getUserIdBySignoffType('SIGNOFF_PRODUCTION') &&  $productionSignoff->getDateNotified() && !$productionSignoff->getDateAcknowledged()}
				{url|assign:"url" op="thankProductionEditor" monographId=$submission->getId()}
				{icon name="mail" url=$url}
			{else}
				{icon name="mail" disabled="disable"}
			{/if}
			{$productionSignoff->getDateAcknowledged()|date_format:$dateFormatShort|default:""}
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="5">
			{translate key="common.file"}:
			{if $initialProductionFile}
				<a href="{url op="downloadFile" path=$submission->getId()|to_array:$initialProductionFile->getFileId():$initialProductionFile->getRevision()}" class="file">{$initialProductionFile->getFileName()|escape}</a>&nbsp;&nbsp;{$initialProductionFile->getDateModified()|date_format:$dateFormatShort}
			{else}
				{translate key="submission.production.mustUploadFileForProduction"}
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="6" class="separator">&nbsp;</td>
	</tr>
</table>

<form method="post" action="{url op="uploadProductionVersion"}"  enctype="multipart/form-data">
	<input type="hidden" name="monographId" value="{$submission->getId()}" />
	{translate key="submission.uploadFile"}
	{fbvFileInput id="upload" submit="submit"}
</form>

{/if}

