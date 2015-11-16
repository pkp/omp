{**
 * templates/frontend/pages/index.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the front page of the site
 *
 * @uses $spotlights array Selected spotlights to promote on the homepage
 * @uses $newReleases array List of new releases in this press
 * @uses $featuredMonographs array List of featured releases in this press
 * @uses $homepageImage array Details about the uploaded homepage image
 * @uses $additionalHomeContent string HTML blob of arbitrary content added by
 *  an editor/admin.
 *}
{include file="common/frontend/header.tpl"}

<div class="page page_homepage">

	{* Homepage Image *}
	{if $homepageImage}
		<div class="homepage_image">
			<img src="{$publicFilesDir}/{$homepageImage.uploadName|escape:"url"}" alt="{$homepageImage.altText|escape}">
		</div>
	{/if}

	{* Spotlights *}
	{if count($spotlights)}
		<h2 class="pkp_screen_reader">
			{translate key="spotlight.spotlights"}
		</h2>
		{include file="frontend/components/spotlights.tpl"}
	{/if}


	{* Featured *}
	{if $featuredMonographs && $featuredMonographs|count}
		{include file="frontend/components/monographList_featured.tpl" monographs=$featuredMonographs titleKey="catalog.featured"}
	{/if}

	{* New releases *}
	{if $newReleases && $newReleases|count}
		{include file="frontend/components/monographList_featured.tpl" monographs=$newReleases titleKey="catalog.newReleases"}
	{/if}

	{* Additional Homepage Content *}
	{if $additionalHomeContent}
		<div class="additional_content">
			{$additionalHomeContent}
		</div>
	{/if}

</div>
{include file="common/frontend/footer.tpl"}
