{**
 * details.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display monograph details (metadata, file grid)
 *
 * $Id$
 *}
{strip}
{include file="common/header.tpl"}
{/strip}

{include file="submission/header.tpl"}

{** FIXME #5898: This may have to be changed pending discussion with Brent on how to get to copyediting stage **}
{if $hasFullAccess}
	{** If user is currently in a Press Manager or Series editor role, show link to copyediting page **}
	<h1><a href="{url page="workflow" op="copyediting" path=$monograph->getId()}" style="size: 3em; text">{translate key="submission.copyediting"}</a></h1>
{elseif $isAuthor}
	{url|assign:copyeditingGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.copyeditingFiles.CopyeditingFilesGridHandler" op="fetchGrid" monographId=$monographId}
	{load_url_in_div id="copyeditingGrid" url=$copyeditingGridUrl}
{/if}

{url|assign:submissionFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submissionFiles.SubmissionDetailsFilesGridHandler" op="fetchGrid" monographId=$monograph->getId()}
{load_url_in_div id="submissionFilesGridDiv" url=$submissionFilesGridUrl}

{include file="common/footer.tpl"}

