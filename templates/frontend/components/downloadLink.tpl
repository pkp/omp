{**
 * templates/frontend/components/download_link.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a download link for files
 *
 * @uses $downloadFile SubmissionFile The download file object
 * @uses $monograph Monograph The monograph this file is attached to
 * @uses $publicationFormat PublicationFormat The publication format this file is attached to
 * @uses $currency Currency The currency object
 * @uses $useFilename string Whether or not to use the file name in link. Default is false and pub format name is used
 *}

{assign var=publicationFormatId value=$publicationFormat->getBestId()}

{* Generate the download URL *}
{if $downloadFile->getDocumentType()==$smarty.const.DOCUMENT_TYPE_PDF}
	{url|assign:downloadUrl op="view" path=$monograph->getBestId()|to_array:$publicationFormatId:$downloadFile->getBestId()}
{else}
	{url|assign:downloadUrl op="download" path=$monograph->getBestId()|to_array:$publicationFormatId:$downloadFile->getBestId()}
{/if}

{* Display the download link *}
<a href="{$downloadUrl}" class="cmp_download_link {$downloadFile->getDocumentType()}">
	{if $useFilename}
		{$downloadFile->getLocalizedName()}
	{else}
		{if $downloadFile->getDirectSalesPrice()}
			{translate key="payment.directSales.purchase" format=$publicationFormat->getLocalizedName() amount=$currency->publicationFormat($downloadFile->getDirectSalesPrice()) currency=$currency->getCodeAlpha()}
		{else}
			{$publicationFormat->getLocalizedName()}
		{/if}
	{/if}
</a>
