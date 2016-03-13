{**
 * templates/manageCatalog/index.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for the catalog management tabbed interface
 *}
{strip}
{assign var="pageTitle" value="navigation.catalog"}
{include file="common/header.tpl"}
{/strip}

{if array_intersect(array(ROLE_ID_MANAGER), $userRoles)}
	{assign var="isManager" value=true}
{/if}

<script type="text/javascript">
	// Initialize JS handler for catalog header.
	$(function() {ldelim}
		$('#catalogTabs').pkpHandler(
			'$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler'
		);
	{rdelim});
</script>

<div id="catalogHeader">
	<div class="pkp_helpers_clear"></div>
	<p>{translate key="catalog.manage.managementIntroduction"}</p>

	<div class="pkp_helpers_clear"></div>
	<div id="catalogTabs" class="pkp_controllers_tab">
		<ul>
			<li><a name="manageHomepage" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.manageCatalog.ManageCatalogTabHandler" op="catalog"}">{translate key="navigation.catalog"}</a></li>
			<li><a name="manageCategory" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.manageCatalog.ManageCatalogTabHandler" op="category"}">{translate key="navigation.catalog.administration.categories"}</a></li>
			<li><a name="manageSeries" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.manageCatalog.ManageCatalogTabHandler" op="series"}">{translate key="catalog.manage.series"}</a></li>
			{if $isManager}<li><a name="manageSpotlights" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.manageCatalog.ManageCatalogTabHandler" op="spotlights"}">{translate key="spotlight.spotlights"}</a></li>{/if}
		</ul>
	</div>
</div>

{include file="common/footer.tpl"}
