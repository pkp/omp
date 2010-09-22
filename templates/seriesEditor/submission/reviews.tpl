

{assign var="start" value="A"|ord}
{foreach from=$reviewAssignments item=reviewAssignment key=reviewKey}
{assign var="reviewId" value=$reviewAssignment->getReviewId()}

{if not $reviewAssignment->getCancelled() and not $reviewAssignment->getDeclined()}
	{assign var="reviewIndex" value=$reviewIndexes[$reviewId]}

	<table width="100%">
	<tr>
		<td colspan="3" class="separator">&nbsp;</td>
	</tr>
	<tr>
		<td width="20%"><h4>{translate key="user.role.reviewer"} {$reviewIndex+$start|chr}</h4></td>
		<td width="34%"><h4>{$reviewAssignment->getReviewerFullName()|escape}</h4></td>
		<td width="46%">
				{if not $reviewAssignment->getDateNotified()}
					<a href="{url op="clearReview" path=$submission->getId()|to_array:$reviewAssignment->getReviewId()}" class="action">{translate key="editor.monograph.clearReview"}</a>
				{elseif $reviewAssignment->getDeclined() or not $reviewAssignment->getDateCompleted()}
					<a href="{url op="cancelReview" monographId=$submission->getId() reviewId=$reviewAssignment->getReviewId()}" class="action">{translate key="editor.monograph.cancelReview"}</a>
				{/if}
		</td>
	</tr>
	</table>

	<table width="100%">
 	<tr valign="top">
		<td class="label">{translate key="submission.reviewForm"}</td>
		<td>
		{if $reviewAssignment->getReviewFormId()}
			{assign var="reviewFormId" value=$reviewAssignment->getReviewFormId()}
			{$reviewFormTitles[$reviewFormId]}
		{else}
			{translate key="manager.reviewForms.noneChosen"}
		{/if}
		{if !$reviewAssignment->getDateCompleted()}
			&nbsp;&nbsp;&nbsp;&nbsp;<a class="action" href="{url op="selectReviewForm" path=$submission->getId()|to_array:$reviewAssignment->getReviewId()}"{if $reviewFormResponses[$reviewId]} onclick="return confirm('{translate|escape:"jsparam" key="editor.monograph.confirmChangeReviewForm"}')"{/if}>{translate key="editor.monograph.selectReviewForm"}</a>{if $reviewAssignment->getReviewFormId()}&nbsp;&nbsp;&nbsp;&nbsp;<a class="action" href="{url op="clearReviewForm" path=$submission->getId()|to_array:$reviewAssignment->getReviewId()}"{if $reviewFormResponses[$reviewId]} onclick="return confirm('{translate|escape:"jsparam" key="editor.monograph.confirmChangeReviewForm"}')"{/if}>{translate key="editor.monograph.clearReviewForm"}</a>{/if}
		{/if}
		</td>
	</tr>
	<tr valign="top">
		<td class="label" width="20%">&nbsp;</td>
		<td width="80%">
			<table width="100%">
				<tr>
					<td class="heading" width="25%">{translate key="submission.request"}</td>
					<td class="heading" width="25%">{translate key="submission.underway"}</td>
					<td class="heading" width="25%">{translate key="submission.due"}</td>
					<td class="heading" width="25%">{translate key="submission.acknowledge"}</td>
				</tr>
				<tr valign="top">
					<td>
						{url|assign:"reviewUrl" op="notifyReviewer" reviewId=$reviewAssignment->getReviewId() monographId=$submission->getId()}
						{if $reviewAssignment->getDateNotified()}
							{$reviewAssignment->getDateNotified()|date_format:$dateFormatShort}
							{if !$reviewAssignment->getDateCompleted()}
								{icon name="mail" url=$reviewUrl}
							{/if}
						{else}
							{icon name="mail" url=$reviewUrl}
						{/if}
					</td>
					<td>
						{$reviewAssignment->getDateConfirmed()|date_format:$dateFormatShort|default:"&mdash;"}
					</td>
					<td>
						{if $reviewAssignment->getDeclined()}
							{translate key="seriesEditor.regrets"}
						{else}
							<a href="{url op="setDueDate" path=$reviewAssignment->getSubmissionId()|to_array:$reviewAssignment->getReviewId()}">{if $reviewAssignment->getDateDue()}{$reviewAssignment->getDateDue()|date_format:$dateFormatShort}{else}&mdash;{/if}</a>
						{/if}
					</td>
					<td>
						{url|assign:"thankUrl" op="thankReviewer" reviewId=$reviewAssignment->getReviewId() monographId=$submission->getId()}
						{if $reviewAssignment->getDateAcknowledged()}
							{$reviewAssignment->getDateAcknowledged()|date_format:$dateFormatShort}
						{elseif $reviewAssignment->getDateCompleted()}
							{icon name="mail" url=$thankUrl}
						{else}
							{icon name="mail" disabled="disabled" url=$thankUrl}
						{/if}
					</td>
				</tr>
			</table>
		</td>
	</tr>

	{if $reviewAssignment->getDateConfirmed() && !$reviewAssignment->getDeclined()}
		<tr valign="top">
			<td class="label">{translate key="reviewer.monograph.recommendation"}</td>
			<td>
				{if $reviewAssignment->getRecommendation() !== null && $reviewAssignment->getRecommendation() !== ''}
					{assign var="recommendation" value=$reviewAssignment->getRecommendation()}
					{translate key=$reviewerRecommendationOptions.$recommendation}
					&nbsp;&nbsp;{$reviewAssignment->getDateCompleted()|date_format:$dateFormatShort}
				{else}
					{translate key="common.none"}&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="{url op="remindReviewer" monographId=$submission->getId() reviewId=$reviewAssignment->getReviewId()}" class="action">{translate key="reviewer.monograph.sendReminder"}</a>
					{if $reviewAssignment->getDateReminded()}
						&nbsp;&nbsp;{$reviewAssignment->getDateReminded()|date_format:$dateFormatShort}
						{if $reviewAssignment->getReminderWasAutomatic()}
							&nbsp;&nbsp;{translate key="reviewer.monograph.automatic"}
						{/if}
					{/if}
				{/if}
			</td>
		</tr>
		{if $reviewFormResponses[$reviewId]}
			<tr valign="top">
				<td class="label">{translate key="submission.reviewFormResponse"}</td>
				<td>
					<a href="javascript:openComments('{url op="viewReviewFormResponse" path=$submission->getId()|to_array:$reviewAssignment->getReviewId()}');" class="icon">{icon name="letter"}</a>
				</td>
			</tr>
		{/if}
		{if !$reviewAssignment->getReviewFormId() || $reviewAssignment->getMostRecentPeerReviewComment()}{* Only display comments link if a comment is entered or this is a non-review form review *}
			<tr valign="top">
				<td class="label">{translate key="submission.review"}</td>
				<td>
					{if $reviewAssignment->getMostRecentPeerReviewComment()}
						{assign var="comment" value=$reviewAssignment->getMostRecentPeerReviewComment()}
						<a href="javascript:openComments('{url op="viewPeerReviewComments" path=$submission->getId()|to_array:$reviewAssignment->getReviewId() anchor=$comment->getCommentId()}');" class="icon">{icon name="letter"}</a>&nbsp;&nbsp;{$comment->getDatePosted()|date_format:$dateFormatShort}
					{else}
						<a href="javascript:openComments('{url op="viewPeerReviewComments" path=$submission->getId()|to_array:$reviewAssignment->getReviewId()}');" class="icon">{icon name="letter"}</a>&nbsp;&nbsp;{translate key="submission.comments.noComments"}
					{/if}
				</td>
			</tr>
		{/if}
		<tr valign="top">
			<td class="label">{translate key="reviewer.monograph.uploadedFile"}</td>
			<td>
				<table width="100%" class="data">
					{foreach from=$reviewAssignment->getReviewerFileRevisions() item=reviewerFile key=key}
					<tr valign="top">
						<td valign="middle">
							<form name="authorView{$reviewAssignment->getReviewId()}" method="post" action="{url op="makeReviewerFileViewable"}">
								<a href="{url op="downloadFile" path=$submission->getId()|to_array:$reviewerFile->getFileId():$reviewerFile->getRevision()}" class="file">{$reviewerFile->getFileName()|escape}</a>&nbsp;&nbsp;{$reviewerFile->getDateModified()|date_format:$dateFormatShort}
								<input type="hidden" name="reviewId" value="{$reviewAssignment->getReviewId()}" />
								<input type="hidden" name="monographId" value="{$submission->getId()}" />
								<input type="hidden" name="fileId" value="{$reviewerFile->getFileId()}" />
								<input type="hidden" name="revision" value="{$reviewerFile->getRevision()}" />
								{translate key="editor.monograph.showAuthor"} <input type="checkbox" name="viewable" value="1"{if $reviewerFile->getViewable()} checked="checked"{/if} />
								<input type="submit" value="{translate key="common.record"}" class="button" />
							</form>
						</td>
					</tr>
					{foreachelse}
					<tr valign="top">
						<td>{translate key="common.none"}</td>
					</tr>
					{/foreach}
				</table>
			</td>
		</tr>
	{/if}

	{if (($reviewAssignment->getRecommendation() === null || $reviewAssignment->getRecommendation() === '') || !$reviewAssignment->getDateConfirmed()) && $reviewAssignment->getDateNotified() && !$reviewAssignment->getDeclined()}
		<tr valign="top">
			<td class="label">{translate key="reviewer.monograph.editorToEnter"}</td>
			<td>
				{if !$reviewAssignment->getDateConfirmed()}
					<a href="{url op="confirmReviewForReviewer" path=$submission->getId()|to_array:$reviewAssignment->getReviewId() accept=1}" class="action">{translate key="reviewer.monograph.canDoReview"}</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="{url op="confirmReviewForReviewer" path=$submission->getId()|to_array:$reviewAssignment->getReviewId() accept=0}" class="action">{translate key="reviewer.monograph.cannotDoReview"}</a><br />
				{/if}
				<form method="post" action="{url op="uploadReviewForReviewer"}" enctype="multipart/form-data">
					{translate key="editor.monograph.uploadReviewForReviewer"}
					<input type="hidden" name="monographId" value="{$submission->getId()}" />
					<input type="hidden" name="reviewId" value="{$reviewAssignment->getReviewId()}"/>
					{fbvFileInput id="upload" submit="submit"}
				</form>
				{if $reviewAssignment->getDateConfirmed() && !$reviewAssignment->getDeclined()}
					<a class="action" href="{url op="enterReviewerRecommendation" monographId=$submission->getId() reviewId=$reviewAssignment->getReviewId()}">{translate key="editor.monograph.recommendation"}</a>
				{/if}
			</td>
		</tr>
	{/if}

	{if $reviewAssignment->getDateNotified() && !$reviewAssignment->getDeclined() && $rateReviewerOnQuality}
		<tr valign="top">
			<td class="label">{translate key="editor.monograph.rateReviewer"}</td>
			<td>
			<form method="post" action="{url op="rateReviewer"}">
				<input type="hidden" name="reviewId" value="{$reviewAssignment->getReviewId()}" />
				<input type="hidden" name="monographId" value="{$submission->getId()}" />
				<select name="quality" size="1" class="selectMenu">
					{html_options_translate options=$reviewerRatingOptions selected=$reviewAssignment->getQuality()}
				</select>&nbsp;&nbsp;
				<input type="submit" value="{translate key="common.record"}" class="button" />
				{if $reviewAssignment->getDateRated()}
					&nbsp;&nbsp;{$reviewAssignment->getDateRated()|date_format:$dateFormatShort}
				{/if}
			</form>
			</td>
		</tr>
	{/if}
	</table>
{/if}
{/foreach}
<div class="separator"></div>


