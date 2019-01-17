{**
 * submission/submissionMetadataFormFields.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission's metadata form fields. To be included in any form that wants to handle
 * submission metadata.
 *}
{capture assign="languagesField"}
	{fbvFormSection description="submission.submit.metadataForm.tip" title="common.languages" required=$languagesRequired}
		{capture assign=languagesSourceUrl}{url router=$smarty.const.ROUTE_PAGE page="submission" op="fetchChoices" codeList="74"}{/capture}
		{fbvElement type="keyword" id="languages" subLabelTranslate=true multilingual=true current=$languages sourceUrl=$languagesSourceUrl disabled=$readOnly}
	{/fbvFormSection}
{/capture}
{include file="core:submission/submissionMetadataFormFields.tpl"}
