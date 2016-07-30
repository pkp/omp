{**
 * templates/controllers/grid/catalogEntry/dependentFiles.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The "dependent files" modal.
 *}
{url|assign:dependentFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.dependent.DependentFilesGridHandler" op="fetchGrid" submissionId=$submissionId fileId=$submissionFile->getFileId() stageId=$smarty.const.WORKFLOW_STAGE_ID_PRODUCTION escape=false}
{load_url_in_div id="dependentFilesGridDiv" url=$dependentFilesGridUrl}
