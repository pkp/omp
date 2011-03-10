{**
 * details.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display monograph details (metadata, file grid)
 *}
{strip}
{include file="common/header.tpl"}
{/strip}

{include file="workflow/header.tpl"}

<!-- Editorial decision actions -->
<div class="pkp_linkActions">
	{foreach from=$editorActions item=action}
		{include file="linkAction/linkAction.tpl" action=$action contextId="submission"}
	{/foreach}
</div>

{url|assign:submissionFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submission.EditorSubmissionDetailsFilesGridHandler" op="fetchGrid" monographId=$monograph->getId()}
{load_url_in_div id="submissionFilesGridDiv" url=$submissionFilesGridUrl}

{include file="common/footer.tpl"}

