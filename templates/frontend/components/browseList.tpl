{**
 * templates/frontend/components/browseList.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a list of categories and series that can be browsed on the
 *  homepage.
 *
 * @uses $categories CategoryDAO Iterator containing categories in this press
 * @uses $series SeriesDAO Iterator containing series in this press
 *}

<ul class="cmp_browse_list">

	{* Categories *}
	{if count($categories)}
		<li{if count($categories) > 10} class="large"{/if}>
			{translate key="catalog.categories"}

			<ul>
				{iterate from=categories item=category}
					<li{if $category->getParentId()} class="is_sub"{/if}>
						<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="category" path=$category->getPath()|escape}">
							{$category->getLocalizedTitle()|strip_unsafe_html}
						</a>
					</li>
				{/iterate}
			</ul>
		</li>
	{/if}

	{* Series *}
	{if count($series)}
		<li{if count($series) > 10} class="large"{/if}>
			{translate key="series.series"}

			<ul>
				{iterate from=series item=series_i}
					<li>
						<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path=$series_i->getPath()|escape}">
							{$series_i->getLocalizedTitle()|strip_unsafe_html}
						</a>
					</li>
				{/iterate}
			</ul>
		</li>
	{/if}

</ul>
