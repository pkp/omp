{**
 * templates/frontend/objects/chapter.tpl
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display a full view of a chapter. Expected to be primary object on
 *  the page.
 *
 * Core components are produced manually below, but can also be added via
 * plugins using the hooks provided:
 *
 * @hook Templates::Catalog::Chapter::Main []
 * @hook Templates::Catalog::Chapter::Details []
 *
 * @uses $currentPress Press The press currently being viewed
 * @uses $monograph Monograph The monograph to be displayed
 * @uses $publication Publication The publication (version) that is being displayed
 * @uses $currentPublication Publication The latest published version of this monograph
 * @uses $dateFormatShort string String defining date format that is passed to
 *       smarty template function
 * @uses $series Series The series this monograph is assigned to, if any.
 * @uses $publicationFormats array List of PublicationFormat objects to display
 * @uses $availableFiles array List of available MonographFiles
 * @uses $ccLicenseBadge string An HTML string containing a CC license image and
 *       text. Only appears when license URL matches a known CC license.
 * @uses $chapter Chapter The chapter to be displayed.
 * @uses $chapterAuthors array List of authors associated with this chapter.
 * @uses $sourceChapter Chapter Earliest published version of the requested chapter.
 * @uses $datePublished	date Date this chapter was published.
 * @uses $firstDatePublished date Earliest chapter publishing date.
 * @uses $chapterPublicationIds array List of publication ids containing this chapter.
 *
 *}
