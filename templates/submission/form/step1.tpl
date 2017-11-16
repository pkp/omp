{**
 * templates/submission/form/step1.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 1 of author monograph submission.
 *}
{capture assign="additionalFormContent1"}
	<!-- Submission Type -->
	{fbvFormSection list="true" label="submission.workType"}
		{fbvElement type="radio" name="workType" id="isEditedVolume-0" value=$smarty.const.WORK_TYPE_AUTHORED_WORK checked=$workType|compare:$smarty.const.WORK_TYPE_EDITED_VOLUME:false:true label="submission.workType.authoredWork" disabled=$submissionId}{* "checked" is inverted; matches empty and WORK_TYPE_AUTHORED_WORK *}
		{fbvElement type="radio" name="workType" id="isEditedVolume-1" value=$smarty.const.WORK_TYPE_EDITED_VOLUME checked=$workType|compare:$smarty.const.WORK_TYPE_EDITED_VOLUME label="submission.workType.editedVolume" disabled=$submissionId}
	{/fbvFormSection}
{/capture}
{capture assign="additionalFormContent2"}
	{include file="submission/form/series.tpl" includeSeriesPosition=false}
{/capture}

{include file="core:submission/form/step1.tpl"}
