{**
 * templates/frontend/components/searchForm_simple.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Simple display of a search form with just text input and search button
 *
 * @uses $searchQuery string Previously input search query
 *}
<form class="pkp_search" action="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="results"}" method="post">
	<input name="query" value="{$searchQuery|escape}" type="text">
	<button type="submit">
		{translate key="common.search"}
	</button>
	<div class="search_controls" aria-hidden="true">
		<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="results"}" class="headerSearchPrompt search_prompt" aria-hidden="true">
			{translate key="common.search"}
		</a>
		<a href="#" class="search_cancel headerSearchCancel" aria-hidden="true"></a>
		<span class="search_loading" aria-hidden="true"></span>
	</div>
</form>
