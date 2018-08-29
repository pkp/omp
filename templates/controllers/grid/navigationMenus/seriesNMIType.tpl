{**
 * templates/controllers/grid/navigationMenus/seriesNMIType.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Custom series NMI Type edit form part
 *}
{fbvFormSection id="NMI_TYPE_SERIES" class="NMI_TYPE_CUSTOM_EDIT" title="manager.navigationMenus.form.navigationMenuItem.series" for="seriesSelect"}
	{if count($navigationMenuItemSeriesTitles) gt 0}
		{fbvElement type="select" id="relatedSeriesId" required=true from=$navigationMenuItemSeriesTitles selected=$selectedRelatedObjectId label="manager.navigationMenus.form.navigationMenuItemSeriesMessage" translate=false}
	{else}
		{translate key="manager.navigationMenus.form.navigationMenuItem.series.noItems"}
	{/if}
{/fbvFormSection}

