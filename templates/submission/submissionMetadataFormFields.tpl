{**
 * submission/submissionMetadataFormFields.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
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
		{fbvElement type="textarea" multilingual=true name="abstract" id="abstract" value=$abstract rich=true disabled=$readOnly}
	{/fbvFormSection}
{/fbvFormArea}

{fbvFormArea id="coverageInformation" title="monograph.coverage" border="true"}
	{fbvFormSection title="monograph.coverage.chron" for="coverageChron" description="monograph.coverage.tip"}
		{fbvElement type="text" multilingual=true name="coverageChron" id="coverageChron" value=$coverageChron maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection title="monograph.coverage.geo" for="coverageGeo"}
		{fbvElement type="text" multilingual=true name="coverageGeo" id="coverageGeo" value=$coverageGeo maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection title="monograph.coverage.sample" for="coverageSample"}
		{fbvElement type="text" multilingual=true name="coverageSample" id="coverageSample" value=$coverageSample maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
{/fbvFormArea}

{fbvFormArea id="typeAndSubject"}
	{fbvFormSection title="monograph.type" for="type" description="monograph.title.tip"}
		{fbvElement type="text" multilingual=true name="type" id="type" value=$type maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection title="monograph.subjectClass" for="subjectClass" description="monograph.subjectClass.tip"}
		{fbvElement type="text" multilingual=true name="subjectClass" id="subjectClass" value=$subjectClass maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
{/fbvFormArea}

{fbvFormArea id="tagitFields" title="submission.submit.metadataForm" border="true"}
	{fbvFormSection description="submission.submit.metadataForm.tip"}
		{fbvElement type="keyword" id="languages" label="monograph.languages" subLabelTranslate=true multilingual=true current=$languages available=$availableLanguages disabled=$readOnly}
		{fbvElement type="keyword" id="subjects" label="monograph.subjects" subLabelTranslate=true multilingual=true current=$subjects disabled=$readOnly}
		{fbvElement type="keyword" id="disciplines" label="search.discipline" subLabelTranslate=true multilingual=true current=$disciplines disabled=$readOnly}
		{fbvElement type="keyword" id="keyword" label="common.keywords" subLabelTranslate=true multilingual=true current=$keywords disabled=$readOnly}
		{fbvElement type="keyword" id="agencies" label="submission.supportingAgencies" multilingual=true subLabelTranslate=true current=$agencies disabled=$readOnly}
	{/fbvFormSection}
{/fbvFormArea}
