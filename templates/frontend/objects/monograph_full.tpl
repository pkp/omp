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
 *}
<div class="obj_monograph_full">
	<h1 class="title">
		{$monograph->getLocalizedFullTitle()}
	</h1>
	<div class="authors">
		{$publishedMonograph->getAuthorString()}
	</div>

	<a href="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="cover" submissionId=$monograph->getId()}" class="cover">
		<img alt="{translate key="catalog.coverImageTitle" monographTitle=$monograph->getLocalizedFullTitle()|strip_tags|escape}" src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" submissionId=$monograph->getId() random=$monograph->getId()|uniqid}" />
	</a>
	<ul class="details">
		<li class="published">
			<span class="label">
				{translate key="catalog.published"}
			</span>
			<span class="value">
				{$monograph->getDatePublished()|date_format:$dateFormatShort}
			</span>
		</li>

		{if $series}
			<li class="series">
				<span class="label">
					{translate key="series.series}
				</span>
				<span class="value">
					<a href="{url page="catalog" op="series" path=$series->getPath()}">
						{$series->getLocalizedFullTitle()}
					</a>
				</span>
			</li>
		{/if}

		{assign var=categories value=$publishedMonograph->getCategories()}
		{if !$categories->wasEmpty()}
			<li class="categories">
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
			</li>
		{/if}
	</ul>

	{if count($publicationFormats)}
		{foreach from=$publicationFormats item="publicationFormat"}
			{if $publicationFormat->getIsApproved()}
				{include file="frontend/objects/publicationFormat.tpl"}
			{/if}
		{/foreach}
	{/if}

	<div class="abstract">
		<h3>
			{translate key="submission.synopsis"}
		</h3>
		{$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html}
	</div>

	<ul class="author_roles">
		{foreach from=$publishedMonograph->getAuthors() item=author}
			{if $author->getIncludeInBrowse()}
				<li>
					<span class="name">
						{$author->getFullName()}
					</span>
					<span class="role">
						{$author->getLocalizedUserGroupName()}
					</span>
					{assign var=biography value=$author->getLocalizedBiography()|strip_unsafe_html}
					{if $biography}
						<span class="bio">
							{$biography}
						</span>
					{/if}
				</li>
			{/if}
		{/foreach}
	</ul>

	{if $chapters|@count}
		<ul class="chapters">
			{foreach from=$chapters item=chapter}
				<li>
					<span class="title">
						{$chapter->getLocalizedTitle()}
						{if $chapter->getLocalizedSubtitle() != ''}
							<span class="subtitle">
								{$chapter->getLocalizedSubtitle()}
							</span>
						{/if}
					</span>
					{assign var=chapterAuthors value=$chapter->getAuthorNamesAsString()}
					{if $publishedMonograph->getAuthorString() != $chapterAuthors}
						<span class="authors">
							{$chapterAuthors}
						</span>
					{/if}
				</li>
			{/foreach}
		</ul>
	{/if}

	{if $availableFiles|@count}
		<ul class="files">
			{assign var=publicationFormats value=$publishedMonograph->getPublicationFormats()}
			{assign var=currency value=$currentPress->getSetting('currency')}
			{foreach from=$publicationFormats item=publicationFormat}
				{assign var=representationId value=$publicationFormat->getId()}
				{if $publicationFormat->getIsAvailable() && $availableFiles[$representationId]}
					<li class="{$representationId|escape}">
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
													{translate key="payment.directSales.purchase" amount=$availableFile->getDirectSalesPrice() currency=$currency}
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
					</li>
				{/if}
			{/foreach}
		</ul>
	{/if}

	{if !is_null($sharingCode)}
		<div class="sharing">
			{$sharingCode}
		</div>
	{/if}

	{if !empty($blocks)}
		<ul class="blocks">
			{foreach from=$blocks item=block key=blockKey}
				<li class="block {$blockKey|escape}">
					{$block}
				</li>
			{/foreach}
		</ul>
	{/if}

	{if $currentPress->getSetting('includeCopyrightStatement')}
		<div class="copyright">
			{translate|escape key="submission.copyrightStatement" copyrightYear=$publishedMonograph->getCopyrightYear() copyrightHolder=$publishedMonograph->getLocalizedCopyrightHolder()}
		</div>
	{/if}

	{if $currentPress->getSetting('includeLicense') && $ccLicenseBadge}
		<div class="license">
			{$ccLicenseBadge}
		</div>
	{/if}

</div><!-- .obj_monograph_full -->
