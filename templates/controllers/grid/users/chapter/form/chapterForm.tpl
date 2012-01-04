{**
 * templates/controllers/grid/users/chapter/form/chapterForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
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
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="chapterId" value="{$chapterId|escape}" />

	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="editChapterFormNotification"}

	{fbvFormSection title="common.title" for="title"}
		{fbvElement type="text" name="title" id="title" value=$title maxlength="255" multilingual=true}
	{/fbvFormSection}


	<!--  Chapter Contributors -->
	{url|assign:chapterAuthorUrl router=$smarty.const.ROUTE_COMPONENT  component="listbuilder.users.ChapterAuthorListbuilderHandler" op="fetch" monographId=$monographId chapterId=$chapterId escape=false}
	{load_url_in_div id="chapterAuthorContainer" url=$chapterAuthorUrl}

	{fbvFormButtons submitText="common.save"}
</form>
