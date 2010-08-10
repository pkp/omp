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

{url|assign:submissionFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submissionFiles.SubmissionDetailsFilesGridHandler" op="fetchGrid" monographId=$monograph->getId()}
{load_url_in_div id="#submissionFilesGridDiv" url=$submissionFilesGridUrl}

{include file="common/footer.tpl"}
