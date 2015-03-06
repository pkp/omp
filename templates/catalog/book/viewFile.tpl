{**
 * templates/catalog/book/viewFile.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing book view in the catalog.
 *
 * Available data:
 *  $availableFiles array Array of available MonographFiles
 *  $publishedMonograph PublishedMonograph The published monograph object.
 *  $viewableFileContent string The viewable file content (HTML)
 *}
{include file="common/header.tpl" pageTitleTranslated=$publishedMonograph->getLocalizedFullTitle()}

<div class="pkp_catalog_bookFile">
	{$viewableFileContent}
</div><!-- pkp_catalog_bookFile -->

{include file="common/footer.tpl"}
