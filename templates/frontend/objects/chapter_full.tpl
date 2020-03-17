{**
 * templates/frontend/objects/chapter_full.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
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
 * @uses $Chapter Chapter The chapter to be displayed
 * @uses $publication Publication The publication (version) that is being displayed
 * @uses $firstPublication Publication The original publication (version) of this monograph
 * @uses $currentPublication Publication The latest published version of this monograph
 * @uses $authors DaoResultFactory List of authors associated with this monograph
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



	<h1 class="title">
		{$chapter->getLocalizedFullTitle()|escape}
	</h1>

	<div class="row">
		<div class="main_entry">

			{* Author list *}
			<div class="item authors">
				<h2 class="pkp_screen_reader">
					{translate key="submission.authors"}
				</h2>

				{assign var="authors" value=$chapter->getAuthors()}

				{* Show short author lists on multiple lines *}
				{iterate from=authors item=author}
						<div class="sub_item">
							<div class="label">
									{$author->getFullName()|escape}
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
					{/iterate}
			</div>

			{* DOI (requires plugin) *}
			{foreach from=$pubIdPlugins item=pubIdPlugin}
				{if $pubIdPlugin->getPubIdType() != 'doi'}
					{continue}
				{/if}
				{assign var=pubId value=$chapter->getStoredPubId($pubIdPlugin->getPubIdType())}
				{if $pubId}
					{assign var="doiUrl" value=$pubIdPlugin->getResolvingURL($currentPress->getId(), $pubId)|escape}
					<div class="item doi">
						<span class="label">{translate key="plugins.pubIds.doi.readerDisplayName"}</span>
						<span class="value"><a href="{$doiUrl}">{$doiUrl}</a></span>
					</div>
				{/if}
			{/foreach}

			{* Abstract *}
			<div class="item abstract">
				<h3 class="label">
					{translate key="submission.synopsis"}
				</h3>
				<div class="value">
					{$chapter->getLocalizedData('abstract')|strip_unsafe_html}
				</div>
			</div>

			{call_hook name="Templates::Catalog::Chapter::Main"}

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

			{* Publication *}
			{if $publication}
				<div class="item series">
					<div class="sub_item">
						<div class="label">
							{translate key="submission.publication"}
						</div>
						<div class="value">
							<a href="{url page="catalog" op="book" path=$publication->getData('submissionId')}">
								{$publication->getLocalizedFullTitle()|escape}
							</a>
						</div>
					</div>
				</div>
			{/if}			

			{* Display any files that are assigned to this chapter *}
			{pluck_files assign="chapterFiles" files=$availableFiles by="chapter" value=$chapter->getId()}
			{if $chapterFiles|@count}
				<div class="item files">

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
							<div class="pub_format_{$publicationFormatId|escape} pub_format_single">
								{include file="frontend/components/downloadLink.tpl" downloadFile=$file monograph=$monograph publicationFormat=$format currency=$currency useFilename=$useFileName}
							</div>
						{/foreach}
					{/foreach}
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

			{call_hook name="Templates::Catalog::Chapter::Details"}

		</div><!-- .details -->
	</div><!-- .row -->

</div><!-- .obj_monograph_full -->
