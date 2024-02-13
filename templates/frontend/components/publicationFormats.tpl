{**
 * templates/frontend/components/publicationFormats.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display non chapter files of a book or files of a requested chapter
 *
 * @uses $monograph Monograph The monograph to be displayed
 * @uses $publication Publication The publication (version) that is being displayed
 * @uses $publicationFiles array if a chapter is requested files of the requested chapter, else non chapter files
 * @uses $publicationFormats array List of PublicationFormat objects to display
 * @uses $currency Currency The Currency object representing the press's currency, if configured.
 * @uses $remotePublicationFormats array List of PublicationFormat objects which
 *       have remote URLs associated
 * @uses $isChapterRequest bool Is true, if a chapter landing page is requested and not a monograph landing page
 *}

{foreach from=$publicationFormats item=format}
	{assign var=publicationFormatId value=$format->getId()}

	{* Remote resources *}
	{if $format->getData('urlRemote') && !$isChapterRequest}
		{* Only one resource allowed per format, so mimic single-file-download *}
		<div class="pub_format_{$publicationFormatId|escape} pub_format_remote">
			<a href="{$format->getData('urlRemote')|escape}" target="_blank" class="remote_resource">
				{$format->getLocalizedName()|escape}
			</a>
		</div>

	{* File downloads *}
	{else}
		{pluck_files assign=pubFormatFiles files=$publicationFiles by="publicationFormat" value=$format->getId()}

		{* Use a simplified presentation if only one file exists *}
		{if $pubFormatFiles|@count == 1}
			<div class="pub_format_{$publicationFormatId|escape} pub_format_single">
				{foreach from=$pubFormatFiles item=file}
					{include file="frontend/components/downloadLink.tpl" downloadFile=$file monograph=$monograph publication=$publication publicationFormat=$format currency=$currency}
				{/foreach}
			</div>

			{* Use an itemized presentation if multiple files exist *}
		{elseif $pubFormatFiles|@count > 1}
			<div class="pub_format_{$publicationFormatId|escape}">
				<span class="label">
					{$format->getLocalizedName()|escape}
				</span>
				<span class="value">
					<ul>
						{foreach from=$pubFormatFiles item=file}
							<li>
								<span class="name">
									{$file->getLocalizedData('name')|escape}
								</span>
								<span class="link">
									{include file="frontend/components/downloadLink.tpl" downloadFile=$file monograph=$monograph publication=$publication publicationFormat=$format currency=$currency useFilename=true}
								</span>
							</li>
						{/foreach}
					</ul>
				</span><!-- .value -->
			</div>
		{/if}
	{/if}
{/foreach}{* Publication formats loop *}
