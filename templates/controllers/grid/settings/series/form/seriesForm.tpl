{**
 * templates/controllers/grid/settings/series/form/seriesForm.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
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
					uploadUrl: '{url|escape:javascript op="uploadImage"}',
					baseUrl: '{$baseUrl|escape:javascript}'
				{rdelim}
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="seriesForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.series.SeriesGridHandler" op="updateSeries" seriesId=$seriesId}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="seriesFormNotification"}

	{if $categoryCount == 0}
		<span class="pkp_form_error"><p>{translate key="manager.series.noCategories"}</p></span>
	{/if}

	{if $seriesEditorCount == 0}
		<span class="pkp_form_error"><p>{translate key="manager.series.noSeriesEditors"}</p></span>
	{/if}

	{fbvFormArea id="file"}
		{fbvFormSection title="monograph.coverImage"}
			{include file="controllers/fileUploadContainer.tpl" id="plupload"}
		{/fbvFormSection}
	{/fbvFormArea}
	{* Container for uploaded file *}
	<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />

	{if $image}
		{translate|assign:"altTitle" key="monograph.currentCoverImage"}
		<img class="pkp_helpers_container_center" height="{$image.thumbnailHeight}" width="{$image.thumbnailWidth}" src="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="thumbnail" type="series" id=$seriesId}" alt="{$altTitle|escape}" />
	{/if}

	{fbvFormArea id="seriesInfo"}
		{fbvFormSection for="title" required=true description="common.prefixAndTitle.tip" title="manager.series.seriesTitle"}
			{fbvElement type="text" multilingual=true id="prefix" label="common.prefix" value=$prefix maxlength="32" size=$fbvStyles.size.SMALL inline=true}
			{fbvElement type="text" multilingual=true id="title" label="common.title" value=$title maxlength="80" size=$fbvStyles.size.LARGE inline=true}
		{/fbvFormSection}

		{fbvFormSection for="subtitle" title="common.subtitle"}
			{fbvElement type="text" multilingual=true name="subtitle" id="subtitle" value=$subtitle maxlength="255"}
		{/fbvFormSection}

		{fbvFormSection title="common.description" for="description"}
		 	{fbvElement type="textarea" multilingual=true id="description" value=$description rich=true}
		{/fbvFormSection}

		{fbvFormSection list="true"}
			{fbvElement type="checkbox" id="restricted" value=1 label="manager.series.restricted" checked=$restricted}
		{/fbvFormSection}

		<input type="hidden" name="seriesId" value="{$seriesId|escape}"/>
		{fbvFormSection for="context" inline=true size=$fbvStyles.size.MEDIUM}
			{if $categoryCount > 0}
				<div id="seriesCategoriesContainer">
					{url|assign:seriesCategoriesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.CategoriesListbuilderHandler" op="fetch" seriesId=$seriesId escape=false}
					{load_url_in_div id="seriesCategoriesContainer" url=$seriesCategoriesUrl}
				</div>
			{/if}
		{/fbvFormSection}

			{fbvFormSection for="context" inline=true size=$fbvStyles.size.MEDIUM}
				{if $seriesEditorCount > 0}{* only include the series editor listbuilder if there are series editors available *}
					<div id="seriesEditorsContainer">
						{url|assign:seriesEditorsUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.SeriesEditorsListbuilderHandler" op="fetch" seriesId=$seriesId escape=false}
						{load_url_in_div id="seriesEditorsContainer" url=$seriesEditorsUrl}
					</div>
				{/if}
			{/fbvFormSection}

		{capture assign="instruct"}
			{url|assign:"sampleUrl" router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path="Path"}
			{translate key="grid.series.urlWillBe" sampleUrl=$sampleUrl}
		{/capture}

		{fbvFormSection title="series.path" required=true description=$instruct translate=false for="path"}
			{fbvElement type="text" id="path" value=$path size=$smarty.const.SMALL maxlength="32"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons submitText="common.save"}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
