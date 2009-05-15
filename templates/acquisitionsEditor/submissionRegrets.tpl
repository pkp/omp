{**
 * submissionRegrets.tpl
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show submission regrets/cancels/earlier rounds
 *
 *
 * $Id$
 *}
{strip}
{translate|assign:"pageTitleTranslated" key="acquisitionsEditor.regrets.title" monographId=$submission->getMonographId()}
{assign var=pageTitleTranslated value=$pageTitleTranslated|escape}
{assign var="pageCrumbTitle" value="acquisitionsEditor.regrets.breadcrumb"}
{include file="common/header.tpl"}
{/strip}

<ul class="menu">
	<li><a href="{url op="submission" path=$submission->getMonographId()}">{translate key="submission.summary"}</a></li>
	{if $canReview}<li><a href="{url op="submissionReview" path=$submission->getMonographId()}">{translate key="submission.review"}</a></li>{/if}
	{if $canEdit}<li><a href="{url op="submissionEditing" path=$submission->getMonographId()}">{translate key="submission.editing"}</a></li>{/if}
	<li><a href="{url op="submissionHistory" path=$submission->getMonographId()}">{translate key="submission.history"}</a></li>
</ul>

{include file="acquisitionsEditor/submission/summary.tpl"}

<div class="separator"></div>

{include file="acquisitionsEditor/submission/rounds.tpl"}

{include file="common/footer.tpl"}
