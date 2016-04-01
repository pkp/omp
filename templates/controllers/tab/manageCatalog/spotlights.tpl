{**
 * templates/controllers/tab/manageCatalog/spotlights.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Catalog management spotlights tab content.
 *}

<p>{translate key="catalog.manage.spotlightDescription"}</p>
{url|assign:spotlightsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.content.spotlights.ManageSpotlightsGridHandler" op="fetchGrid" escape=false}
{load_url_in_div id="spotlightsGridContainer" url=$spotlightsGridUrl}
