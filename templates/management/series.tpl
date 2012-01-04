{**
 * templates/manager/series.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press management series list.
 *}
{strip}
{assign var="pageTitle" value="series.series"}
{include file="common/header.tpl"}
{/strip}

{url|assign:seriesUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.series.SeriesGridHandler" op="fetchGrid"}
{load_url_in_div id="seriesContainer" url=$seriesUrl}

{include file="common/footer.tpl"}

