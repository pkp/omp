{**
 * submissionProduction.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission production.
 *
 * $Id$
 *}
{strip}
{translate|assign:"pageTitleTranslated" key="submission.page.editing" id=$submission->getMonographId()}
{assign var="pageCrumbTitle" value="submission.editing"}
{include file="common/header.tpl"}
{/strip}

<ul class="menu">
	<li><a href="{url op="submission" path=$submission->getMonographId()}">{translate key="submission.summary"}</a></li>
	{if $canReview}<li><a href="{url op="submissionReview" path=$submission->getMonographId()}">{translate key="submission.review"}</a></li>{/if}
	<li><a href="{url op="submissionEditing" path=$submission->getMonographId()}">{translate key="submission.editing"}</a></li>
	<li class="current"><a href="{url op="submissionProduction" path=$submission->getMonographId()}">{translate key="submission.production"}</a></li>
	<li><a href="{url op="submissionHistory" path=$submission->getMonographId()}">{translate key="submission.history"}</a></li>
</ul>

{include file="acquisitionsEditor/submission/summary.tpl"}

<div class="separator"></div>

{if $currentProcess != null and $currentProcess->getProcessId() == WORKFLOW_PROCESS_EDITING_COPYEDIT}

{include file="acquisitionsEditor/submission/production.tpl"}

<div class="separator"></div>
{else}

<em>Production not available</em>

{/if}

{include file="common/footer.tpl"}
