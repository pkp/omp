{**
 * templates/manager/categories.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press management categories list.
 *}

{* Help Link *}
{help file="settings.md" section="context" class="pkp_help_tab"}

{url|assign:categoriesUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.category.CategoryCategoryGridHandler" op="fetchGrid" escape=false}
{load_url_in_div id="categoriesContainer" url=$categoriesUrl}
