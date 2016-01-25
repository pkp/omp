{**
 * templates/dashboard/index.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Dashboard index.
 *}
{capture assign="additionalDashboardTabs"}
	{if array_intersect(array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR), $userRoles)}
		<li><a name="catalog" href="{url router=$smarty.const.ROUTE_PAGE page="manageCatalog"}">{translate key="navigation.catalog"}</a></li>
	{/if}
{/capture}
{include file="core:dashboard/index.tpl" additionalDashboardTabs=$additionalDashboardTabs}
