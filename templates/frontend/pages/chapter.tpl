{**
 * templates/frontend/pages/chapter.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page which represents a single chapter.
 *
 * @uses $representationId int Publication format ID
 * @uses $availableFiles array List of available MonographFiles
 * @uses $chapter Chapter The chapter object.
 *}
{include file="frontend/components/header.tpl" pageTitleTranslated=$chapter->getLocalizedFullTitle()}

<div class="page page_book">
	{* Display book details *}
	{include file="frontend/objects/chapter_full.tpl" monograph=$publishedSubmission}

	{call_hook name="Templates::Catalog::Chapter::Footer::PageFooter"}
</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
