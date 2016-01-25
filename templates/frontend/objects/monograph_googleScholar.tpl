{**
 * templates/frontend/objects/monograph_googleScholar.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Print Google Scholar metadata for a monograph
 *
 * @uses $monograph Monograph The monograph to be displayed
 *}

{**
 * Google Scholar tags should only be included when there is a single PDF
 * available. (See bug #8542.) Ensure that this is the case before
 * generating any tags.
 *}
{assign var=publicationFormats value=$publishedMonograph->getPublicationFormats(true)}
{assign var=viablePdfCount value=0}
{foreach from=$publicationFormats item=publicationFormat}
	{if $publicationFormat->getIsApproved() && !$publicationFormat->getPhysicalFormat()}
		{assign var="representationId" value=$publicationFormat->getId()}
		{if !empty($availableFiles.$representationId)}
			{assign var=publicationFormatFiles value=$availableFiles.$representationId}
			{foreach from=$availableFiles.$representationId item=availableFile}
				{if $availableFile->getDocumentType()==$smarty.const.DOCUMENT_TYPE_PDF}
					{assign var=viablePdf value=$availableFile}
					{assign var=viablePublicationFormat value=$publicationFormat}
					{assign var=viablePdfCount value=$viablePdfCount+1}
				{/if}
			{/foreach}
		{/if}
	{/if}
{/foreach}
{* We have now set $viablePdf, $viablePdfCount, $viablePublicationFormat *}

{if $viablePdfCount == 1}
	<meta name="gs_meta_revision" content="1.1" />
{foreach name="authors" from=$publishedMonograph->getAuthors() item=author}
	<meta name="citation_author" content="{$author->getFirstName()|escape}{if $author->getMiddleName() != ""} {$author->getMiddleName()|escape}{/if} {$author->getLastName()|escape}"/>
{/foreach}
	<meta name="citation_title" content="{$publishedMonograph->getLocalizedTitle()|strip_tags|escape}"/>

	{if is_a($publishedMonograph, 'PublishedMonograph') && $publishedMonograph->getDatePublished()}
		<meta name="citation_publication_date" content="{$publishedMonograph->getDatePublished()|date_format:"%Y/%m/%d"}"/>
	{/if}
	<meta name="citation_publisher" content="{$currentPress->getSetting('publisher')|escape}"/>

	{* Get the ISBN *}
	{assign var=identificationCodes value=$viablePublicationFormat->getIdentificationCodes()}
	{foreach from=$identificationCodes->toArray() item=identificationCode}
		{if $identificationCode->getCode() == "02" || $identificationCode->getCode() == "15"}{* ONIX codes for ISBN-10 or ISBN-13 *}
			<meta name="citation_isbn" content="{$identificationCode->getValue()|escape}"/>
		{/if}
	{/foreach}

	{url|assign:downloadUrl op="download" path=$publishedMonograph->getId()|to_array:$viablePublicationFormat->getId():$viablePdf->getFileIdAndRevision()}
	<meta name="citation_pdf_url" content="{$downloadUrl}"/>

	{if $publishedMonograph->getSubject(null)}{foreach from=$publishedMonograph->getSubject(null) key=metaLocale item=metaValue}
		{foreach from=$metaValue|explode:"; " item=gsKeyword}
			{if $gsKeyword}
				<meta name="citation_keywords" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$gsKeyword|escape}"/>
			{/if}
		{/foreach}
	{/foreach}{/if}
{/if}{* $viablePdfCount == 1 *}
