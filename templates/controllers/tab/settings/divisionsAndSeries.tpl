{**
 * controllers/tab/settings/divisionsAndSeries.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User role divisionsAndSeries.
 *
 *}

<h3>2.2 {translate key="manager.setup.divisionsAndSeries"}</h3>

{url|assign:divisionsUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.DivisionsListbuilderHandler" op="fetch"}
{load_url_in_div id="divisionsContainer" url=$divisionsUrl}

{url|assign:seriesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.series.SeriesGridHandler" op="fetchGrid"}
{load_url_in_div id="seriesGridDiv" url=$seriesGridUrl}