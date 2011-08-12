{**
 * templates/workflow/review.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Review workflow stage.
 *}
{strip}
{include file="workflow/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#reviewTabs').pkpHandler(
				'$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id=reviewTabs>
	<ul>
		{section name="rounds" start=0 loop=$currentRound}
			{assign var="round" value=$smarty.section.rounds.index+1}
			<li{if ($round eq $selectedRound)} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
				<a href="{url path=$monograph->getId()|to_array:$round}">{translate key="submission.round" round=$round}</a>
			</li>
		{/section}
		{if $newRoundAction}
			<li id="newRoundTabContainer" class="ui-state-default ui-corner-top">
				{include file="linkAction/linkAction.tpl" action=$newRoundAction contextId="newRoundTabContainer"}
			</li>
		{/if}
	</ul>
	{if $roundStatus}
		{include file="common/reviewRoundStatus.tpl" round=$round roundStatus=$roundStatus}
	{/if}
	{url|assign:reviewFileSelectionGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.review.EditorReviewFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$selectedStageId round=$selectedRound escape=false}
	{load_url_in_div id="reviewFileSelection" url=$reviewFileSelectionGridUrl}

	{url|assign:reviewersGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.reviewer.ReviewerGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$selectedStageId round=$selectedRound escape=false}
	{load_url_in_div id="reviewersGrid" url=$reviewersGridUrl}

	{url|assign:revisionsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.review.ReviewRevisionsGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$selectedStageId round=$selectedRound escape=false}
	{load_url_in_div id="revisionsGrid" url=$revisionsGridUrl}

	{** editorial decision actions *}
	<div class="pkp_linkActions">
		{foreach from=$editorActions item=action}
			{include file="linkAction/linkAction.tpl" action=$action contextId="reviewTabs"}
		{/foreach}
	</div>
</div>

{include file="common/footer.tpl"}
