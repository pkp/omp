{**
 * complete.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The submission process has been completed; notify the author.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="author.track"}
{include file="common/header.tpl"}
{/strip}

<p>{translate key="author.submit.submissionComplete" pressName=$press->getPressName()}</p>

<p>&#187; <a href="{url page="author"}">{translate key="author.track"}</a></p>

{include file="common/footer.tpl"}
