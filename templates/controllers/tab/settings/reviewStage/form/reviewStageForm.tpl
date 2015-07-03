{**
 * controllers/tab/settings/reviewStage/form/reviewStageForm.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Review stage management form (extension for OMP).
 *
 *}
{capture assign="additionalReviewFormContents"}
	{fbvFormArea id="internalReviewParts"}
		{fbvFormSection label="manager.setup.internalReviewGuidelines" description="manager.setup.internalReviewGuidelinesDescription"}
			{fbvElement type="textarea" multilingual="true" name="internalReviewGuidelines" id="internalReviewGuidelines" value=$internalReviewGuidelines rich=true}
		{/fbvFormSection}
	{/fbvFormArea}
{/capture}
{include file="core:controllers/tab/settings/reviewStage/form/reviewStageForm.tpl" additionalReviewFormContents=$additionalReviewFormContents}
