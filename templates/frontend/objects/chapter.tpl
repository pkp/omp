{**
 * templates/frontend/objects/chapter.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display a full view of a chapter. Expected to be primary object on
 *  the page.
 *
 * Many presses will need to add custom data to this object, either through
 * plugins which attach to hooks on the page or by editing the template
 * themselves. In order to facilitate this, a flexible layout markup pattern has
 * been implemented. If followed, plugins and other content can provide markup
 * in a way that will render consistently with other items on the page. This
 * pattern is used in the .main_entry column and the .entry_details column. It
 * consists of the following:
 *
 * <!-- Wrapper class which provides proper spacing between components -->
 * <div class="item">
 *     <!-- Title/value combination -->
 *     <div class="label">Abstract</div>
 *     <div class="value">Value</div>
 * </div>
 *
 * All styling should be applied by class name, so that titles may use heading
 * elements (eg, <h3>) or any element required.
 *
 * <!-- Example: component with multiple title/value combinations -->
 * <div class="item">
 *     <div class="sub_item">
 *         <div class="label">DOI</div>
 *         <div class="value">12345678</div>
 *     </div>
 *     <div class="sub_item">
 *         <div class="label">Published Date</div>
 *         <div class="value">2015-01-01</div>
 *     </div>
 * </div>
 *
 * <!-- Example: component with no title -->
 * <div class="item">
 *     <div class="value">Whatever you'd like</div>
 * </div>
 *
 * Core components are produced manually below, but can also be added via
 * plugins using the hooks provided:
 *
 * Templates::Catalog::Chapter::Main
 * Templates::Catalog::Chapter::Details
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
 *}
