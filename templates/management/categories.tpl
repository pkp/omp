{**
 * templates/manager/categories.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press management categories list.
 *}
{strip}
{assign var="pageTitle" value="grid.category.categories"}
{include file="common/header.tpl"}
{/strip}

{url|assign:categoriesUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.category.CategoryCategoryGridHandler" op="fetchGrid"}
{load_url_in_div id="categoriesContainer" url=$categoriesUrl}

{include file="common/footer.tpl"}

