{**
 * templates/manageCatalog/categoryMonographs.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Present a list of category monographs for management.
 *}

<div id="categoryMonographs">
	{url|assign:categoryMonographsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.manageCatalog.CategoryMonographsGridHandler" op="fetchGrid" escape=false}
	{load_url_in_div id="categoryMonographsGridContainer" url=$categoryMonographsGridUrl}
</div>

