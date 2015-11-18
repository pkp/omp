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
 * @uses $displayFeaturedBooks bool Should featured books be displayed?
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
	{if $featuredMonographs|count}
		<div class="row row_new_releases">
			{include file="frontend/components/monographList.tpl" monographs=$featuredMonographs titleKey="catalog.feature"}
		</div>
	{/if}

	{* New releases *}
	{if $newReleases|count}
		<div class="row row_new_releases">
			{include file="frontend/components/monographList.tpl" monographs=$newReleases titleKey="catalog.newReleases"}
		</div>
	{/if}

	{* Additional Homepage Content *}
	{if $additionalHomeContent}
		<div class="row row_additional_content">
			{$additionalHomeContent}
		</div>
	{/if}

	{* Search section *}
	<div class="row row_find">
		<h2>
			{translate key="common.searchOrBrowse"}
		</h2>

		{include file="frontend/components/searchForm_homepage.tpl"}
	</div>

</div>
{include file="common/frontend/footer.tpl"}
