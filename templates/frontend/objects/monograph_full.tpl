{**
 * templates/frontend/objects/monograph_full.tpl
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display a full view of a monograph. Expected to be primary object on
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
 * @hook Templates::Catalog::Book::Main []
 * @hook Templates::Catalog::Book::Details []
 *
 * @uses $currentPress Press The press currently being viewed
 * @uses $monograph Monograph The monograph to be displayed
 * @uses $publication Publication The publication (version) that is being displayed
 * @uses $firstPublication Publication The original publication (version) of this monograph
 * @uses $currentPublication Publication The latest published version of this monograph
 * @uses $authors Array List of authors associated with this monograph
 * @uses $editors Array List of editors for this monograph if this is an edited
 *       volume. Otherwise empty.
 * @uses $dateFormatShort string String defining date format that is passed to
 *       smarty template function
 * @uses $series Series The series this monograph is assigned to, if any.
 * @uses $publicationFormats array List of PublicationFormat objects to display
 * @uses $remotePublicationFormats array List of PublicationFormat objects which
 *       have remote URLs associated
 * @uses $availableFiles array List of available MonographFiles
 * @uses $chapters array List of chapters in monograph. Associative array
 * @uses $sharingCode string Code snippet for a social sharing widget
 * @uses $blocks array List of HTML snippets to display block elements
 * @uses $currency Currency The Currency object representing the press's currency, if configured.
 * @uses $licenseTerms string License terms.
 * @uses $licenseUrl string The URL which provides license information.
 * @uses $ccLicenseBadge string An HTML string containing a CC license image and
 *       text. Only appears when license URL matches a known CC license.
 *}
