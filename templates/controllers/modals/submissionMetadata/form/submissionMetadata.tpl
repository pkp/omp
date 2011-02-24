{**
 * submissionMetadata.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a submission's metadata
 *
 *}

{fbvFormArea id="submissionMetadata"}
	{fbvFormSection title="monograph.title"}
		{$monograph->getLocalizedTitle()|escape}
	{/fbvFormSection}
	{fbvFormSection title="monograph.description"}
		{$monograph->getLocalizedAbstract()}
	{/fbvFormSection}
	{fbvFormSection title="common.dateSubmitted"}
		{$monograph->getDateSubmitted()}
	{/fbvFormSection}
	{if $monograph->getSeriesTitle()}
		{fbvFormSection title="series.series"}
			{$monograph->getSeriesTitle()}
		{/fbvFormSection}
	{/if}
{/fbvFormArea}