<div class="obj_monograph_full obj_chapter">

	{* Notification that this is an old version *}
	{if $currentPublication->getID() !== $publication->getId() && !$hasVersionChanged}
		<div class="cmp_notification notice">
			{capture assign="latestVersionUrl"}{url page="catalog" op="book" path=$monograph->getBestId()|to_array:"chapter":$chapter->getSourceChapterId()}{/capture}

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

			{* DOI (requires plugin) *}
			{foreach from=$pubIdPlugins item=pubIdPlugin}
				{if $pubIdPlugin->getPubIdType() != 'doi'}
					{continue}
				{/if}
				{assign var=pubId value=$chapter->getStoredPubId($pubIdPlugin->getPubIdType())}

				{if $pubId}
					{assign var="doiUrl" value=$pubIdPlugin->getResolvingURL($currentPress->getId(), $pubId)|escape}
					<div class="item doi">
						<span class="label">
							{translate key="plugins.pubIds.doi.readerDisplayName"}
						</span>
						<span class="value">
							<a href="{$doiUrl}">
								{$doiUrl}
							</a>
						</span>
					</div>
				{/if}
			{/foreach}

			{* Keywords *}
			{if !empty($publication->getLocalizedData('keywords'))}
			<div class="item keywords">
				<h2 class="label">
					{capture assign=translatedKeywords}{translate key="common.keywords"}{/capture}
					{translate key="semicolon" label=$translatedKeywords}
				</h2>
				<span class="value">
					{foreach name="keywords" from=$publication->getLocalizedData('keywords') item=keyword}
						{$keyword|escape}{if !$smarty.foreach.keywords.last}, {/if}
					{/foreach}
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
									{if $author->getLocalizedAffiliation()}
										{capture assign="authorName"}{$author->getFullName()|escape}{/capture}
										{capture assign="authorAffiliation"}<span class="affiliation">{$author->getLocalizedAffiliation()|escape}</span>{/capture}
										{translate key="submission.authorWithAffiliation" name=$authorName|escape affiliation=$authorAffiliation|escape}
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

			{* References *}
			{if $citations || $publication->getData('citationsRaw')}
				<div class="item references">
					<h2 class="label">
						{translate key="submission.citations"}
					</h2>
					<div class="value">
						{if $citations}
							{foreach from=$citations item=$citation}
								<p>{$citation->getCitationWithLinks()|strip_unsafe_html}</p>
							{/foreach}
						{else}
							{$publication->getData('citationsRaw')|escape|nl2br}
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
			{pluck_files assign=objectFiles files=$availableFiles by="chapter" value=$chapter->getId()}
			{include file="frontend/components/objectFiles.tpl"}


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
								{$publication->getLocalizedFullTitle()|escape}
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
			{if $publication->getData('licenseUrl')}
				<div class="item license">
					<h2 class="label">
						{translate key="submission.license"}
					</h2>
					{if $ccLicenseBadge}
						{$ccLicenseBadge}
					{else}
						<a href="{$publication->getData('licenseUrl')|escape}">
							{translate key="submission.license"}
						</a>
					{/if}
				</div>
			{/if}

			{* Publication formats *}
			{if count($publicationFormats)}
				{foreach from=$publicationFormats item="publicationFormat"}
					{if $publicationFormat->getIsApproved()}

						{assign var=identificationCodes value=$publicationFormat->getIdentificationCodes()}
						{assign var=identificationCodes value=$identificationCodes->toArray()}
						{assign var=publicationDates value=$publicationFormat->getPublicationDates()}
						{assign var=publicationDates value=$publicationDates->toArray()}
						{assign var=hasPubId value=false}
						{foreach from=$pubIdPlugins item=pubIdPlugin}
							{assign var=pubIdType value=$pubIdPlugin->getPubIdType()}
							{if $publicationFormat->getStoredPubId($pubIdType)}
								{assign var=hasPubId value=true}
								{break}
							{/if}
						{/foreach}

						{* Skip if we don't have any information to print about this pub format *}
						{if !$identificationCodes && !$publicationDates && !$hasPubId && !$publicationFormat->getPhysicalFormat()}
							{continue}
						{/if}

						<div class="item publication_format">

							{* Only add the format-specific heading if multiple publication formats exist *}
							{if count($publicationFormats) > 1}
								<h2 class="pkp_screen_reader">
									{assign var=publicationFormatName value=$publicationFormat->getLocalizedName()}
									{translate key="monograph.publicationFormatDetails" format=$publicationFormatName|escape}
								</h2>

								<div class="sub_item item_heading format">
									<div class="label">
										{$publicationFormat->getLocalizedName()|escape}
									</div>
								</div>
							{else}
								<h2 class="pkp_screen_reader">
									{translate key="monograph.miscellaneousDetails"}
								</h2>
							{/if}


							{* DOI's and other identification codes *}
							{if $identificationCodes}
								{foreach from=$identificationCodes item=identificationCode}
									<div class="sub_item identification_code">
										<h3 class="label">
											{$identificationCode->getNameForONIXCode()|escape}
										</h3>
										<div class="value">
											{$identificationCode->getValue()|escape}
										</div>
									</div>
								{/foreach}
							{/if}

							{* Dates of publication *}
							{if $publicationDates}
								{foreach from=$publicationDates item=publicationDate}
									<div class="sub_item date">
										<h3 class="label">
											{$publicationDate->getNameForONIXCode()|escape}
										</h3>
										<div class="value">
											{assign var=dates value=$publicationDate->getReadableDates()}
											{* note: these dates have dateFormatShort applied to them in getReadableDates() if they need it *}
											{if $publicationDate->isFreeText() || $dates|@count == 1}
												{$dates[0]|escape}
											{else}
												{* @todo the &mdash; ought to be translateable *}
												{$dates[0]|escape}&mdash;{$dates[1]|escape}
											{/if}
											{if $publicationDate->isHijriCalendar()}
												<div class="hijri">
													{translate key="common.dateHijri"}
												</div>
											{/if}
										</div>
									</div>
								{/foreach}
							{/if}

							{* PubIDs *}
							{foreach from=$pubIdPlugins item=pubIdPlugin}
								{assign var=pubIdType value=$pubIdPlugin->getPubIdType()}
								{assign var=storedPubId value=$publicationFormat->getStoredPubId($pubIdType)}
								{if $storedPubId != ''}
									<div class="sub_item pubid {$publicationFormat->getId()|escape}">
										<h2 class="label">
											{$pubIdType}
										</h2>
										<div class="value">
											{$storedPubId|escape}
										</div>
									</div>
								{/if}
							{/foreach}

							{* Physical dimensions *}
							{if $publicationFormat->getPhysicalFormat()}
								<div class="sub_item dimensions">
									<h2 class="label">
										{translate key="monograph.publicationFormat.productDimensions"}
									</h2>
									<div class="value">
										{$publicationFormat->getDimensions()|escape}
									</div>
								</div>
							{/if}
						</div>
					{/if}
				{/foreach}
			{/if}

			{call_hook name="Templates::Catalog::Chapter::Details"}

		</div><!-- .details -->
	</div><!-- .row -->

</div><!-- .obj_monograph_full -->
