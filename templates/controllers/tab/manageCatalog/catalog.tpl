{**
 * templates/controllers/tab/manageCatalog/catalog.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Catalog management catalog tab contents.
 *}

{* Help Link *}
{help file="catalog.md" class="pkp_help_tab"}

{url|assign:homepageMonographsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.manageCatalog.HomepageMonographsGridHandler" op="fetchGrid" escape=false}
{load_url_in_div id="homepageMonographsGridContainer" url=$homepageMonographsGridUrl}
