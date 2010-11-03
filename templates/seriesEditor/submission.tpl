{**
 * submission.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission workflow wrapper for editors.
 *}
{strip}
{translate|assign:"pageTitleTranslated" key="submission.page.$pageToDisplay" id=$submission->getId()}
{assign var="pageCrumbTitle" value="submission.crumb.$pageToDisplay"}
{include file="common/header.tpl"}
{/strip}

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">

<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
	<li{if ($pageToDisplay == "submissionSummary")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url op="submission" path=$submission->getId()}">{translate key="submission.summary"}</a>
	</li>
	<li{if ($pageToDisplay == "submissionReview")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url op="submissionReview" path=$submission->getId()}">{translate key="submission.review"}</a>
	</li>
	<li{if ($pageToDisplay == "submissionEditing")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url op="submissionEditing" path=$submission->getId()}">{translate key="submission.editing"}</a>
	</li>
	<li{if ($pageToDisplay == "submissionProduction")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url op="submissionProduction" path=$submission->getId()}">{translate key="submission.production"}</a>
	</li>
	<li{if ($pageToDisplay == "submissionHistory")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url op="submissionHistory" path=$submission->getId()}">{translate key="submission.history"}</a>
	</li>
</ul>

{include file="seriesEditor/$pageToDisplay.tpl"}

</div>

{include file="common/footer.tpl"}

