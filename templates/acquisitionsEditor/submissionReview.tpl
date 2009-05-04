{**
 * submissionReview.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission review.
 *
 * $Id$
 *}
{strip}
{translate|assign:"pageTitleTranslated" key="submission.page.review" id=$submission->getMonographId()}{assign var="pageCrumbTitle" value="submission.review"}
{include file="common/header.tpl"}
{/strip}

<ul class="menu">
	<li><a href="{url op="submission" path=$submission->getMonographId()}">{translate key="submission.summary"}</a></li>
	<li class="current"><a href="{url op="submissionReview" path=$submission->getMonographId()}">{translate key="submission.review"}</a></li>
	{if $canEdit}<li><a href="{url op="submissionEditing" path=$submission->getMonographId()}">{translate key="submission.editing"}</a></li>{/if}
	<li><a href="{url op="submissionHistory" path=$submission->getMonographId()}">{translate key="submission.history"}</a></li>
</ul>

{include file="acquisitionsEditor/submission/peerReview.tpl"}

{include file="common/footer.tpl"}
