{**
 * controllers/tab/settings/categoriesAndSeries/form/categoriesAndSeriesForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Category and series management tab. (Not really a form.)
 *
 *}
{url|assign:categoriesUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.category.CategoryCategoryGridHandler" op="fetchGrid"}
{load_url_in_div id="categoriesContainer" url=$categoriesUrl}

{url|assign:seriesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.series.SeriesGridHandler" op="fetchGrid"}
{load_url_in_div id="seriesGridDiv" url=$seriesGridUrl}
