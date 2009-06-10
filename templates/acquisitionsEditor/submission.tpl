{**
 * submission.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission summary.
 *
 * $Id$
 *}
{strip}
{translate|assign:"pageTitleTranslated" key="submission.page.summary" id=$submission->getMonographId()}
{assign var="pageCrumbTitle" value="submission.summary"}
{include file="common/header.tpl"}
{/strip}

<ul class="menu">
	<li class="current"><a href="{url op="submission" path=$submission->getMonographId()}">{translate key="submission.summary"}</a></li>
	{*if $canReview*}<li><a href="{url op="submissionReview" path=$submission->getMonographId()}">{translate key="submission.review"}</a></li>{*/if*}
	{*if $canEdit*}<li><a href="{url op="submissionEditing" path=$submission->getMonographId()}">{translate key="submission.editing"}</a></li>{*/if*}
	<li><a href="{url op="submissionProduction" path=$submission->getMonographId()}">{translate key="submission.production"}</a></li>
	<li><a href="{url op="submissionHistory" path=$submission->getMonographId()}">{translate key="submission.history"}</a></li>
</ul>

{include file="acquisitionsEditor/submission/management.tpl"}

<div class="separator"></div>

{include file="acquisitionsEditor/submission/editors.tpl"}

<div class="separator"></div>

{include file="acquisitionsEditor/submission/status.tpl"}

<div class="separator"></div>

{include file="submission/metadata/metadata.tpl"}

{include file="common/footer.tpl"}
