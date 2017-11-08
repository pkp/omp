{**
 * templates/controllers/grid/users/author/form/authorForm.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission Contributor grid form
 *
 *}
{if $workType == $smarty.const.WORK_TYPE_EDITED_VOLUME}
	{capture assign="appAuthorCheckboxes"}
		{fbvElement type="checkbox" label="submission.submit.isVolumeEditor" id="isVolumeEditor" checked=$isVolumeEditor}
	{/capture}
	{capture assign="appJSFormHandler"}
		'$.pkp.controllers.modals.catalogEntry.form.AddContributorFormHandler',
		{ldelim}
			volumeEditorGroupIds: {$volumeEditorGroupIds|@json_encode}
		{rdelim}
	{/capture}
{/if}
{include file="core:controllers/grid/users/author/form/authorForm.tpl"}
