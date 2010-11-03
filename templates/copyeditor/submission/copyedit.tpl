{**
 * copyedit.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the copyeditor's submission management table.
 *}
<div id="copyedit">
<h3>{translate key="submission.copyedit"}</h3>

<table width="100%" class="data">
	<tr>
		<td class="label" width="20%">{translate key="user.role.copyeditor"}</td>
		<td class="value" width="80%">{if $submission->getUserIdBySignoffType('SIGNOFF_COPYEDITING_INITIAL')}{$copyeditor->getFullName()|escape}{else}{translate key="common.none"}{/if}</td>
	</tr>
</table>

<table width="100%" class="info">
	<tr>
		<td width="40%" colspan="2"><a class="action" href="{url op="viewMetadata" path=$submission->getId()}">{translate key="submission.reviewMetadata"}</a></td>
		<td width="20%" class="heading">{translate key="submission.request"}</td>
		<td width="20%" class="heading">{translate key="submission.underway"}</td>
		<td width="20%" class="heading">{translate key="submission.complete"}</td>
	</tr>
	<tr>
		<td width="5%">1.</td>
		<td width="35%">{translate key="submission.copyedit.initialCopyedit"}</td>
		{assign var="initialCopyeditSignoff" value=$submission->getSignoff('SIGNOFF_COPYEDITING_INITIAL')}
		<td>{$initialCopyeditSignoff->getDateNotified()|date_format:$dateFormatShort|default:"&mdash;"}</td>
		<td>{$initialCopyeditSignoff->getDateUnderway()|date_format:$dateFormatShort|default:"&mdash;"}</td>
		<td>
			{if not $initialCopyeditSignoff->getDateNotified() or $initialCopyeditSignoff->getDateCompleted()}
				{icon name="mail" disabled="disabled"}
			{else}
				{url|assign:"url" op="completeCopyedit" monographId=$submission->getId()}
				{translate|assign:"confirmMessage" key="common.confirmComplete"}
				{icon name="mail" onclick="return confirm('$confirmMessage')" url=$url}
			{/if}
			{$initialCopyeditSignoff->getDateCompleted()|date_format:$dateFormatShort|default:""}
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="4">
			{translate key="common.file"}:
			{if $initialCopyeditSignoff->getDateNotified() && $initialCopyeditFile}
				<a href="{url op="downloadFile" path=$submission->getId()|to_array:$initialCopyeditSignoff->getFileId():$initialCopyeditSignoff->getFileRevision()}" class="file">{$initialCopyeditFile->getFileName()|escape}</a> {$initialCopyeditFile->getDateModified()|date_format:$dateFormatShort}
			{else}
				{translate key="common.none"}
			{/if}
			<br />
			<form method="post" action="{url op="uploadCopyeditVersion"}"  enctype="multipart/form-data">
				<input type="hidden" name="monographId" value="{$submission->getId()}" />
				<input type="hidden" name="copyeditStage" value="initial" />
				{if not $initialCopyeditSignoff->getDateNotified() or $initialCopyeditSignoff->getDateCompleted()} 
					{assign var="isDisabled" value="disabled"}
				{/if}
				{fbvFileInput id="upload" submit="submitFile" disabled=$isDisabled}
			</form>
		</td>
	</tr>
	<tr>
		<td colspan="5" class="separator">&nbsp;</td>
	</tr>
	<tr>
		<td>2.</td>
		{assign var="authorCopyeditSignoff" value=$submission->getSignoff('SIGNOFF_COPYEDITING_AUTHOR')}
		<td>{translate key="submission.copyedit.editorAuthorReview"}</td>
		<td>{$authorCopyeditSignoff->getDateNotified()|date_format:$dateFormatShort|default:"&mdash;"}</td>
		<td>{$authorCopyeditSignoff->getDateUnderway()|date_format:$dateFormatShort|default:"&mdash;"}</td>
		<td>{$authorCopyeditSignoff->getDateCompleted()|date_format:$dateFormatShort|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="4">
			{translate key="common.file"}:
			{if $authorCopyeditSignoff->getDateCompleted() && $editorAuthorCopyeditFile}
				<a href="{url op="downloadFile" path=$submission->getId()|to_array:$authorCopyeditSignoff->getFileId():$authorCopyeditSignoff->getFileRevision()}" class="file">{$editorAuthorCopyeditFile->getFileName()|escape}</a> {$editorAuthorCopyeditFile->getDateModified()|date_format:$dateFormatShort}
			{else}
				{translate key="common.none"}
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="5" class="separator">&nbsp;</td>
	</tr>
	<tr>
		<td>3.</td>
		{assign var="finalCopyeditSignoff" value=$submission->getSignoff('SIGNOFF_COPYEDITING_FINAL')}
		<td>{translate key="submission.copyedit.finalCopyedit"}</td>
		<td width="20%">{$finalCopyeditSignoff->getDateNotified()|date_format:$dateFormatShort|default:"&mdash;"}</td>
		<td width="20%">{$finalCopyeditSignoff->getDateUnderway()|date_format:$dateFormatShort|default:"&mdash;"}</td>
		<td width="20%">
			{if not $finalCopyeditSignoff->getDateNotified() or $finalCopyeditSignoff->getDateCompleted()}
				{icon name="mail" disabled="disabled"}
			{else}
				{url|assign:"url" op="completeFinalCopyedit" monographId=$submission->getId()}
				{translate|assign:"confirmMessage" key="common.confirmComplete"}
				{icon name="mail" onclick="return confirm('$confirmMessage')" url=$url}
			{/if}
			{$finalCopyeditSignoff->getDateCompleted()|date_format:$dateFormatShort|default:""}
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="4">
			{translate key="common.file"}:
			{if $finalCopyeditSignoff->getDateNotified() && $finalCopyeditFile}
				<a href="{url op="downloadFile" path=$submission->getId()|to_array:$finalCopyeditSignoff->getFileId():$finalCopyeditSignoff->getFileRevision()}" class="file">{$finalCopyeditFile->getFileName()|escape}</a> {$finalCopyeditFile->getDateModified()|date_format:$dateFormatShort}
			{else}
				{translate key="common.none"}
			{/if}
			<br />
			<form method="post" action="{url op="uploadCopyeditVersion"}"  enctype="multipart/form-data">
				<input type="hidden" name="monographId" value="{$submission->getId()}" />
				<input type="hidden" name="copyeditStage" value="final" />
				{if not $finalCopyeditSignoff->getDateNotified() or $finalCopyeditSignoff->getDateCompleted()}
					{assign var="isDisabled" value="disabled"}
				{/if}
				{fbvFileInput id="upload" submit="submitFile" disabled=$isDisabled}
			</form>
		</td>
	</tr>
	<tr>
		<td colspan="5" class="separator">&nbsp;</td>
	</tr>
</table>

{translate key="submission.copyedit.copyeditComments"}
{if $submission->getMostRecentCopyeditComment()}
	{assign var="comment" value=$submission->getMostRecentCopyeditComment()}
	<a href="javascript:openComments('{url op="viewCopyeditComments" path=$submission->getId() anchor=$comment->getCommentId()}');" class="icon">{icon name="comment"}</a>{$comment->getDatePosted()|date_format:$dateFormatShort}
{else}
	<a href="javascript:openComments('{url op="viewCopyeditComments" path=$submission->getId()}');" class="icon">{icon name="comment"}</a>{translate key="common.noComments"}
{/if}

{if $currentPress->getLocalizedSetting('copyeditInstructions') != ''}
&nbsp;&nbsp;
<a href="{url op="instructions" path="copy"}" class="action openHelp">{translate key="submission.copyedit.instructions"}</a>
{/if}
</div>