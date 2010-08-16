<!-- templates/editor/submissions.tpl -->

{**
 * submissions.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Editor submissions page(s).
 *
 * $Id$
 *}
{strip}
{strip}
{assign var="pageTitle" value="common.queue.long.$pageToDisplay"}
{url|assign:"currentUrl" page="editor"}
{include file="common/header.tpl"}
{/strip}
{/strip}

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">

<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
	<li{if ($pageToDisplay == "submissionsUnassigned")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url path="submissionsUnassigned"}">{translate key="common.queue.short.submissionsUnassigned}</a>
	</li>
	<li{if ($pageToDisplay == "submissionsInReview")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url path="submissionsInReview"}">{translate key="common.queue.short.submissionsInReview"}</a>
	</li>
	<li{if ($pageToDisplay == "submissionsInEditing")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url path="submissionsInEditing"}">{translate key="common.queue.short.submissionsInEditing}</a>
	</li>
	<li{if ($pageToDisplay == "submissionsArchives")} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
		<a href="{url path="submissionsArchives"}">{translate key="common.queue.short.submissionsArchives"}</a>
	</li>
</ul>

{include file="editor/$pageToDisplay.tpl"}

{if ($pageToDisplay == "submissionsInReview")}
<br />
<h4>{translate key="common.notes"}</h4>
<p>{translate key="editor.submissionReview.notes"}</p>
{/if}

</div>

{include file="common/footer.tpl"}

<!-- / templates/editor/submissions.tpl -->

