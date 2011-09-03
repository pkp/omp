{**
 * submission/submissionMetadataFormFields.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission's metadata form fields. To be included in any form that wants to handle
 * submission metadata. Use classes/submission/SubmissionMetadataFormImplementation.inc.php
 * to handle this fields data.
 *}

{fbvFormArea id="generalInformation"}
	{fbvFormSection title="monograph.title" for="title"}
		{fbvElement type="text" multilingual=true name="title" id="title" value=$title maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection title="submission.submit.briefSummary" for="abstract"}
		{fbvElement type="textarea" multilingual=true name="abstract" id="abstract" value=$abstract disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection title="submission.submit.metadata"}
		{fbvElement type="keyword" id="disciplines" label="search.discipline" current=$disciplines disabled=$readOnly}
		{fbvElement type="keyword" id="keyword" label="common.keywords" current=$keywords disabled=$readOnly}
		{fbvElement type="keyword" id="agencies" label="submission.supportingAgencies" current=$agencies disabled=$readOnly}
	{/fbvFormSection}
{/fbvFormArea}
