{**
 * templates/catalog/book/bookFiles.tpl
 *
 * Copyright (c) 2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays a book file list with download/payment links in the public catalog.
 *}
{if $chapters|@count != 0}
	{foreach from=$chapters item=chapter}
		<p>
		<strong>{$chapter->getLocalizedTitle()}</strong>
		{if $chapter->getLocalizedSubtitle() != '' }<br />{$chapter->getLocalizedSubtitle()}{/if}
		{assign var=chapterAuthors value=$chapter->getAuthorNamesAsString()}
		{if $publishedMonograph->getAuthorString() != $chapterAuthors}
			<div class="authorName">{$chapterAuthors}</div>
		{/if}
		{foreach from=$publicationFormats item=publicationFormat}
			{assign var=publicationFormatId value=$publicationFormat->getId()}
			{if $publicationFormat->getIsAvailable() && $availableFiles[$publicationFormatId]}
				{foreach from=$availableFiles[$publicationFormatId] item=availableFile}
					{if $availableFile->getData('chapterId') == $chapter->getId() }
						<li>
							<div class="publicationFormatName">{$availableFile->getLocalizedName()|escape} ({$publicationFormat->getLocalizedName()|escape})</div>
							<div class="publicationFormatLink">
								{if $availableFile->getDocumentType()==$smarty.const.DOCUMENT_TYPE_PDF}
									{url|assign:downloadUrl op="view" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}
								{else}
									{url|assign:downloadUrl op="download" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}
								{/if}
								<a href="{$downloadUrl}"><span title="{$availableFile->getDocumentType()|upper|escape}" class="sprite {$availableFile->getDocumentType()|escape}"></span>{if $availableFile->getDirectSalesPrice()}{translate key="payment.directSales.purchase amount=$availableFile->getDirectSalesPrice() currency=$currency}{else}{translate key="payment.directSales.download"}<span title="{translate key="monograph.accessLogoOpen.altText"}" class="sprite openaccess"></span>{/if}</a>
							</div>
						</li>
					{/if}
				{/foreach}
			{/if}
		{/foreach}
		</p>
	{/foreach}
{/if}
{foreach from=$publicationFormats item=publicationFormat}
	{assign var=publicationFormatId value=$publicationFormat->getId()}
	{if $publicationFormat->getIsAvailable() && $availableFiles[$publicationFormatId]}
		{foreach from=$availableFiles[$publicationFormatId] item=availableFile}
			{if $availableFile->getData('chapterId') == "" }
				<li>
					<div class="publicationFormatName">{$availableFile->getLocalizedName()|escape}</div>
					<div class="publicationFormatLink">
						{if $availableFile->getDocumentType()==$smarty.const.DOCUMENT_TYPE_PDF}
							{url|assign:downloadUrl op="view" path=$publishedMonograph->getId()|to_array:$publicationFormat:$availableFile->getFileIdAndRevision()}
						{else}
							{url|assign:downloadUrl op="download" path=$publishedMonograph->getId()|to_array:$publicationFormat:$availableFile->getFileIdAndRevision()}
						{/if}
						<a href="{$downloadUrl}"><span title="{$availableFile->getDocumentType()|upper|escape}" class="sprite {$availableFile->getDocumentType()|escape}"></span>{if $availableFile->getDirectSalesPrice()}{translate key="payment.directSales.purchase amount=$availableFile->getDirectSalesPrice() currency=$currency}{else}{translate key="payment.directSales.download"}<span title="{translate key="monograph.accessLogoOpen.altText"}" class="sprite openaccess"></span>{/if}</a>
					</div>
			</li>
			{/if}
		{/foreach}
	{/if}
{/foreach}
