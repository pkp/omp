{**
 * templates/submission/form/series.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Include series placement for submissions.
 *}
{if count($seriesOptions) > 1} {* only display the series picker if there are series configured for this press *}
	{fbvFormSection label="series.series"}
		{fbvElement type="select" id="seriesId" from=$seriesOptions selected=$seriesId translate=false disabled=$readOnly size=$fbvStyles.size.SMALL}
	{/fbvFormSection}
{/if}
