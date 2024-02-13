{**
 * templates/frontend/components/download_link.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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
{if $publication->getId() == $monograph->getData('currentPublicationId')}
	{capture assign=downloadUrl}{url op="view" path=$monograph->getBestId()|to_array:$publicationFormatId:$downloadFile->getBestId()}{/capture}
{else}
	{capture assign=downloadUrl}{url op="view" path=$monograph->getBestId()|to_array:"version":$publication->getId():$publicationFormatId:$downloadFile->getBestId()}{/capture}
{/if}

{* Display the download link *}
<a href="{$downloadUrl}" class="cmp_download_link">
	{if $useFilename}
		{$downloadFile->getLocalizedData('name')}
	{else}
		{if $downloadFile->getDirectSalesPrice() && $currency}{$downloadFile->getDirectSalesPrice()}
			{translate key="payment.directSales.purchase" format=$publicationFormat->getLocalizedName() amount=$downloadFile->getDirectSalesPrice() currency=$currency->getLetterCode()}
		{else}
			{$publicationFormat->getLocalizedName()}
		{/if}
	{/if}
</a>
