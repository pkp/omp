{**
 * templates/workflow/submission.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display monograph details (metadata, file grid)
 *}
{strip}
{include file="workflow/header.tpl"}
{/strip}

{url|assign:submissionEditorDecisionsUrl op="editorDecisionActions" monographId=$monograph->getId() stageId=$stageId contextId="submission" escape=false}
{load_url_in_div id="submissionEditorDecisionsDiv" url=$submissionEditorDecisionsUrl class="editorDecisionActions"}

<p class="pkp_help">{translate key="editor.submission.introduction"}</p>

{url|assign:submissionFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submission.EditorSubmissionDetailsFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId escape=false}
{load_url_in_div id="submissionFilesGridDiv" url=$submissionFilesGridUrl}

{url|assign:bookDocumentsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.bookDocuments.BookDocumentsFilesGridHandler" op="fetchGrid" monographId=$monograph->getId()}
{load_url_in_div id="bookDocumentsGridDiv" url=$bookDocumentsGridUrl}
</div>

{include file="common/footer.tpl"}
