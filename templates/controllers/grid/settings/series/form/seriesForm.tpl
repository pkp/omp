{**
 * templates/controllers/grid/settings/series/form/seriesForm.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Series form under press management.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#seriesForm').pkpHandler(
			'$.pkp.controllers.form.FileUploadFormHandler',
			{ldelim}
				publishChangeEvents: ['updateSidebar'],
				$uploader: $('#plupload'),
				uploaderOptions: {ldelim}
					uploadUrl: {url|json_encode op="uploadImage" escape=false},
					baseUrl: {$baseUrl|json_encode},
					filters: {ldelim}
						mime_types : [
							{ldelim} title : "Image files", extensions : "jpg,jpeg,png,svg" {rdelim}
						]
					{rdelim}
				{rdelim}
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="seriesForm" method="post" action="{url router=PKP\core\PKPApplication::ROUTE_COMPONENT component="grid.settings.series.SeriesGridHandler" op="updateSeries" seriesId=$seriesId}">
	{csrf}
	<input type="hidden" name="seriesId" value="{$seriesId|escape}"/>
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="seriesFormNotification"}

	{fbvFormArea id="file"}
		{fbvFormSection title="monograph.coverImage"}
			{include file="controllers/fileUploadContainer.tpl" id="plupload"}
		{/fbvFormSection}
	{/fbvFormArea}
	{* Container for uploaded file *}
	<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />

	{if $image}
		{capture assign="altTitle"}{translate key="submission.currentCoverImage"}{/capture}
		<img class="pkp_helpers_container_center" height="{$image.thumbnailHeight}" width="{$image.thumbnailWidth}" src="{url router=PKP\core\PKPApplication::ROUTE_PAGE page="catalog" op="thumbnail" type="series" id=$seriesId}" alt="{$altTitle|escape}" />
	{/if}

	{fbvFormArea id="seriesInfo"}
		<div class="pkp_helpers_clear">
			{fbvFormSection for="title" title="common.prefix" inline="true" size=$fbvStyles.size.SMALL}
				{fbvElement label="common.prefixAndTitle.tip" type="text" multilingual=true name="prefix" id="prefix" value=$prefix}
			{/fbvFormSection}
			{fbvFormSection for="title" for="title" title="common.title" inline="true" size=$fbvStyles.size.LARGE required=true}
				{fbvElement type="text" multilingual=true name="title" id="title" value=$title required=true}
			{/fbvFormSection}
		</div>

		{fbvFormSection for="subtitle" title="common.subtitle" for="subtitle"}
			{fbvElement type="text" multilingual=true name="subtitle" id="subtitle" value=$subtitle maxlength="255"}
		{/fbvFormSection}

		{fbvFormSection title="series.path" required=true for="urlPath"}
			{fbvElement type="text" id="urlPath" label=$instruct subLabelTranslate=false value=$urlPath maxlength="32"}
		{/fbvFormSection}

		{fbvFormSection title="common.description" for="description"}
		 	{fbvElement type="textarea" multilingual=true id="description" value=$description rich=true}
		{/fbvFormSection}

		{fbvFormSection list="true"}
			{fbvElement type="checkbox" id="isInactive" value=1 checked=$isInactive label="manager.sections.form.deactivateSection"}
			{fbvElement type="checkbox" id="editorRestricted" value=1 label="manager.series.restricted" checked=$editorRestricted}
		{/fbvFormSection}

		{fbvFormSection label="catalog.manage.series.issn" description="manager.setup.issnDescription"}
			{fbvElement type="text" id="onlineIssn" label="catalog.manage.series.onlineIssn" value=$onlineIssn maxlength="16" size=$fbvStyles.size.MEDIUM inline=true}
			{fbvElement type="text" id="printIssn" label="catalog.manage.series.printIssn" value=$printIssn maxlength="16" size=$fbvStyles.size.MEDIUM inline=true}
		{/fbvFormSection}

		{fbvFormSection label="catalog.sortBy" description="catalog.sortBy.seriesDescription" for="sortOption"}
			{fbvElement type="select" id="sortOption" from=$sortOptions selected=$sortOption translate=false}
		{/fbvFormSection}

		{fbvFormSection list=true title="manager.sections.form.assignEditors"}
		<div>{translate key="manager.sections.form.assignEditors.description"}</div>
		{foreach from=$assignableUserGroups item="assignableUserGroup"}
			{assign var="role" value=$assignableUserGroup.userGroup->getLocalizedData('name')}
			{assign var="userGroupId" value=$assignableUserGroup.userGroup->id}
			{foreach from=$assignableUserGroup.users item=$username key="id"}
				{fbvElement
					type="checkbox"
					id="subEditors[{$userGroupId}][]"
					value=$id
					checked=(isset($subeditorUserGroups[$id]) && in_array($userGroupId, $subeditorUserGroups[$id]))
					label={translate key="manager.sections.form.assignEditorAs" name=$username role=$role}
					translate=false
				}
			{/foreach}
		{/foreach}
		{/fbvFormSection}

		{if count($allCategories)}
			{fbvFormSection list=true title="grid.category.categories"}
				{foreach from=$allCategories item="category" key="id"}
					{fbvElement type="checkbox" id="categories[]" value=$id checked=in_array($id, $selectedCategories) label=$category|escape translate=false}
				{/foreach}
			{/fbvFormSection}
		{/if}

	{/fbvFormArea}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
	{fbvFormButtons submitText="common.save"}
</form>
