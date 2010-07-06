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

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">

<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
	{foreach from=$rounds item=round}
		<li{if ($round eq $currentRound)} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url path=$round}">{translate key="submission.round" round=$round}</a>
		</li>
	{/foreach}
</ul>

{** FIXME: need to set escape=false due to bug 5265 *}
{url|assign:reviewFileSelectionGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.ReviewFilesGridHandler" op="fetchGrid" monographId=$monographId reviewType=$currentReviewType round=$currentRound canAdd=1 escape=false}
{load_url_in_div id="reviewFileSelection" url=$reviewFileSelectionGridUrl}

{url|assign:reviewersGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.reviewer.ReviewerGridHandler" op="fetchGrid" monographId=$monographId reviewType=$currentReviewType round=$currentRound escape=false}
{load_url_in_div id="reviewersGrid" url=$reviewersGridUrl}

{** editorial decision actions *}
{foreach from=$editorActions item=action}
	{include file="linkAction/linkAction.tpl" action=$action id="editorAction"}
{/foreach}

</div>
{include file="common/footer.tpl"}