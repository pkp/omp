{**
 * templates/catalog/book/bookInfo.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the information pane of a public-facing book view in the catalog.
 *}

<script type="text/javascript">
	// Attach the tab handler.
	$(function() {ldelim}
		$('#bookInfoTabs').pkpHandler(
			'$.pkp.controllers.TabHandler'
		);
	{rdelim});
</script>

<div class="bookInfo">
	<h3>{$publishedMonograph->getLocalizedTitle()|strip_unsafe_html}</h3>
	<div class="authorName">{$publishedMonograph->getAuthorString()}</div>

	<div id="bookInfoTabs">
		<ul>
			<li><a href="#abstractTab">{translate key="submission.synopsis"}</a></li>
			{if $availableFiles|@count != 0}<li><a href="#downloadTab">{translate key="submission.download"}</a></li>{/if}
			<li><a href="#sharingTab">{translate key="submission.sharing"}</a></li>
		</ul>

		<div id="abstractTab">
			{$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html}
		</div>
		{if $availableFiles|@count != 0}
		<div id="downloadTab">
			{assign var=publicationFormats value=$publishedMonograph->getPublicationFormats()}
			{foreach from=$publicationFormats item=publicationFormat}
				{assign var=publicationFormatId value=$publicationFormat->getId()}
				{if $availableFiles[$publicationFormatId]}
					<div class="publicationFormatDownload" id="publicationFormat-download-{$publicationFormatId|escape}">
						{$publicationFormat->getLocalizedTitle()|escape}
						<ul>
							{foreach from=$availableFiles[$publicationFormatId] item=availableFile}
								<li>
									<div class="publicationFormatName">{$availableFile->getLocalizedName()|escape}</div>
									<div class="publicationFormatLink">
										<a href="{url op="download" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}">{if $availableFile->getDirectSalesPrice()}{translate key="payment.directSales.purchase amount=$availableFile->getDirectSalesPrice() currency=$currentPress->getSetting('pressCurrency')}{else}{translate key="payment.directSales.download"}{/if}</a>
									</div>
								</li>
							{/foreach}
						</ul>
					</div>
				{/if}
			{/foreach}
		</div>
		{/if}
		<div id="sharingTab">
			{call_hook name="Templates::Catalog::Book::BookInfo::Sharing"}

			{foreach from=$blocks item=block key=blockKey}
				<div id="socialMediaBlock{$blockKey|escape}" class="pkp_helpers_clear">
					{$block}
				</div>
			{/foreach}
		</div>
	</div>
</div>
