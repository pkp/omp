{**
 * templates/frontend/objects/spotlight.tpl.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a spotlight
 *
 * @uses $spotlight Spotlight The spotlight to be displayed
 * @uses $item Book|Series|Author The item this spotlight is about
 * @uses $assocType bit The type of item this spotlight is about. Matches a
 *       constant representing a book, series or author.
 * @uses $coverImage array A cover image related to $item
 * @uses $coverImageUrl string The url to $coverImage
 * @uses $targetUrl string The url this spotlight links to
 * @uses $hasCoverImage string Does this spotlight have a cover image? String
 *       is empty or set to the desired CSS class
 * @uses $description string A description to display with this spotlight
 *}
<div class="obj_spotlight {$hasCoverImage}">

	{if $coverImage}
		<a class="cover_image" href="{$targetUrl}">
			<img alt="{$item->getLocalizedFullTitle()|strip_tags|escape}" src="{$coverImageUrl}">
		</a>
	{/if}

	<div class="call_to_action">
		<h3 class="title">
			{$spotlight->getLocalizedTitle()|strip_unsafe_html}
		</h3>
		{if $description}
		<div class="description">
			{$description}
		</div>
		{/if}
		<a href="{$targetUrl}" title="{translate|escape key="common.readMoreWithTitle" title=$spotlight->getLocalizedTitle()}">
			{translate key="common.readMore"}
		</a>
	</div>
</div>
