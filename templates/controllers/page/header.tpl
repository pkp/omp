{**
 * templates/controllers/page/header.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site header div contents.
 *}
{assign var="logoImage" value="templates/images/structure/omp_logo.png"}
{url|assign:"homeUrl" page="index" router=$smarty.const.ROUTE_PAGE}
{include file="core:controllers/page/header.tpl"}