<div class="obj_monograph_full">

	{* Indicate if this is only a preview *}
	{if $publication->getData('status') !== \PKP\submission\PKPSubmission::STATUS_PUBLISHED}
		<div class="cmp_notification notice">
			{capture assign="submissionUrl"}{url page="dashboard" op="editorial" workflowSubmissionId=$monograph->getId()}{/capture}
			{translate key="submission.viewingPreview" url=$submissionUrl}
		</div>
	{/if}

	{* Notification that this is an old version *}
	{if $currentPublication->getId() !== $publication->getId()}
		<div class="cmp_notification notice">
			{capture assign="latestVersionUrl"}{url page="catalog" op="book" path=$monograph->getBestId()}{/capture}
			{translate key="submission.outdatedVersion"
				datePublished=$publication->getData('datePublished')|date_format:$dateFormatShort
				urlRecentVersion=$latestVersionUrl|escape
			}
		</div>
	{/if}

	<h1 class="title">
		{$publication->getLocalizedFullTitle(null, 'html')|strip_unsafe_html}
	</h1>

	<div class="row">
		<div class="main_entry">

			{* Author list *}
			{include file="frontend/components/authors.tpl" authors=$publication->getData('authors')}

			{* DOIs *}
			{if $monographDoiObject}
				{assign var="doiUrl" value=$monographDoiObject->getData('resolvingUrl')|escape}
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
					{$publication->getLocalizedData('abstract')|strip_unsafe_html}
				</div>
			</div>

			{* Plain language summary *}
			{if $publication->getLocalizedData('plainLanguageSummary')}
				<div class="item abstract">
					<h2 class="label">
						{translate key="submission.plainLanguageSummary"}
					</h2>
					<div class="value">
						{$publication->getLocalizedData('plainLanguageSummary')|strip_unsafe_html}
					</div>
				</div>
			{/if}

			{* Chapters *}
			{if $chapters|@count}
				<div class="item chapters">
					<h2 class="pkp_screen_reader">
						{translate key="submission.chapters"}
					</h2>
					<ul>
						{foreach from=$chapters item=chapter}
							{assign var=chapterId value=$chapter->getId()}
							<li>
								{if $chapter->isPageEnabled()}
									{if $publication->getId() === $currentPublication->getId()}
										<a href="{url page="catalog" op="book" path=$monograph->getBestId()|to_array:"chapter":$chapter->getSourceChapterId()}">
									{else}
										<a href="{url page="catalog" op="book" path=$monograph->getBestId()|to_array:"version":$publication->getId():"chapter":$chapter->getSourceChapterId()}">
									{/if}
								{/if}
								<div class="title">
									{$chapter->getLocalizedTitle()|escape}
									{if $chapter->getLocalizedSubtitle() != ''}
										<div class="subtitle">
											{$chapter->getLocalizedSubtitle()|escape}
										</div>
									{/if}
								</div>
								{if $chapter->isPageEnabled()}
									</a>
								{/if}
								{assign var=chapterAuthors value=$chapter->getAuthorNamesAsString()}
								{if $authorString != $chapterAuthors}
									<div class="authors">
										{$chapterAuthors|escape}
									</div>
								{/if}

								{* DOI *}
								{assign var=chapterDoiObject value=$chapter->getData('doiObject')}
								{if $chapterDoiObject}
									{assign var="doiUrl" value=$chapterDoiObject->getData('resolvingUrl')|escape}
									<div class="doi">{translate key="doi.readerDisplayName"} <a href="{$doiUrl}">{$doiUrl}</a></div>
								{/if}

								{* Display any files that are assigned to this chapter *}
								{pluck_files assign="chapterFiles" files=$availableFiles by="chapter" value=$chapterId}
								{if $chapterFiles|@count}
									<div class="files">

										{* Display chapter files sorted by publication format so that they are ordered
										   consistently across all chapters. *}
										{foreach from=$publicationFormats item=format}
											{pluck_files assign="pubFormatFiles" files=$chapterFiles by="publicationFormat" value=$format->getId()}

											{foreach from=$pubFormatFiles item=file}

												{* Use the publication format name in the download link unless a pub format has multiple files *}
												{assign var=useFileName value=false}
												{if $pubFormatFiles|@count > 1}
													{assign var=useFileName value=true}
												{/if}

												{include file="frontend/components/downloadLink.tpl" downloadFile=$file monograph=$monograph publicationFormat=$format currency=$currency useFilename=$useFileName}
											{/foreach}
										{/foreach}
									</div>
								{/if}
							</li>
						{/foreach}
					</ul>
				</div>
			{/if}

			{call_hook name="Templates::Catalog::Book::Main"}

			{* Usage statistics chart *}
			{if $activeTheme && $activeTheme->getOption('displayStats') != 'none'}
				{$activeTheme->displayUsageStatsGraph($monograph->getId())}
				<section class="item downloads_chart">
					<h2 class="label">
						{translate key="plugins.themes.default.displayStats.downloads"}
					</h2>
					<div class="value">
						<canvas class="usageStatsGraph" data-object-type="Submission" data-object-id="{$monograph->getId()|escape}"></canvas>
						<div class="usageStatsUnavailable" data-object-type="Submission" data-object-id="{$monograph->getId()|escape}">
							{translate key="plugins.themes.default.displayStats.noStats"}
						</div>
					</div>
				</section>
			{/if}

			{* Determine if any authors have biographies to display *}
			{assign var="hasBiographies" value=0}
			{foreach from=$publication->getData('authors') item=author}
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
					{foreach from=$publication->getData('authors') item=author}
						{if $author->getLocalizedBiography()}
							<div class="sub_item">
								<div class="label">
									{if $author->getLocalizedAffiliationNamesAsString()}
										{capture assign="authorName"}{$author->getFullName()|escape}{/capture}
										{capture assign="authorAffiliations"} {$author->getLocalizedAffiliationNamesAsString(null, ', ')|escape} {/capture}
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

			{* References *}
			{if $citations || (string) $publication->getData('citationsRaw')}
				<div class="item references">
					<h2 class="label">
						{translate key="submission.citations"}
					</h2>
					<div class="value">
						{if $citations}
							{foreach from=$citations item=$citation}
								<p>{$citation->getRawCitationWithLinks()|strip_unsafe_html}</p>
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
				{assign var="coverImage" value=$publication->getLocalizedData('coverImage')}
				<img
						src="{$publication->getLocalizedCoverImageThumbnailUrl($monograph->getData('contextId'))}"
						alt="{$coverImage.altText|escape|default:''}"
				>
			</div>

			{* Any non-chapter files and remote resources *}
			{pluck_files assign=bookFiles files=$availableFiles by="chapter" value=0}
			{if $bookFiles|@count || $remotePublicationFormats|@count}
				<div class="item files">
					<h2 class="pkp_screen_reader">
						{translate key="submission.downloads"}
					</h2>
					{include file="frontend/components/publicationFormats.tpl" publicationFiles=$bookFiles}
				</div>
			{/if}

			{* Publication Date *}
			{if $publication->getData('datePublished')}
				<div class="item date_published">
					<div class="sub_item">
						<h2 class="label">
							{* Use Y-m-d to compare dates instead of customizable date formats (pkp-lib#10169) *}
							{if $publication->getData('datePublished')|date_format:"Y-m-d" > $smarty.now|date_format:"Y-m-d"}
								{translate key="catalog.forthcoming"}
							{else}
								{translate key="catalog.published"}
							{/if}
						</h2>
						<div class="value">
							{* If this is the original version *}
							{if $firstPublication->getId() === $publication->getId()}
								<span>{$firstPublication->getData('datePublished')|date_format:$dateFormatLong}</span>
								{* If this is an updated version *}
							{else}
								<span>{translate key="submission.updatedOn" datePublished=$firstPublication->getData('datePublished')|date_format:$dateFormatLong dateUpdated=$publication->getData('datePublished')|date_format:$dateFormatLong}</span>
							{/if}
						</div>
					</div>
					<div class="sub_item versions">
						<h2 class="label">
							{translate key="submission.versions"}
						</h2>
						<ul class="value">
							{foreach from=array_reverse($monograph->getPublishedPublications()) item=iPublication}
								{capture assign="name"}{translate key="submission.versionIdentity" datePublished=$iPublication->getData('datePublished')|date_format:$dateFormatShort version=$iPublication->getData('versionString')}{/capture}
								<li>
									{if $iPublication->getId() === $publication->getId()}
										{$name}
									{elseif $iPublication->getId() === $currentPublication->getId()}
										<a href="{url page="catalog" op="book" path=$monograph->getBestId()}">{$name}</a>
									{else}
										<a href="{url page="catalog" op="book" path=$monograph->getBestId()|to_array:"version":$iPublication->getId()}">{$name}</a>
									{/if}
								</li>
							{/foreach}
						</ul>
					</div>
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

			{* Data Availability Statement *}
			{if $publication->getLocalizedData('dataAvailability')}
				<div class="item dataAvailability">
					<h2 class="label">{translate key="submission.dataAvailability"}</h2>
					{$publication->getLocalizedData('dataAvailability')|strip_unsafe_html}
				</div>
			{/if}

			{* Funding Statement *}
			{if $publication->getLocalizedData('fundingStatement')}
				<div class="item fundingStatement"">
					<h2 class="label">{translate key="submission.fundingStatement"}</h2>
					{$publication->getLocalizedData('fundingStatement')|strip_unsafe_html}
				</div>
			{/if}

			{* Copyright statement *}
			{if $publication->getData('copyrightYear') && $publication->getLocalizedData('copyrightHolder')}
				<div class="item copyright">
					{translate|escape key="submission.copyrightStatement" copyrightYear=$publication->getData('copyrightYear') copyrightHolder=$publication->getLocalizedData('copyrightHolder')}
				</div>
			{/if}

			{* License *}
			{if $currentContext->getLocalizedData('licenseTerms') || $publication->getData('licenseUrl')}
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
					{$currentContext->getLocalizedData('licenseTerms')}
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
						{if $publicationFormat->getDoi()}
							{assign var=hasPubId value=true}
						{/if}

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

							{* DOIs *}
							{assign var=publicationFormatDoiObject value=$publicationFormat->getData('doiObject')}
							{if $publicationFormatDoiObject}
								{assign var="doiUrl" value=$publicationFormatDoiObject->getData('resolvingUrl')|escape}
								<div class="sub_item pubid {$publicationFormat->getId()|escape}">
									<h2 class="label">
										{translate key="doi.readerDisplayName"}
									</h2>
									<div class="value">
										<a href="{$doiUrl}">
											{$doiUrl}
										</a>
									</div>
								</div>
							{/if}

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

			{call_hook name="Templates::Catalog::Book::Details"}

		</div><!-- .details -->
	</div><!-- .row -->

</div><!-- .obj_monograph_full -->
