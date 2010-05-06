{**
 * submission.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission summary.
 *
 * $Id$
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
	<li{if ($pageToDisplay == "submissionArt")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url op="submissionArt" path=$submission->getId()}">{translate key="submission.art"}</a>
	</li>
	<li{if ($pageToDisplay == "submissionLayout")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url op="submissionLayout" path=$submission->getId()}">{translate key="submission.layout"}</a>
	</li>
</ul>

{include file="productionEditor/$pageToDisplay.tpl"}

</div>

{include file="common/footer.tpl"}
