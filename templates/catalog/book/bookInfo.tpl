{**
 * templates/catalog/book/bookInfo.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * Display the information pane of a public-facing book view in the catalog.
 * 
 * --------------------------------------------------------------------
 * modifiedy by Simon A. Frank: contents and download in same tab
 * --------------------------------------------------------------------
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
<<<<<<< HEAD
			{if $availableFiles|@count != 0}<li><a href="#downloadTab">{translate key="submission.download"}</a></li>{/if}
=======
			{if $chapters|@count != 0}<li><a href="#contentsTab">{translate key="common.contents"}</a></li>{/if}
>>>>>>> 0f54f6b321df1f1adf157b0467cada21b3f3adef
			{call_hook|assign:"sharingCode" name="Templates::Catalog::Book::BookInfo::Sharing"}
			{if !is_null($sharingCode) || !empty($blocks)}
				<li><a href="#sharingTab">{translate key="submission.sharing"}</a></li>
			{/if}
		</ul>

		<div id="abstractTab">
			{$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html}

			{assign var=authors value=$publishedMonograph->getAuthors()}
			{foreach from=$authors item=author}
				{if $author->getIncludeInBrowse()}
					<p>{translate key="catalog.aboutTheAuthor" roleName=$author->getLocalizedUserGroupName()}: <strong>{$author->getFullName()}</strong></p>
					{assign var=biography value=$author->getLocalizedBiography()|strip_unsafe_html}
					{if $biography != ''}{$biography}{else}{translate key="catalog.noBioInfo"}{/if}
				{/if}
			{/foreach}
		</div>
<<<<<<< HEAD
		{if $availableFiles|@count != 0}
		<div id="downloadTab">
			{assign var=publicationFormats value=$publishedMonograph->getPublicationFormats()}
			{assign var=currency value=$currentPress->getSetting('currency')}
			{if !$loggedInUsername}<p>{translate key="catalog.loginRequiredForPayment"}</p>{/if}
				<ul>
					{include file="catalog/book/bookFiles.tpl" availableFile=$availableFile publicationFormatId=$publicationFormatId publishedMonograph=$publishedMonograph currency=$currency}
				</ul>
		</div>
		{/if}
=======
		{if $chapters|@count != 0}
			<div id="contentsTab">
				{foreach from=$chapters item=chapter}
					<p>
						{* TODO: Move style to Stylesheet *} 
						<div style='float:left;width:60%;margin-bottom:10px'>
						<strong>{$chapter->getLocalizedTitle()}</strong>
						{if $chapter->getLocalizedSubtitle() != '' }<br />{$chapter->getLocalizedSubtitle()}{/if}
						{assign var=chapterAuthors value=$chapter->getAuthorNamesAsString()}
						{if $publishedMonograph->getAuthorString() != $chapterAuthors}
							<div class="authorName">{$chapterAuthors}</div>
						{/if}
						</div>
						<div>
							{assign var=publicationFormats value=$publishedMonograph->getPublicationFormats()}
							{assign var=currency value=$currentPress->getSetting('currency')}							
							{foreach from=$publicationFormats item=publicationFormat}
								{if $publicationFormat->getIsAvailable()}
									{include file="catalog/book/bookFilesContentAndDownload.tpl" availableFile=$availableFile publicationFormatId=$publicationFormat->getId() publishedMonograph=$publishedMonograph currency=$currency chapterId=$chapter->getId()}
								{/if}
							{/foreach}
						</div>
					</p>
				{/foreach}
			</div>
		{/if}

>>>>>>> 0f54f6b321df1f1adf157b0467cada21b3f3adef
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
