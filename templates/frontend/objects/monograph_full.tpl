{**
 * templates/frontend/objects/monograph_full.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a full view of a monograph. Expected to be primary object on
 *  the page.
 *
 * @uses $currentPress Press The press currently being viewed
 * @uses $monograph Monograph The monograph to be displayed
 * @uses $dateFormatShort string String defining date format that is passed to
 *       smarty template function
 * @uses $series Series The series this monograph is assigned to, if any.
 * @uses $publicationFormats array List of PublicationFormat objects to display
 * @uses $availableFiles array List of available MonographFiles
 * @uses $chapters array List of chapters in monograph. Associative array
 * @uses $sharingCode string Code snippet for a social sharing widget
 * @uses $blocks array List of HTML snippets to display block elements
 * @uses $currency Currency The Currency object representing the press's currency, if configured.
 *}
<div class="obj_monograph_full">
	<h1 class="title">
		{$monograph->getLocalizedFullTitle()|escape}
	</h1>

	<div class="row">
		<div class="main_entry">

			<ul class="author_roles">
				{foreach from=$publishedMonograph->getAuthors() item=author}
					{if $author->getIncludeInBrowse()}
						<li>
							<span class="name">
								{$author->getFullName()|escape}
							</span>
							<span class="role">
								{$author->getLocalizedUserGroupName()|escape}
							</span>
							{assign var=biography value=$author->getLocalizedBiography()|strip_unsafe_html}
							{if $biography}
								<span class="bio">
									{$biography|strip_unsafe_html}
								</span>
							{/if}
						</li>
					{/if}
				{/foreach}
			</ul>

			<div class="abstract">
				<h3>
					{translate key="submission.synopsis"}
				</h3>
				{$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html}
			</div>

			{if $chapters|@count}
				<ul class="chapters">
					{foreach from=$chapters item=chapter}
						<li>
							<span class="title">
								{$chapter->getLocalizedTitle()}
								{if $chapter->getLocalizedSubtitle() != ''}
									<span class="subtitle">
										{$chapter->getLocalizedSubtitle()|escape}
									</span>
								{/if}
							</span>
							{assign var=chapterAuthors value=$chapter->getAuthorNamesAsString()}
							{if $publishedMonograph->getAuthorString() != $chapterAuthors}
								<span class="authors">
									{$chapterAuthors|escape}
								</span>
							{/if}
						</li>
					{/foreach}
				</ul>
			{/if}
		</div><!-- .main_entry -->

		<div class="entry_details">

			{* Cover image *}
			<div class="item cover">
				<img alt="{translate key="catalog.coverImageTitle" monographTitle=$monograph->getLocalizedFullTitle()|strip_tags|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" submissionId=$monograph->getId() random=$monograph->getId()|uniqid}" />
			</div>

			{* Date published *}
			<div class="item published_date">
				<span class="label">
					{translate key="catalog.published"}
				</span>
				<span class="value">
					{$monograph->getDatePublished()|date_format:$dateFormatShort}
				</span>
			</div>

			{* Sharing code *}
			{if !is_null($sharingCode)}
				<div class="item sharing">
					{$sharingCode}
				</div>
			{/if}

			{* Files *}
			{if $availableFiles|@count}
				<div class="item files">
					{assign var=publicationFormats value=$publishedMonograph->getPublicationFormats()}
					{foreach from=$publicationFormats item=publicationFormat}
						{assign var=representationId value=$publicationFormat->getId()}
						{if $publicationFormat->getIsAvailable() && $availableFiles[$representationId]}
							<div class="{$representationId|escape}">
								<span class="label">
									{$publicationFormat->getLocalizedName()|escape}
								</span>
								<span class="value">
									<ul>
										{* There will be at most one of these *}
										{foreach from=$availableFiles[$representationId] item=availableFile}
											<li>
												<span class="name">
													{$availableFile->getLocalizedName()|escape}
												</span>
												<span class="link">
													{if $availableFile->getDocumentType()==$smarty.const.DOCUMENT_TYPE_PDF}
														{url|assign:downloadUrl op="view" path=$publishedMonograph->getId()|to_array:$representationId:$availableFile->getFileIdAndRevision()}
													{else}
														{url|assign:downloadUrl op="download" path=$publishedMonograph->getId()|to_array:$representationId:$availableFile->getFileIdAndRevision()}
													{/if}
													<a href="{$downloadUrl}" class="{$availableFile->getDocumentType()}">
														{if $availableFile->getDirectSalesPrice()}
															{translate key="payment.directSales.purchase" amount=$currency->format($availableFile->getDirectSalesPrice()) currency=$currency->getCodeAlpha()}
														{else}
															{translate key="payment.directSales.download"}
															{* @todo make the open access icon appear *}
														{/if}
													</a>
												</span>
											</li>
										{/foreach}
									</ul>
								</span><!-- .value -->
							</div>
						{/if}
					{/foreach}
				</div>
			{/if}

			{* Series *}
			{if $series}
				<div class="item series">
					<span class="label">
						{translate key="series.series"}
					</span>
					<span class="value">
						<a href="{url page="catalog" op="series" path=$series->getPath()}">
							{$series->getLocalizedFullTitle()|escape}
						</a>
					</span>
				</div>
			{/if}

			{* Categories *}
			{assign var=categories value=$publishedMonograph->getCategories()}
			{if !$categories->wasEmpty()}
				<div class="item categories">
					<span class="label">
						{translate key="catalog.categories"}
					</span>
					<span class="value">
						<ul>
							{iterate from=categories item=category}
								<li>
									<a href="{url op="category" path=$category->getPath()}">
										{$category->getLocalizedTitle()|strip_unsafe_html}
									</a>
								</li>
							{/iterate}
						</ul>
					</span>
				</div>
			{/if}

			{* Custom blocks *}
			{if !empty($blocks)}
				{foreach from=$blocks item=block key=blockKey}
					<div class="item block {$blockKey|escape}">
						{$block}
					</div>
				{/foreach}
			{/if}

			{* Copyright statement *}
			{if $currentPress->getSetting('includeCopyrightStatement')}
				<div class="item copyright">
					{translate|escape key="submission.copyrightStatement" copyrightYear=$publishedMonograph->getCopyrightYear() copyrightHolder=$publishedMonograph->getLocalizedCopyrightHolder()}
				</div>
			{/if}

			{* License *}
			{if $currentPress->getSetting('includeLicense') && $ccLicenseBadge}
				<div class="item license">
					{$ccLicenseBadge}
				</div>
			{/if}

			{* Publication formats *}
			{if count($publicationFormats)}
				{foreach from=$publicationFormats item="publicationFormat"}
					{if $publicationFormat->getIsApproved()}
						<div class="item publication_format">
							<h3 class="pkp_screen_reader">
								{translate key="monograph.publicationFormatDetails"}
							</h3>

							<div class="format_detail format">
								<span class="label">
									{translate key="monograph.publicationFormat"}
								</span>
								<span class="value">
									{$publicationFormat->getLocalizedName()|escape}
								</span>
							</div>

							{* DOI's and other identification codes *}
							{assign var=identificationCodes value=$publicationFormat->getIdentificationCodes()}
							{assign var=identificationCodes value=$identificationCodes->toArray()}
							{if $identificationCodes}
								{foreach from=$identificationCodes item=identificationCode}
									<div class="format_detail identification_code">
										<span class="label">
											{$identificationCode->getNameForONIXCode()|escape}
										</span>
										<span class="value">
											{$identificationCode->getValue()|escape}
										</span>
									</div>
								{/foreach}
							{/if}

							{* Dates of publication *}
							{assign var=publicationDates value=$publicationFormat->getPublicationDates()}
							{assign var=publicationDates value=$publicationDates->toArray()}
							{if $publicationDates}
								{foreach from=$publicationDates item=publicationDate}
									<div class="format_detail date">
										<span class="label">
											{$publicationDate->getNameForONIXCode()|escape}
										</span>
										<span class="value">
											{assign var=dates value=$publicationDate->getReadableDates()}
											{* note: these dates have dateFormatShort applied to them in getReadableDates() if they need it *}
											{if $publicationDate->isFreeText() || $dates|@count == 1}
												{$dates[0]|escape}
											{else}
												{* @todo the &mdash; ought to be translateable *}
												{$dates[0]|escape}&mdash;{$dates[1]|escape}
											{/if}
											{if $publicationDate->isHijriCalendar()}
												<span class="hijri">
													{translate key="common.dateHijri"}
												</span>
											{/if}
									</div>
								{/foreach}
							{/if}

							{* PubIDs *}
							{if $enabledPubIdTypes|@count}
								{foreach from=$enabledPubIdTypes item=pubIdType}
									{assign var=storedPubId value=$publicationFormat->getStoredPubId($pubIdType)}
									{if $storePubId != ''}
										<div class="format_detail pubid {$publicationFormat->getId()|escape}">
											<span class="label">
												{$pubIdType}
											</span>
											<span class="value">
												{$storedPubId|escape}
											</span>
										</div>
									{/if}
								{/foreach}
							{/if}

							{* Physical dimensions *}
							{if $publicationFormat->getPhysicalFormat()}
								<div class="format_detail dimensions">
									<span class="label">
										{translate key="monograph.publicationFormat.productDimensions"}
									</span>
									<span class="value">
										{$publicationFormat->getDimensions()|escape}
									</span>
								</div>
							{/if}
						</div>
					{/if}
				{/foreach}
			{/if}

		</div><!-- .details -->
	</div><!-- .row -->

</div><!-- .obj_monograph_full -->
