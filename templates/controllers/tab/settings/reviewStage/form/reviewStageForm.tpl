{**
 * controllers/tab/settings/reviewStage/form/reviewStageForm.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Review stage management form (extension for OMP).
 *
 *}

{* Help Link *}
{help file="settings.md" section="workflow" class="pkp_help_tab"}

{capture assign="additionalReviewFormContents"}
	{fbvFormSection label="manager.setup.internalReviewGuidelines" description="manager.setup.internalReviewGuidelinesDescription"}
		{fbvElement type="textarea" multilingual="true" name="internalReviewGuidelines" id="internalReviewGuidelines" value=$internalReviewGuidelines rich=true}
	{/fbvFormSection}
{/capture}
{include file="core:controllers/tab/settings/reviewStage/form/reviewStageForm.tpl" additionalReviewFormContents=$additionalReviewFormContents}
