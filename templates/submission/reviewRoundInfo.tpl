{**
 * details.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display monograph details (metadata, file grid)
 *}

<!--  Display round status -->
<div id="roundStatus" class="statusContainer">
	<p>{translate key="editor.monograph.roundStatus" round=$round}: {translate key="$roundStatus"}</p>
</div>
<br />

<!-- Display editor's message to the author -->
{if $monographEmails}
	<h6>{translate key="editor.review.personalMessageFromEditor"}:</h6>
	{iterate from=monographEmails item=monographEmail}
		<textarea class="editorPersonalMessage" disabled=true class="textArea">{$monographEmail->getBody()}</textarea>
	{/iterate}
	<br />
{/if}

<!-- Display review attachments grid -->
{if $showReviewAttachments}
	{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewAttachments.AuthorReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monograph->getId() escape=false}
	{load_url_in_div id="reviewAttachmentsGridContainer" url="$reviewAttachmentsGridUrl"}
{/if}