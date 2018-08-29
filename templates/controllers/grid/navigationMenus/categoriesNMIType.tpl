{**
 * templates/controllers/grid/navigationMenus/categoriesNMIType.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Custom categories NMI Type edit form part
 *}
{fbvFormSection id="NMI_TYPE_CATEGORY" class="NMI_TYPE_CUSTOM_EDIT" title="manager.navigationMenus.form.navigationMenuItem.category" for="categorySelect"}
	{if count($navigationMenuItemCategoryTitles) gt 0}
		{fbvElement type="select" id="relatedCategoryId" required=true from=$navigationMenuItemCategoryTitles selected=$selectedRelatedObjectId label="manager.navigationMenus.form.navigationMenuItemCategoryMessage" translate=false}
	{else}
		{translate key="manager.navigationMenus.form.navigationMenuItem.category.noItems"}
	{/if}
{/fbvFormSection}

