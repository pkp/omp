<!-- templates/controllers/grid/users/chapter/form/chapterForm.tpl -->

{**
 * chapters.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Chapters grid form
 *
 *}
{modal_title id="#editChapterForm" key="submission.chapter.addChapter" iconClass="fileManagement" canClose=1}

<form name="editChapterForm" id="editChapterForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.chapter.ChapterGridHandler" op="updateChapter"}">
{include file="common/formErrors.tpl"}

{fbvFormSection title="common.title" for="title"}
	{fbvElement type="text" name="title[en_US]" id="title" value=$title maxlength="255" size=$fbvStyles.size.LARGE}
{/fbvFormSection}

<input type="hidden" name="monographId" value="{$monographId|escape}" />
{if $chapterId}
	<input type="hidden" name="chapterId" value="{$chapterId|escape}" />

	{* only show the contributor list builder if the chapter already exists *}
	<!--  Chapter Contributors -->
	{** FIXME: can remove escape=false after fix of bug 5265 **}
	{url|assign:chapterContributorUrl router=$smarty.const.ROUTE_COMPONENT  component="listbuilder.users.ChapterContributorListbuilderHandler" op="fetch" monographId=$monographId chapterId=$chapterId escape=false}
	{load_url_in_div id="chapterContributorContainer" url=$chapterContributorUrl}
{/if}
</form>

{init_button_bar id="#editChapterForm" submitText="submission.chapter.addChapter"}
<!-- / templates/controllers/grid/users/chapter/form/chapterForm.tpl -->

