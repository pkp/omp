{**
 * details.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display monograph details (metadata, file grid)
 *
 * $Id$
 *}
{strip}
{include file="common/header.tpl"}
{/strip}
{assign var="stageId" value=$monograph->getCurrentStageId()}

<script type="text/javascript">{literal}
	// initialise plugins
	$(function(){

		$(".stageContainer").accordion({
			autoHeight: false,
			collapsible: true
		});

		var stageId = {/literal}{$stageId}{literal};

		// Minimize all accordions not in the current stage; Disable accordions for future stages
		if (stageId == 1) {
			$(".stageContainer").not("#submission").accordion("activate", false);
			$(".stageContainer").not("#submission").accordion("option", "disabled", true);
		}

		if (stageId == 2 || stageId == 3) {
			$(".stageContainer").not("#review").accordion("activate", false);
			$(".stageContainer").not("#submission, #review").accordion("option", "disabled", true);
		}

		if (stageId == 4) {
			$(".stageContainer").not("#copyediting").accordion("activate", false);
			$(".stageContainer").not("#submission, #review, #copyediting").accordion("option", "disabled", true);
		}

		if (stageId == 5) {
			$(".stageContainer").not("#production").accordion("activate", false);
		}
	});


{/literal}</script>

<div class="submissionHeader">
	<div class="headerTop">
		<div class="heading">
			{assign var="primaryAuthor" value=$monograph->getPrimaryAuthor()}
			{$primaryAuthor->getLastName()} - {$monograph->getLocalizedTitle()}
		</div>
	</div>
</div>
<div style="clear:both;"></div>

{** Determine the amount the progress bar should be filled **}
{if $stageId == 2 || $stageId == 3}{assign var="fillerClass" value="accepted"}
{elseif $stageId == 4}{assign var="fillerClass" value="reviewed"}
{elseif $stageId == 5}{assign var="fillerClass" value="copyedited"}
{elseif $stageId == 0}{assign var="fillerClass" value="published"}
{else}{assign var="fillerClass" value=""}{/if}
<div id="authorTimeline" class="headerTimeline">
	<div id="timelineContainer">
		<div id="timelineFiller" class="{$fillerClass}"></div>
	</div>
	<div id="timelineLabelContainer">
		<span class="timelineLabel {if $stageId > 0}pastStep{else}futureStep{/if}">{translate key="submissions.submitted"}</span>
		<span class="timelineLabel {if $stageId > 1}pastStep{else}futureStep{/if}">{translate key="submission.accepted"}</span>
		<span class="timelineLabel center {if $stageId > 3}pastStep{else}futureStep{/if}">{translate key="submission.reviewed"}</span>
		<span class="timelineLabel right {if $stageId > 4}pastStep{else}futureStep{/if}">{translate key="submission.copyedited"}</span>
		<span class="timelineLabel right {if $stageId == 0}pastStep{else}futureStep{/if}">{translate key="navigation.published"}</span>
	</div>
</div>
<div style="clear:both;"></div>

{** User Alert**}
<div id="userAlert"></div>

<br />
<!-- Author actions -->
<div id="authorActions">
	<div id="addFile" class="authorAction">
		{include file="linkAction/linkAction.tpl" action=$uploadFileAction id="uploadFileAction"}
	</div>
	<div id="addVersions" class="authorAction">
		{if $stageId == 2 || $stageId == 3}
			{include file="linkAction/linkAction.tpl" action=$uploadRevisionAction id="uploadRevisionAction"}
		{elseif $stageId == 4}
			{include file="linkAction/linkAction.tpl" action=$addCopyeditedFileAction id="addCopyeditedFileAction"}
		{/if}
	</div>
	<div id="viewMetadata" class="authorAction">
		{include file="linkAction/linkAction.tpl" action=$viewMetadataAction id="viewMetadataAction"}
	</div>
</div>
<div style="clear:both;"></div>

<div class="stageContainer" id="submission">
	<h3><a href="#">{translate key='submission.submission'}</a></h3>
	<div id="content">
		{url|assign:submissionFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submissionFiles.SubmissionDetailsFilesGridHandler" op="fetchGrid" canAdd='false' monographId=$monograph->getId() escape=false}
		{load_url_in_div id="submissionFilesGridDiv" url=$submissionFilesGridUrl}
	</div>
</div>

<div class="stageContainer" id="review">
	<h3><a href="#">{translate key='submission.review'}</a></h3>
	<div id="content">
		{if $stageId > 1}
			{assign var="currentReviewRound" value=$monograph->getCurrentRound()}
			{init_tabs id="#reviewRoundTabs"}

			<div id="reviewRoundTabs">
				<ul>
					{foreach from=$rounds item=round}
						<li><a href="{url op="reviewRoundInfo" round=$round monographId=$monograph->getId() escape=false}">{translate key="submission.round" round=$round}</a></li>
					{/foreach}
				</ul>
			</div>

		{/if}
	</div>
</div>

<div class="stageContainer" id="copyediting">
	<h3><a href="#">{translate key='submission.copyediting'}</a></h3>
	<div id="content">
		<!-- Display editor's message to the author -->
		{if $monographEmails}
			<h6>{translate key="editor.review.personalMessageFromEditor"}:</h6>
			{iterate from=monographEmails item=monographEmail}
				<textarea class="editorPersonalMessage" disabled=true class="textArea">{$monographEmail->getBody()}</textarea>
			{/iterate}
			<br />
		{/if}

		<!-- Display review attachments grid -->
		{if $showCopyeditingFiles}
			{url|assign:copyeditingFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.authorCopyeditingFiles.AuthorCopyeditingFilesGridHandler" op="fetchGrid" canAdd='false' monographId=$monograph->getId() escape=false}
			{load_url_in_div id="copyeditingFilesGridDiv" url=$copyeditingFilesGridUrl}
		{/if}
	</div>
</div>

<div class="stageContainer" id="production">
	<h3><a href="#">{translate key='submission.production'}</a></h3>
	<div id="content">&nbsp;</div>
</div>


{include file="common/footer.tpl"}
