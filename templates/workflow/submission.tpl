{**
 * templates/workflow/details.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display monograph details (metadata, file grid)
 *}

{strip}
{include file="workflow/header.tpl"}
{/strip}

{url|assign:submissionFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submission.EditorSubmissionDetailsFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId escape=false}
{load_url_in_div id="submissionFilesGridDiv" url=$submissionFilesGridUrl}

{include file="workflow/editorialLinkActions.tpl" contextId="submission"}
{include file="common/footer.tpl"}
