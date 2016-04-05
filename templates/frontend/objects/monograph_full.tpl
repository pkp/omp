{**
 * templates/frontend/objects/monograph_full.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
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

			<div class="item authors">
				{foreach from=$publishedMonograph->getAuthors() item=author}
					{if $author->getIncludeInBrowse()}
						<div class="sub_item">
							<div class="label">
								{$author->getFullName()|escape}
							</div>
							<div class="value">
								<div class="role">
									{$author->getLocalizedUserGroupName()|escape}
								</div>
								{assign var=biography value=$author->getLocalizedBiography()|strip_unsafe_html}
								{if $biography}
									<div class="bio">
										{$biography|strip_unsafe_html}
									</div>
								{/if}
							</div>
						</div>
					{/if}
				{/foreach}
			</div>

			<div class="item abstract">
				<h3 class="label">
					{translate key="submission.synopsis"}
				</h3>
				<div class="value">
					{$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html}
				</div>
			</div>

			{if $chapters|@count}
				<div class="item chapters">
					<h3 class="pkp_screen_reader">
						{translate key="submission.chapters"}
					</h3>
					<ul>
						{foreach from=$chapters item=chapter}
							<li>
								<div class="title">
									{$chapter->getLocalizedTitle()}
									{if $chapter->getLocalizedSubtitle() != ''}
										<div class="subtitle">
											{$chapter->getLocalizedSubtitle()|escape}
										</div>
									{/if}
								</div>
								{assign var=chapterAuthors value=$chapter->getAuthorNamesAsString()}
								{if $publishedMonograph->getAuthorString() != $chapterAuthors}
									<div class="authors">
										{$chapterAuthors|escape}
									</div>
								{/if}
							</li>
						{/foreach}
					</ul>
				</div>
			{/if}

			{call_hook name="Templates::Catalog::Book::Main"}

		</div><!-- .main_entry -->

		<div class="entry_details">

			{* Cover image *}
			<div class="item cover">
				<img alt="{translate key="catalog.coverImageTitle" monographTitle=$monograph->getLocalizedFullTitle()|strip_tags|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" submissionId=$monograph->getId() random=$monograph->getId()|uniqid}" />
			</div>

			{* Date published *}
			<div class="item published_date">
				<span class="label">
					{translate key="catalog.dateAdded"}
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

			{* Files and remote resources *}
			{if $availableFiles|@count || $remoteResources|@count}
				<div class="item files">
					{assign var=publicationFormats value=$publishedMonograph->getPublicationFormats()}
					{foreach from=$publicationFormats item=publicationFormat}
						{assign var=publicationFormatId value=$publicationFormat->getId()}
						{if $publicationFormat->getIsAvailable() && $remoteResources[$publicationFormatId]}
							{* Only one resource allowed per format, so mimic single-file-download *}
							<div class="pub_format_{$publicationFormatId|escape} pub_format_remote">
								<a href="{$publicationFormat->getRemoteURL()|escape}" target="_blank" class="remote_resource">
									{$publicationFormat->getLocalizedName()|escape}
								</a>
							</div>
						{elseif $publicationFormat->getIsAvailable() && $availableFiles[$publicationFormatId]}

							{* Use a simplified presentation if only one file exists *}
							{if $availableFiles[$publicationFormatId]|@count == 1}
								<div class="pub_format_{$publicationFormatId|escape} pub_format_single">
									{foreach from=$availableFiles[$publicationFormatId] item=availableFile}
										{if $availableFile->getDocumentType()==$smarty.const.DOCUMENT_TYPE_PDF}
											{url|assign:downloadUrl op="view" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}
										{else}
											{url|assign:downloadUrl op="download" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}
										{/if}
										<a href="{$downloadUrl}" class="{$availableFile->getDocumentType()|escape}">
											{if $availableFile->getDirectSalesPrice()}
												{translate key="payment.directSales.purchase" format=$publicationFormat->getLocalizedName() amount=$currency->format($availableFile->getDirectSalesPrice()) currency=$currency->getCodeAlpha()}
											{else}
												{translate key="payment.directSales.download" format=$publicationFormat->getLocalizedName()}
												{* @todo make the open access icon appear *}
											{/if}
										</a>
									{/foreach}
								</div>

							{* Use an itemized presentation if multiple files exists *}
							{else}
								<div class="pub_format_{$publicationFormatId|escape}">
									<span class="label">
										{$publicationFormat->getLocalizedName()|escape}
									</span>
									<span class="value">
										<ul>
											{* There will be at most one of these *}
											{foreach from=$availableFiles[$publicationFormatId] item=availableFile}
												<li>
													<span class="name">
														{$availableFile->getLocalizedName()|escape}
													</span>
													<span class="link">
														{if $availableFile->getDocumentType()==$smarty.const.DOCUMENT_TYPE_PDF}
															{url|assign:downloadUrl op="view" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}
														{else}
															{url|assign:downloadUrl op="download" path=$publishedMonograph->getId()|to_array:$publicationFormatId:$availableFile->getFileIdAndRevision()}
														{/if}
														<a href="{$downloadUrl}" class="{$availableFile->getDocumentType()}">
															{if $availableFile->getDirectSalesPrice()}
																{translate key="payment.directSales.purchase" format=$publicationFormat->getLocalizedName() amount=$currency->format($availableFile->getDirectSalesPrice()) currency=$currency->getCodeAlpha()}
															{else}
																{translate key="payment.directSales.download" format=$publicationFormat->getLocalizedName()}
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
						{/if}
					{/foreach}
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
			{assign var=categories value=$publishedMonograph->getCategories()}
			{if !$categories->wasEmpty()}
				<div class="item categories">
					<div class="label">
						{translate key="catalog.categories"}
					</div>
					<div class="value">
						<ul>
							{iterate from=categories item=category}
								<li>
									<a href="{url op="category" path=$category->getPath()}">
										{$category->getLocalizedTitle()|strip_unsafe_html}
									</a>
								</li>
							{/iterate}
						</ul>
					</div>
				</div>
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

						{assign var=identificationCodes value=$publicationFormat->getIdentificationCodes()}
						{assign var=identificationCodes value=$identificationCodes->toArray()}
						{assign var=publicationDates value=$publicationFormat->getPublicationDates()}
						{assign var=publicationDates value=$publicationDates->toArray()}
						{assign var=hasPubId value=false}
						{if $enabledPubIdTypes|@count}
							{foreach from=$enabledPubIdTypes item=pubIdType}
								{if $publicationFormat->getStoredPubId($pubIdType)}
									{php}break;{/php}
								{/if}
							{/foreach}
						{/if}

						{* Skip if we don't have any information to print about this pub format *}
						{if !$identificationCodes && !$publicationDates && !$hasPubId && !$publicationFormat->getPhysicalFormat()}
							{php}continue;{/php}
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
							{if $enabledPubIdTypes|@count}
								{foreach from=$enabledPubIdTypes item=pubIdType}
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
							{/if}

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
