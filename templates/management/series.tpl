{**
 * templates/manager/series.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press management series list.
 *}

<p>{translate key="manager.setup.series.description"}</p>

{url|assign:seriesUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.series.SeriesGridHandler" op="fetchGrid"}
{load_url_in_div id="seriesContainer" url=$seriesUrl}
