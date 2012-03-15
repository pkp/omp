{**
 * templates/submission/form/seriesAndCategories.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Include series placement and categories for submissions. This template is 
 * included in:
 *
 * templates/submission/form/step1.tpl
 * controllers/modals/submissionMetadata/form/catalogEntryFormForm.tpl
 * controllers/modals/submissionMetadata/form/submissionMetadataViewForm.tpl
 *}
<!-- Submission Placement -->
{if count($seriesOptions) > 1} {* only display the series picker if there are series configured for this press *}
	{fbvFormSection label="submission.submit.placement" description="submission.submit.placement.seriesDescription"}
		{fbvElement type="select" id="seriesId" from=$seriesOptions selected=$seriesId translate=false disabled=$readOnly}
	{/fbvFormSection}

	{fbvFormSection label="submission.submit.seriesPosition" description="submission.submit.placement.seriesPositionDescription"}
		{fbvElement type="text" id="seriesPosition" name="seriesPosition" value=$seriesPosition|escape maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
{/if}

{if $categoriesExist}
	{if !$readOnly}
		<h3 class="pkp_grid_title">{translate key="submission.submit.placement.categories"}</h3>
		<p class="pkp_grid_description">{translate key="submission.submit.placement.categoriesDescription"}</p>
			{assign var="monographCategoriesContainer" value="monographCategoriesContainer-"|uniqid|escape}
			<div id={$monographCategoriesContainer}>
				{url|assign:monographCategoriesUrl router=$smarty.const.ROUTE_COMPONENT component="submission.CategoriesListbuilderHandler" op="fetch" monographId=$monographId readOnly=$readOnly escape=false}
				{load_url_in_div id=$monographCategoriesContainer url=$monographCategoriesUrl}
			</div>
	{else}
		{if count($assignedCategories) > 0}
			{fbvFormSection title="grid.category.categories" list=true}
				{foreach from=$assignedCategories item=category}
					<li>{$category->getLocalizedTitle()}</li>
				{/foreach}
			{/fbvFormSection}

		{/if}
	{/if}
{/if}