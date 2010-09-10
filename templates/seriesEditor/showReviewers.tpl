<!-- templates/seriesEditor/showReviewers.tpl -->

{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Series editor index.
 *
 * $Id$
 *}
{strip}
{include file="common/header.tpl"}
{/strip}

{include file="submission/header.tpl"}

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">

<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
	{foreach from=$rounds item=round}
	<li{if ($round eq $selectedRound)} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url path=$monographId|to_array:$round}">{translate key="submission.round" round=$round}</a>
	</li>
	{/foreach}
	{url|assign:"newRoundUrl" router=$smarty.const.ROUTE_COMPONENT component="modals.editorDecision.EditorDecisionHandler" op="newReviewRound" monographId=$monographId}
	{modal url="$newRoundUrl" actOnId="nothing" dialogText='editor.monograph.newRound' button="#newRoundTab"}
	<li id="newRoundTabContainer" class="ui-state-default ui-corner-top ui-state-active">
		<a id="newRoundTab" href="#"><img class="ui-icon ui-icon-plus" style="float:left; margin-left:-5px;" />{translate key="editor.monograph.newRound"}</a>
	</li>
</ul>

{if $roundStatus}
<div id="roundStatus" class="statusContainer">
	<p>{translate key="editor.monograph.roundStatus" round=$round}: {translate key="$roundStatus"}</p>
</div>

<br />
{/if}

{** FIXME: need to set escape=false due to bug 5265 *}
{url|assign:reviewFileSelectionGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.EditorReviewFilesGridHandler" op="fetchGrid" stageId=$smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW monographId=$monographId reviewType=$currentReviewType round=$selectedRound canAdd=1 escape=false}
{load_url_in_div id="reviewFileSelection" url=$reviewFileSelectionGridUrl}

<br />

{url|assign:reviewersGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.reviewer.ReviewerGridHandler" op="fetchGrid" monographId=$monographId reviewType=$currentReviewType round=$selectedRound escape=false}
{load_url_in_div id="reviewersGrid" url=$reviewersGridUrl}

<br />

{url|assign:revisionsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.revisions.RevisionsGridHandler" op="fetchGrid" monographId=$monographId reviewType=$currentReviewType round=$selectedRound escape=false}
{load_url_in_div id="revisionsGrid" url=$revisionsGridUrl}

<br />

{** editorial decision actions *}
{foreach from=$editorActions item=action}
	{include file="linkAction/linkAction.tpl" action=$action id="editorAction"}
{/foreach}

</div>
{include file="common/footer.tpl"}
<!-- / templates/seriesEditor/showReviewers.tpl -->

