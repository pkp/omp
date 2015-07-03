{**
 * templates/submission/form/series.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Include series placement for submissions.
 *}
{if count($seriesOptions) > 1} {* only display the series picker if there are series configured for this press *}
	{fbvFormSection label="series.series" description="submission.submit.placement.seriesDescription"}
		{fbvElement type="select" id="seriesId" from=$seriesOptions selected=$seriesId translate=false disabled=$readOnly size=$fbvStyles.size.SMALL}
	{/fbvFormSection}

	{if $includeSeriesPosition}
		{fbvFormSection label="submission.submit.seriesPosition" description="submission.submit.placement.seriesPositionDescription"}
			{fbvElement type="text" id="seriesPosition" name="seriesPosition" value=$seriesPosition maxlength="255" disabled=$readOnly}
		{/fbvFormSection}
	{/if}
{/if}
