{**
 * templates/frontend/pages/book.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page which represents a single book.
 *
 * @uses $representationId int Publication format ID
 * @uses $availableFiles array List of available MonographFiles
 * @uses $publishedMonograph PublishedMonograph The published monograph object.
 * @uses $series Series The series this monograph is assigned to, if any.
 *}
{include file="frontend/components/header.tpl" pageTitleTranslated=$publishedMonograph->getLocalizedFullTitle()}

<div class="page page_book">

	{* Display book details *}
	{include file="frontend/objects/monograph_full.tpl" monograph=$publishedMonograph}

	{* Display Dublin Core metadata *}
	{include file="frontend/objects/monograph_dublinCore.tpl" monograph=$publishedMonograph}

	{* Display Google Scholar metadata *}
	{include file="frontend/objects/monograph_googleScholar.tpl" monograph=$publishedMonograph}

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
