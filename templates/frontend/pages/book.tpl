{**
 * templates/frontend/pages/book.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page which represents a single book.
 *
 * @uses $representationId int Publication format ID
 * @uses $availableFiles array List of available MonographFiles
 * @uses $publishedSubmission PublishedSubmission The published monograph object.
 * @uses $series Series The series this monograph is assigned to, if any.
 *}
{include file="frontend/components/header.tpl" pageTitleTranslated=$publishedSubmission->getLocalizedFullTitle()}

<div class="page page_book">

	{* Display book details *}
	{include file="frontend/objects/monograph_full.tpl" monograph=$publishedSubmission}

	{* Display Dublin Core metadata *}
	{include file="frontend/objects/monograph_dublinCore.tpl" monograph=$publishedSubmission}

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
