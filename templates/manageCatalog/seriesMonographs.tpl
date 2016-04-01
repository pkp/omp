{**
 * templates/manageCatalog/seriesMonographs.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Present a list of series monographs for management.
 *}

<div id="seriesMonographs">
	{url|assign:seriesMonographsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.manageCatalog.SeriesMonographsGridHandler" op="fetchGrid" escape=false}
	{load_url_in_div id="seriesMonographsGridContainer" url=$seriesMonographsGridUrl}
</div>

