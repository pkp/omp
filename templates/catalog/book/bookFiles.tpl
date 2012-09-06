{**
 * templates/catalog/book/bookFiles.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays a book file list with download/payment links in the public catalog.
 *}
{foreach from=$availableFiles[$publicationFormatId] item=availableFile}{* There will be at most one of these *}
	<li>
		<div class="publicationFormatName">{$availableFile->getLocalizedName()|escape}</div>
		<div class="publicationFormatLink">
			<a href="{url op="download" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}">{if $availableFile->getDirectSalesPrice()}{translate key="payment.directSales.purchase amount=$availableFile->getDirectSalesPrice() currency=$currency}{else}{translate key="payment.directSales.download"}{/if}</a>
		</div>
	</li>
{/foreach}
