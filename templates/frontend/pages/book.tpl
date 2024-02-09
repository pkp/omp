{**
 * templates/frontend/pages/book.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display the page which represents a single book.
 *
 * @uses $representationId int Publication format ID
 * @uses $availableFiles array List of available MonographFiles
 * @uses $publishedSubmission PublishedSubmission The published submission object.
 * @uses $series Series The series this monograph is assigned to, if any.
 *
 * @hook Templates::Catalog::Book::Footer::PageFooter []
 *}
{if $isChapterRequest}
	{assign var=pageTitle value=$chapter->getLocalizedFullTitle()}
{else}
	{assign var=pageTitle value=$publishedSubmission->getCurrentPublication()->getLocalizedFullTitle()}
{/if}

{include file="frontend/components/header.tpl" pageTitleTranslated=$pageTitle}

<div class="page page_book">
	{if $isChapterRequest}
		{* Display chapter details *}
		{include file="frontend/objects/chapter.tpl" monograph=$publishedSubmission}
	{else}
		{* Display book details *}
		{include file="frontend/objects/monograph_full.tpl" monograph=$publishedSubmission}
	{/if}

	{call_hook name="Templates::Catalog::Book::Footer::PageFooter"}
</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
