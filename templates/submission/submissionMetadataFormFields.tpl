{**
 * submission/submissionMetadataFormFields.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Submission's metadata form fields. To be included in any form that wants to handle
 * submission metadata.
 *}
{capture assign="languagesField"}
	{fbvFormSection description="submission.submit.metadataForm.tip" label="common.languages" required=$languagesRequired}
		{fbvElement type="keyword" id="languages" subLabelTranslate=true multilingual=true current=$languages sourceUrl=$languagesSourceUrl disabled=$readOnly}
	{/fbvFormSection}
{/capture}
{include file="core:submission/submissionMetadataFormFields.tpl"}
