{**
 * templates/controllers/grid/settings/category/form/categoryForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to edit or create a category
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#categoryForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="categoryForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.category.CategoryCategoryGridHandler" op="updateCategory" categoryId=$categoryId}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="categoryFormNotification"}

	{fbvFormArea id="categoryDetails"}
		<h3>{translate key="grid.category.categoryDetails"}</h3>
		{fbvFormSection title="grid.category.name" for="name" required="true"}
			{fbvElement type="text" multilingual="true" name="name" value=$name id="name"}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormSection title="grid.category.parentCategory" for="context"}
		<select name="parentId" class="field select">
			<option>{translate key="common.none"}</option>
			{iterate from=rootCategories item=rootCategory}
				{* Exclude the current category from the list of potential parents (time travel paradox) *}
				{if $rootCategory->getId() != $categoryId}
					<option value="{$rootCategory->getId()|escape}" {if $parentId == $rootCategory->getId()}selected="selected"{/if}>{$rootCategory->getLocalizedTitle()|escape}</option>
				{/if}
			{/iterate}
		</select>
	{/fbvFormSection}

	{fbvFormSection title="grid.category.description" for="context" required="true"}
	 	{fbvElement type="text" multilingual="true" id="description" value=$description maxlength="80"}
	{/fbvFormSection}

	{fbvFormButtons}
</form>
