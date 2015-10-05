{**
 * templates/frontend/components/searchForm_homepage.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display of a search form on the homepage in a search and browse section
 *
 *}
<form class="cmp_search_homepage pkp_form" action="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="results"}" method="post">

	<label for="search_query_homepage">
		{translate key="common.searchOrBrowse"}
	</label>
	<input name="query" id="search_query_homepage" type="text">
	<button>{translate key="common.searchCatalog"}</button>
</form>
