{**
 * templates/catalog/book/book.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing book view in the catalog.
 *
 * Available data:
 *  $representationId int Publication format ID
 *  $availableFiles array Array of available MonographFiles
 *  $publishedMonograph PublishedMonograph The published monograph object.
 *}
{include file="common/header.tpl" suppressPageTitle=true pageTitleTranslated=$publishedMonograph->getLocalizedFullTitle()}

<div class="pkp_catalog_book">

{url|assign:bookImageLinkUrl router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="cover" submissionId=$publishedMonograph->getId()}
{include file="catalog/book/bookSpecs.tpl" bookImageLinkUrl=$bookImageLinkUrl}

{include file="catalog/book/bookInfo.tpl"}

{include file="catalog/book/googlescholar.tpl"}
{include file="catalog/book/dublincore.tpl"}

</div><!-- pkp_catalog_book -->

{include file="common/footer.tpl"}
