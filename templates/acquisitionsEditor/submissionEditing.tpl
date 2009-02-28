{**
 * submissionEditing.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission editing.
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
	<li class="current"><a href="{url op="submissionEditing" path=$submission->getMonographId()}">{translate key="submission.editing"}</a></li>
	<li><a href="{url op="submissionHistory" path=$submission->getMonographId()}">{translate key="submission.history"}</a></li>
</ul>

{include file="acquisitionsEditor/submission/summary.tpl"}

<div class="separator"></div>

{include file="acquisitionsEditor/submission/copyedit.tpl"}

<div class="separator"></div>

{include file="acquisitionsEditor/submission/scheduling.tpl"}

<div class="separator"></div>

{include file="acquisitionsEditor/submission/layout.tpl"}

<div class="separator"></div>

{include file="acquisitionsEditor/submission/proofread.tpl"}

{include file="common/footer.tpl"}
