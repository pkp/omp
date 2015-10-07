{**
 * templates/frontend/objects/file_downloadButton.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a download button for a file. Applies file type classes and
 *  determines whether a purchase price should be displayed
 *
 * @uses $availableFile MonographFile|MonographArtworkFile|SupplementaryFile The
 *       file to display a download link for
 *}
{if $availableFile->getDocumentType()==$smarty.const.DOCUMENT_TYPE_PDF}
	{url|assign:downloadUrl op="view" path=$publishedMonograph->getId()|to_array:$representationId:$availableFile->getFileIdAndRevision()}
{else}
	{url|assign:downloadUrl op="download" path=$publishedMonograph->getId()|to_array:$representationId:$availableFile->getFileIdAndRevision()}
{/if}
<a href="{$downloadUrl}" class="cmp_file_download_button {$availableFile->getDocumentType()}{if $availableFile->getDirectSalesPrice()} purchase_required{/if}">
	{if $availableFile->getDirectSalesPrice()}
		{translate key="payment.directSales.purchase" amount=$availableFile->getDirectSalesPrice() currency=$currency}
	{else}
		{translate key="payment.directSales.download"}
	{/if}
</a>