<div class="obj_monograph_full obj_chapter">

	{* Notification that this is an old version *}
	{if $currentPublication->getId() !== $publication->getId()}
		<div class="cmp_notification notice">
			{if $currentPublication->getID()|in_array:$chapterPublicationIds}
				{capture assign="latestVersionUrl"}{url page="catalog" op="book" path=$monograph->getBestId()|to_array:"chapter":$chapter->getSourceChapterId()}{/capture}
			{else}
				{capture assign="latestVersionUrl"}{url page="catalog" op="book" path=$monograph->getBestId()}{/capture}
			{/if}

			{translate key="submission.outdatedVersion"
				datePublished=$publication->getData('datePublished')|date_format:$dateFormatShort
				urlRecentVersion=$latestVersionUrl|escape
			}
		</div>
	{/if}

	<h1 class="title">
		{$chapter->getLocalizedFullTitle()|escape}
	</h1>

	<div class="row">
		<div class="main_entry">

			{* Author list *}
			{include file="frontend/components/authors.tpl" authors=$chapterAuthors}

			{* DOIs *}
			{assign var=doiObject value=$chapter->getData('doiObject')}
			{if $doiObject}
				{assign var="doiUrl" value=$doiObject->getData('resolvingUrl')|escape}
				<div class="item doi">
					<span class="label">
						{translate key="doi.readerDisplayName"}
					</span>
					<span class="value">
						<a href="{$doiUrl}">
							{$doiUrl}
						</a>
					</span>
				</div>
			{/if}

			{* Abstract *}
			<div class="item abstract">
				<h2 class="label">
					{translate key="submission.synopsis"}
				</h2>
				<div class="value">
					{$chapter->getLocalizedData('abstract')|strip_unsafe_html}
				</div>
			</div>

			{call_hook name="Templates::Catalog::Chapter::Main"}

			{* Determine if any authors have biographies to display *}
			{assign var="hasBiographies" value=0}
			{foreach from=$chapterAuthors item=author}
				{if $author->getLocalizedBiography()}
					{assign var="hasBiographies" value=$hasBiographies+1}
				{/if}
			{/foreach}
			{if $hasBiographies}
				<div class="item author_bios">
					<h2 class="label">
						{if $hasBiographies > 1}
							{translate key="submission.authorBiographies"}
						{else}
							{translate key="submission.authorBiography"}
						{/if}
					</h2>
					{foreach from=$chapterAuthors item=author}
						{if $author->getLocalizedBiography()}
							<div class="sub_item">
								<div class="label">
									{if $author->getLocalizedAffiliationNamesAsString()}
										{capture assign="authorName"}{$author->getFullName()|escape}{/capture}
										{capture assign="authorAffiliations"}<span class="affiliation">{$author->getLocalizedAffiliationNamesAsString(null, ', ')|escape}</span>{/capture}
										{translate key="submission.authorWithAffiliation" name=$authorName affiliation=$authorAffiliations}
									{else}
										{$author->getFullName()|escape}
									{/if}
								</div>
								<div class="value">
									{$author->getLocalizedBiography()|strip_unsafe_html}
								</div>
							</div>
						{/if}
					{/foreach}
				</div>
			{/if}

			{* Chapter references *}
			{if $chapterCitations || $chapter->getData('chapterCitationsRaw')}
				<div class="item references">
					<h2 class="label">
						{translate key="submission.citations"}
					</h2>
					<div class="value">
						{if $chapterCitations}
							{foreach from=$chapterCitations item=$chapterCitation}
								<p>{$chapterCitation->getCitationWithLinks()|strip_unsafe_html}</p>
							{/foreach}
						{else}
							{$chapter->getData('chapterCitationsRaw')|escape|nl2br}
						{/if}
					</div>
				</div>
			{/if}

		</div><!-- .main_entry -->

		<div class="entry_details">

			{* Cover image *}
			<div class="item cover">
				{if $publication->getId() === $currentPublication->getId()}
					<a href="{url page="catalog" op="book" path=$monograph->getId()}">
				{else}
					<a href="{url page="catalog" op="book" path=$monograph->getBestId()|to_array:"version":$publication->getId()}">
				{/if}

				{assign var="coverImage" value=$publication->getLocalizedData('coverImage')}
				<img
					src="{$publication->getLocalizedCoverImageThumbnailUrl($monograph->getData('contextId'))}"
					alt="{$coverImage.altText|escape|default:''}"
				>
				</a>
			</div>

			{* Any files of the requested chapter*}
			{pluck_files assign=chapterFiles files=$availableFiles by="chapter" value=$chapter->getId()}
			{if $chapterFiles|@count}
				<div class="item files">
					<h2 class="pkp_screen_reader">
						{translate key="submission.downloads"}
					</h2>
					{include file="frontend/components/publicationFormats.tpl" publicationFiles=$chapterFiles}
				</div>
			{/if}


			{* Monograph *}
			<div class="item monograph">
				<div class="sub_item">
					<h2 class="label">{translate key="chapter.volume"}</h2>
					<div class="value">
						{if $publication->getId() === $currentPublication->getId()}
							<a href="{url page="catalog" op="book" path=$monograph->getId()}">
						{else}
							<a href="{url page="catalog" op="book" path=$monograph->getBestId()|to_array:"version":$publication->getId()}">
						{/if}
								{$publication->getLocalizedFullTitle(null, 'html')|strip_unsafe_html}
							</a>
					</div>
				</div>
				{if $chapter->getPages()}
					<div class="sub_item">
						<h2 class="label">{translate key="chapter.pages"}</h2>
						<div class="value">
							{$chapter->getPages()|escape}
						</div>
					</div>
				{/if}
			</div>

			{* Publication Date *}
			{if $publication->getData('datePublished')}
				<div class="item date_published">
					<div class="sub_item">
						<h2 class="label">
							{if $publication->getData('datePublished')|date_format:$dateFormatShort > $smarty.now|date_format:$dateFormatShort}
								{translate key="catalog.forthcoming"}
							{else}
								{translate key="catalog.published"}
							{/if}
						</h2>
						<div class="value">
							{* If this is the original version *}
							{if $sourceChapter->getId() === $chapter->getId()}
								<span>{$firstDatePublished|date_format:$dateFormatLong}</span>
								{* If this is an updated version *}
							{else}
								<span>{translate key="submission.updatedOn" datePublished=$firstDatePublished|date_format:$dateFormatLong dateUpdated=$datePublished|date_format:$dateFormatLong}</span>
							{/if}
						</div>
					</div>
					{if count($monograph->getPublishedPublications()) > 1}
						<div class="sub_item versions">
							<h2 class="label">
								{translate key="submission.versions"}
							</h2>
							<ul class="value">
								{capture assign="versionCounter"}{count($monograph->getPublishedPublications())}{/capture}
								{capture assign="chapterCounter"}{count($chapterPublicationIds)}{/capture}
								{foreach from=array_reverse($monograph->getPublishedPublications()) item=iPublication}
									{capture assign="name"}{translate key="submission.versionIdentity" datePublished=$iPublication->getData('datePublished')|date_format:$dateFormatShort version=$iPublication->getData('version')}{/capture}
									<li>
										{capture}{$versionCounter--}{/capture}
										{if $iPublication->getId()|in_array:$chapterPublicationIds}
											{capture}{$chapterCounter--}{/capture}
											{if $chapterCounter === 0 && $versionCounter > 0}
												{capture assign="versionSuffix"}{translate key="submission.chapterCreated"}{/capture}
											{else}
												{capture assign="versionSuffix"}{/capture}
											{/if}
											{if $iPublication->getId() === $publication->getId()}
												{$name}{$versionSuffix}
											{elseif $iPublication->getId() === $currentPublication->getId()}
												<a href="{url page="catalog" op="book" path=$monograph->getBestId()|to_array:"chapter":$chapter->getSourceChapterId()}">{$name}</a>{$versionSuffix}
											{else}
												<a href="{url page="catalog" op="book" path=$monograph->getBestId()|to_array:"version":$iPublication->getId():"chapter":$chapter->getSourceChapterId()}">{$name}</a>{$versionSuffix}
											{/if}
										{else}
											{if $chapterCounter > 0}
												{translate key="submission.withoutChapter" name=$name}
											{else}
												{$name}
											{/if}
										{/if}
									</li>
								{/foreach}
							</ul>
						</div>
					{/if}
				</div>
			{/if}

			{* Series *}
			{if $series}
				<div class="item series">
					<div class="sub_item">
						<h2 class="label">
							{translate key="series.series"}
						</h2>
						<div class="value">
							<a href="{url page="catalog" op="series" path=$series->getPath()}">
								{$series->getLocalizedFullTitle()|escape}
							</a>
						</div>
					</div>
					{if $series->getOnlineISSN()}
						<div class="sub_item">
							<h3 class="label">{translate key="catalog.manage.series.onlineIssn"}</h3>
							<div class="value">{$series->getOnlineISSN()|escape}</div>
						</div>
					{/if}
					{if $series->getPrintISSN()}
						<div class="sub_item">
							<h3 class="label">{translate key="catalog.manage.series.printIssn"}</h3>
							<div class="value">{$series->getPrintISSN()|escape}</div>
						</div>
					{/if}
				</div>
			{/if}

			{* Categories *}
			{if $categories}
				<div class="item categories">
					<h2 class="label">
						{translate key="catalog.categories"}
					</h2>
					<div class="value">
						<ul>
							{foreach from=$categories item="category"}
								<li>
									<a href="{url op="category" path=$category->getPath()}">
										{$category->getLocalizedTitle()|strip_unsafe_html}
									</a>
								</li>
							{/foreach}
						</ul>
					</div>
				</div>
			{/if}

			{* Copyright statement *}
			{if $publication->getData('copyrightYear') && $publication->getLocalizedData('copyrightHolder')}
				<div class="item copyright">
					{translate|escape key="submission.copyrightStatement" copyrightYear=$publication->getData('copyrightYear') copyrightHolder=$publication->getLocalizedData('copyrightHolder')}
				</div>
			{/if}

			{* License *}
			{if $chapter->getData('licenseUrl') || $publication->getData('licenseUrl')}
				<div class="item license">
					<h2 class="label">
						{translate key="submission.license"}
					</h2>
					{if $ccLicenseBadge}
						{$ccLicenseBadge}
					{elseif $chapter->getData('licenseUrl')}
						<a href="{$chapter->getData('licenseUrl')|escape}">
							{translate key="submission.license"}
						</a>
					{else}
						<a href="{$publication->getData('licenseUrl')|escape}">
							{translate key="submission.license"}
						</a>
					{/if}
				</div>
			{/if}

			{call_hook name="Templates::Catalog::Chapter::Details"}

		</div><!-- .details -->
	</div><!-- .row -->

</div><!-- .obj_monograph_full -->
