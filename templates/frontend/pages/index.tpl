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
 * @uses $categories array List of categories in this press
 * @uses $series array List of series in this press
 * @uses $newReleases array List of new releases in this press
 *}
{include file="common/frontend/header.tpl"}

<div class="page page_homepage">

	{* Spotlights *}
	{if count($spotlights)}
		<div class="row row_spotlights">
			{include file="frontend/components/spotlights.tpl"}
		</div>
	{/if}

	{* Search and browse section *}
	<div class="row row_find">
		<h2>
			{translate key="common.searchOrBrowse"}
		</h2>

		{include file="frontend/components/searchForm_homepage.tpl"}

		{if count($categories) || count($series)}
			{include file="frontend/components/browseList.tpl"}
		{/if}
	</div>

	{* New releases *}
	{if $newReleases|count}
		<div class="row row_new_releases">
			<h2>
				{translate key="catalog.newReleases"}
			</h2>
			<ul class="cmp_monographs_list">
				{foreach from=$newReleases item=monograph}
					<li>
						{include file="frontend/objects/monograph_summary.tpl" monograph=$monograph}
					</li>
				{/foreach}
			</ul>
		</div>
	{/if}

</div>
{include file="common/frontend/footer.tpl"}
