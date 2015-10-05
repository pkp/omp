{**
 * templates/submission/form/categories.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Include categories for submissions.
 *}
{if $categoriesExist}
	{if !$readOnly}
		{assign var="monographCategoriesContainer" value="monographCategoriesContainer-"|uniqid|escape}
		<div id="{$monographCategoriesContainer|escape}">
			{url|assign:monographCategoriesUrl router=$smarty.const.ROUTE_COMPONENT component="submission.CategoriesListbuilderHandler" op="fetch" submissionId=$submissionId readOnly=$readOnly escape=false}
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
