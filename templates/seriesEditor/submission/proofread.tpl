{**
 * proofread.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the proofreading table.
 *}
<div id="proofread">
<h3>{translate key="submission.proofreading"}</h3>

{if $useProofreaders}
<table class="data" width="100%">
	<tr>
		<td width="20%" class="label">{translate key="user.role.proofreader"}</td>
		{if $proofAssignment->getProofreaderId()}<td class="value" width="20%">{$proofAssignment->getProofreaderFullName()|escape}</td>{/if}
		<td class="value"><a href="{url op="selectProofreader" path=$submission->getId()}" class="action">{translate key="editor.monograph.selectProofreader"}</a></td>
	</tr>
</table>
{/if}

<table width="100%" class="info">
	<tr>
		<td width="28%" colspan="2">&nbsp;</td>
		<td width="18%" class="heading">{translate key="submission.request"}</td>
		<td width="18%" class="heading">{translate key="submission.underway"}</td>
		<td width="18%" class="heading">{translate key="submission.complete"}</td>
		<td width="18%" class="heading">{translate key="submission.acknowledge"}</td>
	</tr>
	<tr>
		<td width="2%">1.</td>
		<td width="26%">{translate key="user.role.author"}</td>
		<td>
			{url|assign:"url" op="notifyAuthorProofreader" monographId=$submission->getId()}
			{if $proofAssignment->getDateAuthorUnderway()}
				{translate|escape:"javascript"|assign:"confirmText" key="seriesEditor.author.confirmRenotify"}
				{icon name="mail" onclick="return confirm('$confirmText')" url=$url}
			{else}
				{icon name="mail" url=$url}
			{/if}

			{$proofAssignment->getDateAuthorNotified()|date_format:$dateFormatShort|default:""}
		</td>
		<td>
				{$proofAssignment->getDateAuthorUnderway()|date_format:$dateFormatShort|default:"&mdash;"}
		</td>
		<td>
			{$proofAssignment->getDateAuthorCompleted()|date_format:$dateFormatShort|default:"&mdash;"}
		</td>
		<td>
			{if $proofAssignment->getDateAuthorCompleted() && !$proofAssignment->getDateAuthorAcknowledged()}
				{url|assign:"url" op="thankAuthorProofreader" monographId=$submission->getId()}
				{icon name="mail" url=$url}
			{else}
				{icon name="mail" disabled="disable"}
			{/if}
			{$proofAssignment->getDateAuthorAcknowledged()|date_format:$dateFormatShort|default:""}
		</td>
	</tr>
	<tr>
		<td>2.</td>
		<td>{translate key="user.role.proofreader"}</td>
		<td>
			{if $useProofreaders}
				{if $proofAssignment->getProofreaderId() && $proofAssignment->getDateAuthorCompleted()}
					{url|assign:"url" op="notifyProofreader" monographId=$submission->getId()}
					{if $proofAssignment->getDateProofreaderUnderway()}
						{translate|escape:"javascript"|assign:"confirmText" key="seriesEditor.proofreader.confirmRenotify"}
						{icon name="mail" onclick="return confirm('$confirmText')" url=$url}
					{else}
						{icon name="mail" url=$url}
					{/if}
				{else}
					{icon name="mail" disabled="disable"}
				{/if}
			{else}
				{if !$proofAssignment->getDateProofreaderNotified()}
					<a href="{url op="editorInitiateProofreader" monographId=$submission->getId()}" class="action">{translate key="common.initiate"}</a>
				{/if}
			{/if}
			{$proofAssignment->getDateProofreaderNotified()|date_format:$dateFormatShort|default:""}
		</td>
		<td>
			{if $useProofreaders}
					{$proofAssignment->getDateProofreaderUnderway()|date_format:$dateFormatShort|default:"&mdash;"}
			{else}
				{translate key="common.notApplicableShort"}
			{/if}
		</td>
		<td>
			{if !$useProofreaders && !$proofAssignment->getDateProofreaderCompleted() && $proofAssignment->getDateProofreaderNotified()}
				<a href="{url op="editorCompleteProofreader" monographId=$submission->getId()}" class="action">{translate key="common.complete"}</a>
			{else}
				{$proofAssignment->getDateProofreaderCompleted()|date_format:$dateFormatShort|default:"&mdash;"}
			{/if}
		</td>
		<td>
			{if $useProofreaders}
				{if $proofAssignment->getDateProofreaderCompleted() && !$proofAssignment->getDateProofreaderAcknowledged()}
					{url|assign:"url" op="thankProofreader" monographId=$submission->getId()}
					{icon name="mail" url=$url}
				{else}
					{icon name="mail" disabled="disable"}
				{/if}
				{$proofAssignment->getDateProofreaderAcknowledged()|date_format:$dateFormatShort|default:""}
			{else}
				{translate key="common.notApplicableShort"}
			{/if}
		</td>
	</tr>
	<tr>
		<td>3.</td>
		<td>{translate key="user.role.designer"}</td>
		<td>
			{if $useLayoutEditors}
				{if $layoutAssignment->getEditorId() && $proofAssignment->getDateProofreaderCompleted()}
					{url|assign:"url" op="notifyLayoutEditorProofreader" monographId=$submission->getId()}
					{if $proofAssignment->getDateLayoutEditorUnderway()}
						{translate|escape:"javascript"|assign:"confirmText" key="seriesEditor.layout.confirmRenotify"}
						{icon name="mail" onclick="return confirm('$confirmText')" url=$url}
					{else}
						{icon name="mail" url=$url}
					{/if}
				{else}
					{icon name="mail" disabled="disable"}
				{/if}
			{else}
				{if !$proofAssignment->getDateLayoutEditorNotified()}
					<a href="{url op="editorInitiateLayoutEditor" monographId=$submission->getId()}" class="action">{translate key="common.initiate"}</a>
				{/if}
			{/if}
				{$proofAssignment->getDateLayoutEditorNotified()|date_format:$dateFormatShort|default:""}
		</td>
		<td>
			{if $useLayoutEditors}
				{$proofAssignment->getDateLayoutEditorUnderway()|date_format:$dateFormatShort|default:"&mdash;"}
			{else}
				{translate key="common.notApplicableShort"}
			{/if}
		</td>
		<td>
			{if $useLayoutEditors}
				{$proofAssignment->getDateLayoutEditorCompleted()|date_format:$dateFormatShort|default:"&mdash;"}
			{elseif $proofAssignment->getDateLayoutEditorCompleted()}
				{$proofAssignment->getDateLayoutEditorCompleted()|date_format:$dateFormatShort}
			{elseif $proofAssignment->getDateLayoutEditorNotified()}
				<a href="{url op="editorCompleteLayoutEditor" monographId=$submission->getId()}" class="action">{translate key="common.complete"}</a>
			{else}
				&mdash;
			{/if}
		</td>
		<td>
			{if $useLayoutEditors}
				{if $proofAssignment->getDateLayoutEditorCompleted() && !$proofAssignment->getDateLayoutEditorAcknowledged()}
					{url|assign:"url" op="thankLayoutEditorProofreader" monographId=$submission->getId()}
					{icon name="mail" url=$url}
				{else}
					{icon name="mail" disabled="disable"}
				{/if}
				{$proofAssignment->getDateLayoutEditorAcknowledged()|date_format:$dateFormatShort|default:""}
			{else}
				{translate key="common.notApplicableShort"}
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="6" class="separator">&nbsp;</td>
	</tr>
</table>

{translate key="submission.proofread.corrections"}
{if $submission->getMostRecentProofreadComment()}
	{assign var="comment" value=$submission->getMostRecentProofreadComment()}
	<a href="javascript:openComments('{url op="viewProofreadComments" path=$submission->getId() anchor=$comment->getCommentId()}');" class="icon">{icon name="comment"}</a>{$comment->getDatePosted()|date_format:$dateFormatShort}
{else}
	<a href="javascript:openComments('{url op="viewProofreadComments" path=$submission->getId()}');" class="icon">{icon name="comment"}</a>{translate key="common.noComments"}
{/if}

{if $currentPress->getLocalizedSetting('proofInstructions')}
&nbsp;&nbsp;
<a href="{url op="instructions" path="proof"}" class="action openHelp">{translate key="submission.proofread.instructions"}</a>
{/if}
</div>

