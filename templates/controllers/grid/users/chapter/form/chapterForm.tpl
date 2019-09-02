{**
 * templates/controllers/grid/users/chapter/form/chapterForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Chapters grid form
 *
 *}

<script type="text/javascript">
	// Attach the Information Center handler.
	$(function() {ldelim}
		$('#editChapterForm').pkpHandler(
			'$.pkp.controllers.form.AjaxFormHandler'
		);
	{rdelim});
</script>

<form class="pkp_form" id="editChapterForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.chapter.ChapterGridHandler" op="updateChapter"}">
	{csrf}
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="chapterId" value="{$chapterId|escape}" />

	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="chapterFormNotification"}

	{fbvFormSection title="common.title" for="title" required=true}
		{fbvElement type="text" name="title" id="title" value=$title maxlength="255" multilingual=true required=true}
	{/fbvFormSection}

	{fbvFormSection title="metadata.property.displayName.subTitle" for="subTitle"}
		{fbvElement type="text" name="subtitle" id="subtitle" value=$subtitle maxlength="255" multilingual=true}
	{/fbvFormSection}

	{fbvFormSection title="submission.chapter.abstract" for="abstract"}
	    {fbvElement type="textarea" name="abstract" id="abstract" value=$abstract  rich="extended" multilingual=true}
	{/fbvFormSection}

	{fbvFormSection title="submission.chapter.pages" for="customExtras"}
	    {fbvElement type="text" id="pages" value=$pages inline=true size=$fbvStyles.size.LARGE}
	{/fbvFormSection}

	{if $enableChapterPublicationDates}
		{fbvFormSection title="submission.chapter.datePublished" for="customExtras"}
		{fbvElement type="text" id="datePublished" value=$datePublished inline=true size=$fbvStyles.size.LARGE  class="datepicker"}
		{/fbvFormSection}
	{/if}

	{fbvFormSection}
		<!--  Chapter Contributors -->
		{capture assign=chapterAuthorUrl}{url router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.ChapterAuthorListbuilderHandler" op="fetch" submissionId=$submissionId chapterId=$chapterId escape=false}{/capture}
		{load_url_in_div id="chapterAuthorContainer" url=$chapterAuthorUrl}
	{/fbvFormSection}

	{fbvFormSection}
		<!-- Chapter Files -->
		{capture assign=chapterFilesUrl}{url router=$smarty.const.ROUTE_COMPONENT component="listbuilder.files.ChapterFilesListbuilderHandler" op="fetch" submissionId=$submissionId chapterId=$chapterId escape=false}{/capture}
		{load_url_in_div id="chapterFilesContainer" url=$chapterFilesUrl}
	{/fbvFormSection}

	{fbvFormButtons submitText="common.save"}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
