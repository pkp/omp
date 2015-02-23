{**
 * templates/catalog/book/bookFiles.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays a book file list with download/payment links in the public catalog.
 *
 * --------------------------------------------------------------------
 * new by Simon A. Frank: content and download in same tab
 * --------------------------------------------------------------------
 *}
{foreach from=$availableFiles[$publicationFormatId] item=availableFile}{* There will be at most one of these *}
	{if $availableFile->getData('chapterId') == $chapterId }
	<li>		
		<div class="publicationFormatLink" style="float:left">			 
			{if $availableFile->getDocumentType()==$smarty.const.DOCUMENT_TYPE_PDF}
				{url|assign:downloadUrl op="view" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}
			{else}
				{url|assign:downloadUrl op="download" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}
			{/if}
			
			{url|assign:downloadUrl op="download" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}
			{*
			Version with payment-info and icons
			<a href="{$downloadUrl}"><span title="{$availableFile->getDocumentType()|upper|escape}" class="sprite {$availableFile->getDocumentType()|escape}"></span>{if $availableFile->getDirectSalesPrice()}{translate key="payment.directSales.purchase amount=$availableFile->getDirectSalesPrice() currency=$currency}{else}{translate key="payment.directSales.download"} ({$availableFile->getDocumentType()|upper|escape})<span title="{translate key="monograph.accessLogoOpen.altText"}" class="sprite openaccess"></span>{/if}</a>
			*}
			{* without payment-info and openaccess-icon *}
			<a href="{$downloadUrl}"><span title="{$availableFile->getDocumentType()|upper|escape}" class="sprite {$availableFile->getDocumentType()|escape}"></span></a>
		</div>
	</li>
	{/if}
{/foreach}
