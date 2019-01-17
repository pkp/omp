{**
 * templates/controllers/grid/catalogEntry/dependentFiles.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The "dependent files" modal.
 *}
{capture assign=dependentFilesGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="grid.files.dependent.DependentFilesGridHandler" op="fetchGrid" submissionId=$submissionId fileId=$submissionFile->getFileId() stageId=$smarty.const.WORKFLOW_STAGE_ID_PRODUCTION escape=false}{/capture}
{load_url_in_div id="dependentFilesGridDiv" url=$dependentFilesGridUrl}
