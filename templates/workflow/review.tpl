{**
 * review.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Review workflow stage.
 *}
{strip}
{include file="common/header.tpl"}
{/strip}

{include file="workflow/header.tpl"}

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
	{section name="rounds" start=0 loop=$currentRound}
		{assign var="round" value=$smarty.section.rounds.index+1}
		<li{if ($round eq $selectedRound)} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url path=$monograph->getId()|to_array:$round}">{translate key="submission.round" round=$round}</a>
		</li>
	{/section}

	<li id="newRoundTabContainer" class="ui-state-default ui-corner-top">
		{include file="linkAction/linkAction.tpl" action=$newRoundAction contextId="newRoundTabContainer"}
	</li>
</ul>

{if $roundStatus}
	{include file="common/reviewRoundStatus.tpl" round=$round roundStatus=$roundStatus}
{/if}

{url|assign:reviewFileSelectionGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.review.EditorReviewFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() reviewType=$currentReviewType round=$selectedRound escape=false}
{load_url_in_div id="reviewFileSelection" url=$reviewFileSelectionGridUrl}

<br />

{url|assign:reviewersGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.reviewer.ReviewerGridHandler" op="fetchGrid" monographId=$monograph->getId() reviewType=$currentReviewType round=$selectedRound escape=false}
{load_url_in_div id="reviewersGrid" url=$reviewersGridUrl}

<br />

{url|assign:revisionsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.review.ReviewRevisionsGridHandler" op="fetchGrid" monographId=$monograph->getId() reviewType=$currentReviewType round=$selectedRound escape=false}
{load_url_in_div id="revisionsGrid" url=$revisionsGridUrl}

<br />

{** editorial decision actions *}
<div class="pkp_linkActions">
{foreach from=$editorActions item=action}
	{include file="linkAction/linkAction.tpl" action=$action contextId="review"}
{/foreach}
</div>

</div>
{include file="common/footer.tpl"}
