{**
 * submission.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Layout editor's view of submission details.
 *
 * $Id$
 *}
{strip}
{translate|assign:"pageTitleTranslated" key="submission.page.editing" id=$submission->getId()}
{assign var="pageCrumbTitle" value="submission.editing"}
{include file="common/header.tpl"}
{/strip}

{assign var=layoutFile value=$submission->getLayoutFile()}

{include file="designer/submission/summary.tpl"}

<div class="separator"></div>

{include file="designer/submission/layout.tpl"}

{include file="common/footer.tpl"}

