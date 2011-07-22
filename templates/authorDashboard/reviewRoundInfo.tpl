{**
 * details.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display monograph details (metadata, file grid)
 *}

<!--  Display round status -->
{include file="common/reviewRoundStatus.tpl" round=$round roundStatus=$roundStatus}
<form class="pkp_form">
{fbvFormArea id="reviewRoundInfo"}
<!-- Display editor's message to the author -->
{if $monographEmails}
	{fbvFormSection label="editor.review.personalMessageFromEditor"}
	{iterate from=monographEmails item=monographEmail}
		{fbvElement type="textarea" id="monographEmail" value=$monographEmail->getBody() height=$fbvStyles.height.TALL disabled=true}
	{/iterate}
	{/fbvFormSection}
{/if}

<!-- Display review attachments grid -->
{if $showReviewAttachments}
	{** need to use the stage id in the div because two of these grids can appear in the dashboard at the same time (one for each stage). *}
	{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.attachment.AuthorReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId round=$round escape=false}
	{load_url_in_div id="reviewAttachmentsGridContainer-`$stageId`" url="$reviewAttachmentsGridUrl"}
{/if}
{/fbvFormArea}
</form>