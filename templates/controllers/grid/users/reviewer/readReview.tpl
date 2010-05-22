{**
 * readReview.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Screen to let user read a review
 *
 *}
<h2>{translate key="editor.review"}: {$monograph->getLocalizedTitle()}</h2>
<table width="100%">
	<tr>
	<td>{translate key="user.role.reviewer"}</td>
	<td>{translate key="editor.review.reviewCompleted"}</td>
	</tr>
	<tr>
	<td>{$reviewAssignment->getReviewerFullName()}</td>
	<td>{$reviewAssignment->getDateCompleted()}</td>
	</tr>	
</table> 
{** FIXME: add review forms **}
<br />
<h3>{translate key="editor.review.reviewerComments"}</h3>
{** FIXME: the reviewer comments are not being saved yet **}
<br />
<div id="attachments">
	{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.files.reviewAttachments.ReviewAttachmentsGridHandler" op="fetchGrid" readOnly=1 reviewId=$reviewAssignment->getId() escape=false}
	{load_url_in_div id="reviewAttachmentsGridContainer" loadMessageId="submission.submissionContributors.form.loadMessage" url="$reviewAttachmentsGridUrl"}
</div>
 