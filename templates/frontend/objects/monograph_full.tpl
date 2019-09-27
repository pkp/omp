{**
 * templates/frontend/objects/monograph_full.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
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
 * Templates::Catalog::Book::Main
 * Templates::Catalog::Book::Details
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
 * @uses $licenseUrl string The URL which provides license information.
 * @uses $ccLicenseBadge string An HTML string containing a CC license image and
 *       text. Only appears when license URL matches a known CC license.
 *}
<div class="obj_monograph_full">

	{* Notification that this is an old version *}
	{if $currentPublication->getID() !== $publication->getId()}
		<div class="cmp_notification notice">
			{capture assign="latestVersionUrl"}{url page="catalog" op="book" path=$monograph->getBestId()}{/capture}
			{translate key="submission.outdatedVersion"
				datePublished=$publication->getData('datePublished')|date_format:$dateFormatShort
				urlRecentVersion=$latestVersionUrl|escape
			}
		</div>
	{/if}

	<h1 class="title">
		{$publication->getLocalizedFullTitle()|escape}
	</h1>

	<div class="row">
		<div class="main_entry">

			{* Author list *}
			<div class="item authors">
				<h2 class="pkp_screen_reader">
					{translate key="submission.authors"}
				</h2>

				{assign var="authors" value=$publication->getData('authors')}

				{* Only show editors for edited volumes *}
				{if $monograph->getWorkType() == $smarty.const.WORK_TYPE_EDITED_VOLUME && $editors|@count}
					{assign var="authors" value=$editors}
					{assign var="identifyAsEditors" value=true}
				{/if}

				{* Show short author lists on multiple lines *}
				{if $authors|@count < 5}
					{foreach from=$authors item=author}
						<div class="sub_item">
							<div class="label">
								{if $identifyAsEditors}
									{translate key="submission.editorName" editorName=$author->getFullName()|escape}
								{else}
									{$author->getFullName()|escape}
								{/if}
							</div>
							{if $author->getLocalizedAffiliation()}
								<div class="value">
									{$author->getLocalizedAffiliation()|escape}
								</div>
							{/if}
							{if $author->getOrcid()}
								<span class="orcid">
									<a href="{$author->getOrcid()|escape}" target="_blank">
										{$author->getOrcid()|escape}
									</a>
								</span>
							{/if}
						</div>
					{/foreach}

				{* Show long author lists on one line *}
				{else}
					{foreach name="authors" from=$authors item=author}
						{* strip removes excess white-space which creates gaps between separators *}
						{strip}
							{if $author->getLocalizedAffiliation()}
								{if $identifyAsEditors}
									{capture assign="authorName"}<span class="label">{translate key="submission.editorName" editorName=$author->getFullName()|escape}</span>{/capture}
								{else}
									{capture assign="authorName"}<span class="label">{$author->getFullName()|escape}</span>{/capture}
								{/if}
								{capture assign="authorAffiliation"}<span class="value">{$author->getLocalizedAffiliation()|escape}</span>{/capture}
								{translate key="submission.authorWithAffiliation" name=$authorName affiliation=$authorAffiliation}
							{else}
								<span class="label">{$author->getFullName()|escape}</span>
							{/if}
							{if !$smarty.foreach.authors.last}
								{translate key="submission.authorListSeparator"}
							{/if}
						{/strip}
					{/foreach}
				{/if}
			</div>

			{* DOI (requires plugin) *}
			{foreach from=$pubIdPlugins item=pubIdPlugin}
				{if $pubIdPlugin->getPubIdType() != 'doi'}
					{continue}
				{/if}
				{assign var=pubId value=$monograph->getStoredPubId($pubIdPlugin->getPubIdType())}
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
				<span class="label">
					{capture assign=translatedKeywords}{translate key="common.keywords"}{/capture}
					{translate key="semicolon" label=$translatedKeywords}
				</span>
				<span class="value">
					{foreach name="keywords" from=$publication->getLocalizedData('keywords') item=keyword}
						{$keyword|escape}{if !$smarty.foreach.keywords.last}, {/if}
					{/foreach}
				</span>
			</div>
			{/if}

			{* Abstract *}
			<div class="item abstract">
				<h3 class="label">
					{translate key="submission.synopsis"}
				</h3>
				<div class="value">
					{$publication->getLocalizedData('abstract')|strip_unsafe_html}
				</div>
			</div>

			{* Chapters *}
			{if $chapters|@count}
				<div class="item chapters">
					<h3 class="pkp_screen_reader">
						{translate key="submission.chapters"}
					</h3>
					<ul>
						{foreach from=$chapters item=chapter}
							{assign var=chapterId value=$chapter->getId()}
							<li>
								<div class="title">
									{$chapter->getLocalizedTitle()|escape}
									{if $chapter->getLocalizedSubtitle() != ''}
										<div class="subtitle">
											{$chapter->getLocalizedSubtitle()|escape}
										</div>
									{/if}
								</div>
								{assign var=chapterAuthors value=$chapter->getAuthorNamesAsString()}
								{if $authorString != $chapterAuthors}
									<div class="authors">
										{$chapterAuthors|escape}
									</div>
								{/if}

								{* DOI (requires plugin) *}
								{foreach from=$pubIdPlugins item=pubIdPlugin}
									{if $pubIdPlugin->getPubIdType() != 'doi'}
										{continue}
									{/if}
									{assign var=pubId value=$chapter->getStoredPubId($pubIdPlugin->getPubIdType())}
									{if $pubId}
										{assign var="doiUrl" value=$pubIdPlugin->getResolvingURL($currentPress->getId(), $pubId)|escape}
										<div class="doi">{translate key="plugins.pubIds.doi.readerDisplayName"} <a href="{$doiUrl}">{$doiUrl}</a></div>
									{/if}
								{/foreach}

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

			{* Determine if any authors have biographies to display *}
			{assign var="hasBiographies" value=0}
			{foreach from=$publication->getData('authors') item=author}
				{if $author->getLocalizedBiography()}
					{assign var="hasBiographies" value=$hasBiographies+1}
				{/if}
			{/foreach}
			{if $hasBiographies}
				<div class="item author_bios">
					<h3 class="label">
						{if $hasBiographies > 1}
							{translate key="submission.authorBiographies"}
						{else}
							{translate key="submission.authorBiography"}
						{/if}
					</h3>
					{foreach from=$publication->getData('authors') item=author}
						{if $author->getLocalizedBiography()}
							<div class="sub_item">
								<div class="label">
									{if $author->getLocalizedAffiliation()}
										{capture assign="authorName"}{$author->getFullName()|escape}{/capture}
										{capture assign="authorAffiliation"}<span class="affiliation">{$author->getLocalizedAffiliation()|escape}</span>{/capture}
										{translate key="submission.authorWithAffiliation" name=$authorName affiliation=$authorAffiliation}
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
					<h3 class="label">
						{translate key="submission.citations"}
					</h3>
					<div class="value">
						{if $citations}
							{foreach from=$citations item=$citation}
								<p>{$citation->getCitationWithLinks()|strip_unsafe_html}</p>
							{/foreach}
						{else}
							{$publication->getData('citationsRaw')|nl2br}
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
					alt="{$coverImage.altText|escape|default:'null'}"
				>
			</div>

			{* Any non-chapter files and remote resources *}
			{pluck_files assign=nonChapterFiles files=$availableFiles by="chapter" value=0}
			{if $nonChapterFiles|@count || $remotePublicationFormats|@count}
				<div class="item files">
					{foreach from=$publicationFormats item=format}
						{assign var=publicationFormatId value=$format->getId()}

						{* Remote resources *}
						{if $format->getRemoteUrl()}
							{* Only one resource allowed per format, so mimic single-file-download *}
							<div class="pub_format_{$publicationFormatId|escape} pub_format_remote">
								<a href="{$format->getRemoteURL()|escape}" target="_blank" class="remote_resource">
									{$format->getLocalizedName()|escape}
								</a>
							</div>

						{* File downloads *}
						{else}

							{* Only display files that haven't been displayed in a chapter *}
							{pluck_files assign=pubFormatFiles files=$nonChapterFiles by="publicationFormat" value=$format->getId()}

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
														{$file->getLocalizedName()|escape}
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
				</div>
			{/if}

			{* Publication Date *}
			{if $publication->getData('datePublished')}
				<div class="item date_published">
					<div class="sub_item">
						<div class="label">
							{if $publication->getData('datePublished')|date_format:$dateFormatShort > $smarty.now|date_format:$dateFormatShort}
								{translate key="catalog.forthcoming"}
							{else}
								{translate key="catalog.published"}
							{/if}
						</div>
						<div class="value">
							{* If this is the original version *}
							{if $firstPublication->getID() === $publication->getId()}
								<span>{$firstPublication->getData('datePublished')|date_format:$dateFormatLong}</span>
							{* If this is an updated version *}
							{else}
								<span>{translate key="submission.updatedOn" datePublished=$firstPublication->getData('datePublished')|date_format:$dateFormatLong dateUpdated=$publication->getData('datePublished')|date_format:$dateFormatLong}</span>
							{/if}
						</div>
					</div>
					{if count($monograph->getPublishedPublications()) > 1}
						<div class="sub_item versions">
							<div class="label">
								{translate key="submission.versions"}
							</div>
							<ul class="value">
								{foreach from=array_reverse($monograph->getPublishedPublications()) item=iPublication}
									{capture assign="name"}{translate key="submission.versionIdentity" datePublished=$iPublication->getData('datePublished')|date_format:$dateFormatShort versionId=$iPublication->getId()}{/capture}
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
					{/if}
				</div>
			{/if}

			{* Series *}
			{if $series}
				<div class="item series">
					<div class="sub_item">
						<div class="label">
							{translate key="series.series"}
						</div>
						<div class="value">
							<a href="{url page="catalog" op="series" path=$series->getPath()}">
								{$series->getLocalizedFullTitle()|escape}
							</a>
						</div>
					</div>
					{if $series->getOnlineISSN()}
						<div class="sub_item">
							<div class="label">{translate key="catalog.manage.series.onlineIssn"}</div>
							<div class="value">{$series->getOnlineISSN()|escape}</div>
						</div>
					{/if}
					{if $series->getPrintISSN()}
						<div class="sub_item">
							<div class="label">{translate key="catalog.manage.series.printIssn"}</div>
							<div class="value">{$series->getPrintISSN()|escape}</div>
						</div>
					{/if}
				</div>
			{/if}

			{* Categories *}
			{if $categories}
				<div class="item categories">
					<div class="label">
						{translate key="catalog.categories"}
					</div>
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
								<h3 class="pkp_screen_reader">
									{assign var=publicationFormatName value=$publicationFormat->getLocalizedName()}
									{translate key="monograph.publicationFormatDetails" format=$publicationFormatName|escape}
								</h3>

								<div class="sub_item item_heading format">
									<div class="label">
										{$publicationFormat->getLocalizedName()|escape}
									</div>
								</div>
							{else}
								<h3 class="pkp_screen_reader">
									{translate key="monograph.miscellaneousDetails"}
								</h3>
							{/if}


							{* DOI's and other identification codes *}
							{if $identificationCodes}
								{foreach from=$identificationCodes item=identificationCode}
									<div class="sub_item identification_code">
										<div class="label">
											{$identificationCode->getNameForONIXCode()|escape}
										</div>
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
										<div class="label">
											{$publicationDate->getNameForONIXCode()|escape}
										</div>
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
										<div class="label">
											{$pubIdType}
										</div>
										<div class="value">
											{$storedPubId|escape}
										</div>
									</div>
								{/if}
							{/foreach}

							{* Physical dimensions *}
							{if $publicationFormat->getPhysicalFormat()}
								<div class="sub_item dimensions">
									<div class="label">
										{translate key="monograph.publicationFormat.productDimensions"}
									</div>
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
