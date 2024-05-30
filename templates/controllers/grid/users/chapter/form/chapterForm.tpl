{**
 * templates/controllers/grid/users/chapter/form/chapterForm.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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

<form class="pkp_form" id="editChapterForm" method="post" action="{url router=PKP\core\PKPApplication::ROUTE_COMPONENT component="grid.users.chapter.ChapterGridHandler" op="updateChapter"}">
	{csrf}
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="publicationId" value="{$publicationId|escape}" />
	<input type="hidden" name="chapterId" value="{$chapterId|escape}" />

	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="chapterFormNotification"}

	{fbvFormSection title="common.title" for="title" required=true}
		{fbvElement type="text" name="title" id="title" value=$title maxlength="255" multilingual=true required=true}
	{/fbvFormSection}

	{fbvFormSection title="metadata.property.displayName.subTitle" for="subTitle"}
		{fbvElement type="text" name="subtitle" id="subtitle" value=$subtitle maxlength="255" multilingual=true}
	{/fbvFormSection}

	{fbvFormSection title="common.abstract" for="abstract"}
	    {fbvElement type="textarea" name="abstract" id="abstract" value=$abstract  rich="extended" multilingual=true}
	{/fbvFormSection}

	{fbvFormSection title="submission.chapter.pages" for="customExtras"}
	    {fbvElement type="text" id="pages" value=$pages inline=true size=$fbvStyles.size.LARGE}
	{/fbvFormSection}

	{if $enableChapterPublicationDates}
		{fbvFormSection title="publication.datePublished" for="customExtras"}
		{fbvElement type="text" id="datePublished" value=$datePublished inline=true size=$fbvStyles.size.LARGE  class="datepicker"}
		{/fbvFormSection}
	{/if}

	{if $submissionWorkType === 1}
		{fbvFormSection title="publication.chapter.licenseUrl" for="customExtras"}
		<div class="pkpFormField__description">{$chapterLicenseUrlDescription}</div>
		{fbvElement type="text" id="licenseUrl" value=$licenseUrl inline=true size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
	{/if}

	{fbvFormSection list=true title="publication.chapter.landingPage" for="customExtras"}
	{fbvElement type="checkbox" name="isPageEnabled" id="isPageEnabled" checked=$isPageEnabled|compare:true label="publication.chapter.hasLandingPage" value="1" translate="true"}
	{/fbvFormSection}

	{fbvFormSection list=true title="submission.submit.addAuthor"}
		{foreach from=$chapterAuthorOptions item="chapterAuthor" key="id"}
			{fbvElement type="checkbox" id="authors[]" value=$id checked=in_array($id, $selectedChapterAuthors) label=$chapterAuthor|escape translate=false}
		{/foreach}
	{/fbvFormSection}

	{fbvFormSection list=true title="submission.files"}
		{foreach from=$chapterFileOptions item="chapterFile" key="id"}
			{fbvElement type="checkbox" id="files[]" value=$id checked=in_array($id, $selectedChapterFiles) label=$chapterFile|escape translate=false}
		{/foreach}
	{/fbvFormSection}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
	{fbvFormButtons submitText="common.save"}
</form>
