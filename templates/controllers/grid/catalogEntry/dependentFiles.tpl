{**
 * templates/controllers/grid/catalogEntry/dependentFiles.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * The "dependent files" modal.
 *}
{capture assign=dependentFilesGridUrl}{url router=PKP\core\PKPApplication::ROUTE_COMPONENT component="grid.files.dependent.DependentFilesGridHandler" op="fetchGrid" submissionId=$submissionId submissionFileId=$submissionFile->getId() stageId=$smarty.const.WORKFLOW_STAGE_ID_PRODUCTION escape=false}{/capture}
{load_url_in_div id="dependentFilesGridDiv" url=$dependentFilesGridUrl}
