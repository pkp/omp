{**
 * templates/controllers/grid/settings/category/form/categoryForm.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to edit or create a category
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#categoryForm').pkpHandler(
			'$.pkp.controllers.form.FileUploadFormHandler',
			{ldelim}
				publishChangeEvents: ['updateSidebar'],
				$uploader: $('#plupload'),
				uploaderOptions: {ldelim}
					uploadUrl: {url|json_encode op="uploadImage" escape=false},
					baseUrl: {$baseUrl|json_encode}
				{rdelim}
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="categoryForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.category.CategoryCategoryGridHandler" op="updateCategory" categoryId=$categoryId}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="categoryFormNotification"}

	{fbvFormArea id="file"}
		{fbvFormSection title="monograph.coverImage"}
			<div id="plupload"></div>
		{/fbvFormSection}
	{/fbvFormArea}
	{* Container for uploaded file *}
	<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />

	{if $image}
		{translate|assign:"altTitle" key="monograph.currentCoverImage"}
		<img class="pkp_helpers_container_center" height="{$image.thumbnailHeight}" width="{$image.thumbnailWidth}" src="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="thumbnail" type="category" id=$categoryId}" alt="{$altTitle|escape}" />
	{/if}

	{fbvFormArea id="categoryDetails"}

		<h3>{translate key="grid.category.categoryDetails"}</h3>

		{fbvFormSection title="grid.category.name" for="name" required="true" size=$fbvStyles.size.MEDIUM inline="true"}
			{fbvElement type="text" multilingual="true" name="name" value=$name id="name"}
		{/fbvFormSection}

		{fbvFormSection title="grid.category.parentCategory" for="context" inline="true" size=$fbvStyles.size.MEDIUM}
			{fbvElement type="select" id="parentId" from=$rootCategories selected=$parentId translate=false disabled=$cannotSelectChild}
		{/fbvFormSection}

		{fbvFormSection title="grid.category.description" for="context"}
			{fbvElement type="textarea" multilingual="true" id="description" value=$description rich=true}
		{/fbvFormSection}

		{fbvFormSection label="catalog.sortBy" description="catalog.sortBy.categoryDescription" for="sortOption"}
			{fbvElement type="select" id="sortOption" from=$sortOptions selected=$sortOption translate=false}
		{/fbvFormSection}

		{fbvFormSection title="grid.category.path" required=true for="path"}
			{fbvElement type="text" id="path" value=$path size=$smarty.const.SMALL maxlength="32"}
			{url|assign:"sampleUrl" router=$smarty.const.ROUTE_PAGE page="catalog" op="category" path="path"}
			{** FIXME: is this class instruct still the right one? **}
			<span class="instruct">{translate key="grid.category.urlWillBe" sampleUrl=$sampleUrl}</span>
		{/fbvFormSection}
		{fbvFormButtons}

	{/fbvFormArea}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
