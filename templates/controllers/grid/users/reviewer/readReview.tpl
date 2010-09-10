<!-- templates/controllers/grid/users/reviewer/readReview.tpl -->

{**
 * readReview.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Screen to let user read a review
 *
 *}
{assign var='uniqueId' value=""|uniqid}

{translate|assign:"reviewTranslated" key="editor.review"}
{assign var=titleTranslated value="$reviewTranslated"|concat:": ":$monograph->getLocalizedTitle()}
{modal_title id="#editorReview" keyTranslated=$titleTranslated iconClass="fileManagement" canClose=1}

<div id="editorReview">
	<table width="100%" style="margin-left: 12px;">
		<tr>
			<td><strong>{translate key="user.role.reviewer"}</strong></td>
			<td><strong>{translate key="editor.review.reviewCompleted"}</strong></td>
		</tr>
		<tr>
			<td>{$reviewAssignment->getReviewerFullName()}</td>
			<td>{$reviewAssignment->getDateCompleted()}</td>
		</tr>
	</table>

	<br />

	{if $reviewAssignment->getReviewFormId()}
		{** FIXME: add review forms **}
	{else}
		<strong style="margin-left: 12px;">{translate key="editor.review.reviewerComments"}</strong>
		<p>{$reviewerComment->getComments()}</p>
	{/if}

	<br />

	<div id="attachments">
		{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.files.reviewAttachments.ReviewerReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monograph->getId() readOnly=1 reviewId=$reviewAssignment->getId() escape=false}
		{load_url_in_div id="readReviewAttachmentsGridContainer" url="$reviewAttachmentsGridUrl"}
	</div>
</div>

<!-- / templates/controllers/grid/users/reviewer/readReview.tpl -->

