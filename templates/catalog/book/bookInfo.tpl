{**
 * templates/catalog/book/bookInfo.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the information pane of a public-facing book view in the catalog.
 *
 * Available data:
 *  $representationId int Publication format ID
 *  $availableFiles array Array of available MonographFiles
 *  $publishedMonograph PublishedMonograph The published monograph object.
 *}
<script type="text/javascript">
	// Attach the tab handler.
	$(function() {ldelim}
		$('#bookInfoTabs').pkpHandler('$.pkp.controllers.TabHandler');
	{rdelim});
</script>

<div class="bookInfo">
	<div class="bookInfoHeader">
		<h3>{$publishedMonograph->getLocalizedFullTitle()|strip_unsafe_html}</h3>
		<div class="authorName">{$publishedMonograph->getAuthorString()}</div>
	</div>

	<div id="bookInfoTabs" class="pkp_controllers_tab">
		<ul>
			<li><a href="#abstractTab">{translate key="submission.synopsis"}</a></li>
			{if $availableFiles|@count != 0}<li><a href="#downloadTab">{translate key="submission.download"}</a></li>{/if}
			{call_hook|assign:"sharingCode" name="Templates::Catalog::Book::BookInfo::Sharing"}
			{if !is_null($sharingCode) || !empty($blocks)}
				<li><a href="#sharingTab">{translate key="submission.sharing"}</a></li>
			{/if}
		</ul>

		<div id="abstractTab">
			<div id="abstractContents">{$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html}</div>

			<div id="authorContents">
				{assign var=authors value=$publishedMonograph->getAuthors()}
				{foreach from=$authors item=author}
					{if $author->getIncludeInBrowse()}
						<div class="authorContent">
							<div class="role">{$author->getLocalizedUserGroupName()}</div>
							<div class="name">{$author->getFullName()}</div>
							{assign var=biography value=$author->getLocalizedBiography()|strip_unsafe_html}
							{if $biography}
								<div class="biography">{$biography}</div>
							{/if}
						</div>
					{/if}
				{/foreach}
			</div>

			<div id="permissionContents">
				{if $currentPress->getSetting('includeCopyrightStatement')}
					<div id="copyrightStatement">{translate|escape key="submission.copyrightStatement" copyrightYear=$publishedMonograph->getCopyrightYear() copyrightHolder=$publishedMonograph->getLocalizedCopyrightHolder()}</div>
				{/if}
				{if $currentPress->getSetting('includeLicense') && $ccLicenseBadge}
					<div id="license">{$ccLicenseBadge}</div>
				{/if}
			</div>
		</div>
		{if $availableFiles|@count != 0}
			<div id="downloadTab">
				{assign var=publicationFormats value=$publishedMonograph->getPublicationFormats()}
				{assign var=currency value=$currentPress->getSetting('currency')}
				{if !$loggedInUsername}<p>{translate key="catalog.loginRequiredForPayment"}</p>{/if}
				<ul>
<<<<<<< HEAD
					{include file="catalog/book/bookFiles.tpl" availableFile=$availableFile publicationFormatId=$publicationFormatId publishedMonograph=$publishedMonograph currency=$currency}
				</ul>
<<<<<<< HEAD
=======
					{foreach from=$publicationFormats item=publicationFormat}
						{if $publicationFormat->getIsAvailable() && $publicationFormat->getIsApproved()}
							{include file="catalog/book/bookFiles.tpl" availableFile=$availableFile representationId=$publicationFormat->getId() publishedMonograph=$publishedMonograph currency=$currency}
						{/if}
					{/foreach}
				</ul>
			{else}
				{foreach from=$publicationFormats item=publicationFormat}
					{assign var=representationId value=$publicationFormat->getId()}
					{if $publicationFormat->getIsAvailable() && $availableFiles[$representationId]}
						<div class="publicationFormatDownload" id="publicationFormat-download-{$representationId|escape}">
							{$publicationFormat->getLocalizedName()|escape}
							<ul>
								{include file="catalog/book/bookFiles.tpl" availableFile=$availableFile representationId=$representationId publishedMonograph=$publishedMonograph currency=$currency}
							</ul>
						</div>
					{/if}
				{/foreach}
			{/if}{* useCollapsedView *}
>>>>>>> pkp/master
		</div>
=======
			</div>
>>>>>>> 189e2c5d21eea633643680f5dfd07bbe8c9b15c3
		{/if}
		{if !is_null($sharingCode) || !empty($blocks)}
			<div id="sharingTab">
				{$sharingCode}
				{foreach from=$blocks item=block key=blockKey}
					<div id="socialMediaBlock{$blockKey|escape}" class="pkp_helpers_clear">
						{$block}
					</div>
				{/foreach}
			</div>
		{/if}
	</div>
</div>
