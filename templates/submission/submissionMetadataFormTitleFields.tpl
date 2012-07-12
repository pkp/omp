{**
 * submission/submissionMetadataFormTitleFields.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission's metadata form title fields. To be included in any form that wants to handle
 * submission metadata. Use classes/submission/SubmissionMetadataFormImplementation.inc.php
 * to handle this fields data.
 *}

{fbvFormArea id="generalInformation" class="border"}
	<p>{translate key="common.catalogInformation"}</p>
	{fbvFormSection for="title" title="common.prefix" inline="true" size=$fbvStyles.size.SMALL}
		{fbvElement type="text" multilingual=true id="prefix" value="$prefix" disabled=$readOnly maxlength="32"}
	{/fbvFormSection}
	{fbvFormSection for="title" title="monograph.title" inline="true" size=$fbvStyles.size.LARGE}
		{fbvElement type="text" multilingual=true name="title" id="title" value=$title disabled=$readOnly maxlength="255"}
	{/fbvFormSection}
	{fbvFormSection description="common.prefixAndTitle.tip"}{/fbvFormSection}
	{fbvFormSection title="monograph.subtitle" for="subtitle"}
		{fbvElement type="text" multilingual=true name="subtitle" id="subtitle" value=$subtitle disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection title="submission.submit.briefSummary" for="abstract"}
		{fbvElement type="textarea" multilingual=true name="abstract" id="abstract" value=$abstract rich=true disabled=$readOnly}
	{/fbvFormSection}
{/fbvFormArea}