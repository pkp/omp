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
	{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.attachment.AuthorReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monograph->getId() escape=false}
	{load_url_in_div id="reviewAttachmentsGridContainer" url="$reviewAttachmentsGridUrl"}
{/if}
{/fbvFormArea}
</form>