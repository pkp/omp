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

{include file="acquisitionsEditor/submission/peerReview.tpl"}

{include file="common/footer.tpl"}
