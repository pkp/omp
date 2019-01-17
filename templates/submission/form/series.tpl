{**
 * templates/submission/form/series.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Include series placement for submissions.
 *}
{if count($seriesOptions) > 1} {* only display the series picker if there are series configured for this press *}
	{fbvFormSection label="series.series"}
		{fbvElement type="select" id="seriesId" from=$seriesOptions selected=$seriesId translate=false disabled=$readOnly size=$fbvStyles.size.SMALL}
	{/fbvFormSection}

	{if $includeSeriesPosition}
		{fbvFormSection label="submission.submit.seriesPosition"}
			{fbvElement type="text" id="seriesPosition" name="seriesPosition" label="submission.submit.seriesPosition.description" value=$seriesPosition maxlength="255" disabled=$readOnly}
		{/fbvFormSection}
	{/if}
{/if}
