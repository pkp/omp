{**
 * templates/controllers/grid/users/chapter/form/chapterForm.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
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

	{fbvFormSection}
		<!--  Chapter Contributors -->
		{url|assign:chapterAuthorUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.ChapterAuthorListbuilderHandler" op="fetch" submissionId=$submissionId chapterId=$chapterId escape=false}
		{load_url_in_div id="chapterAuthorContainer" url=$chapterAuthorUrl}
	{/fbvFormSection}

	{fbvFormSection}
		<!-- Chapter Files -->
		{url|assign:chapterFilesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.files.ChapterFilesListbuilderHandler" op="fetch" submissionId=$submissionId chapterId=$chapterId escape=false}
		{load_url_in_div id="chapterFilesContainer" url=$chapterFilesUrl}
	{/fbvFormSection}

	{fbvFormButtons submitText="common.save"}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
