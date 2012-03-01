{**
 * templates/controllers/tab/content/spotlights/spotlights.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Spotlights management.
 *
 *}

{url|assign:spotlightsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.content.spotlights.ManageSpotlightsGridHandler" op="fetchGrid"}
{load_url_in_div id="spotlightsGridContainer" url="$spotlightsGridUrl"}