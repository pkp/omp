{**
 * templates/frontend/objects/monographFile_googleScholar.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Print Google Scholar metadata for a monograph
 *
 * @uses $monograph Monograph The monograph this file belongs to
 * @uses $publicationFormat PublictionFormat The publication format this file belongs to
 * @uses $submissionFile SubmissionFile The submission file to be presented
 * @uses $chapter Chapter The (optional) chapter associated with this file
 *}
<meta name="gs_meta_revision" content="1.1" />
<meta name="citation_issn" content="{$issn|strip_tags|escape}"/>

{* Get the ISBN *}
{assign var=identificationCodes value=$publicationFormat->getIdentificationCodes()}
{foreach from=$identificationCodes->toArray() item=identificationCode}
	{if $identificationCode->getCode() == "02" || $identificationCode->getCode() == "15"}{* ONIX codes for ISBN-10 or ISBN-13 *}
		<meta name="citation_isbn" content="{$identificationCode->getValue()|escape}"/>
	{/if}
{/foreach}

{* Authors *}
{if $chapter}
	{* Only include metadata for authors associated with this chapter *}
	{assign var=authors value=$chapter->getAuthors()}
{else}
	{* Include metadata for all authors of the submission *}
	{assign var=authors value=$monograph->getAuthors()}
{/if}
{foreach name="authors" from=$publishedMonograph->getAuthors() item=author}
	<meta name="citation_author" content="{$author->getFirstName()|escape}{if $author->getMiddleName() != ""} {$author->getMiddleName()|escape}{/if} {$author->getLastName()|escape}"/>
	{if $author->getLocalizedAffiliation() != ""}
		<meta name="citation_author_institution" content="{$author->getLocalizedAffiliation()|strip_tags|escape}"/>
	{/if}
{/foreach}

<meta name="citation_title" content="{$publishedMonograph->getLocalizedTitle()|strip_tags|escape}"/>

{if is_a($publishedMonograph, 'PublishedMonograph') && $publishedMonograph->getDatePublished()}
	<meta name="citation_publication_date" content="{$publishedMonograph->getDatePublished()|date_format:"%Y/%m/%d"}"/>
{/if}

<meta name="citation_publisher" content="{$currentPress->getSetting('publisher')|escape}"/>

{url|assign:downloadUrl op="download" path=$publishedMonograph->getId()|to_array:$publicationFormat->getId():$submissionFile->getFileIdAndRevision()}
<meta name="citation_pdf_url" content="{$downloadUrl}"/>

{foreach from=$submissionKeywords key=keywordLocale item=languageKeywords}
	{foreach from=$languageKeywords item=keyword}
		<meta name="citation_keywords" xml:lang="{$keywordLocale|String_substr:0:2|escape}" content="{$keyword|escape}"/>
	{/foreach}
{/foreach}
