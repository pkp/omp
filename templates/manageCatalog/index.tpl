{**
 * templates/manageCatalog/index.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
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
		$('#catalogTabs').pkpHandler('$.pkp.controllers.TabHandler');
	{rdelim});
</script>

<div id="catalogTabs" class="pkp_controllers_tab">
	<ul>
		<li><a name="monographs" href="#monographs">{translate key="navigation.catalog.allMonographs"}</a></li>
		{if $isManager}<li><a name="spotlights" href="#spotlights">{translate key="spotlight.spotlights"}</a></li>{/if}
	</ul>
	<div id="monographs">
		{help file="catalog.md" class="pkp_help_tab"}
		<div class="pkp_content_panel">
			{assign var="uuid" value=""|uniqid|escape}
			<div id="catalog-submissions-list-handler-{$uuid}">
			    <script type="text/javascript">
			        pkp.registry.init('catalog-submissions-list-handler-{$uuid}', 'CatalogSubmissionsListPanel', {$catalogListData|json_encode});
			    </script>
			</div>
		</div>
	</div>
	{if $isManager}
		<div id="spotlights">
			{help file="catalog.md" section="spotlights" class="pkp_help_tab"}
			<div class="pkp_content_panel">
				{capture assign=spotlightsGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="grid.content.spotlights.ManageSpotlightsGridHandler" op="fetchGrid" escape=false}{/capture}
				{load_url_in_div id="spotlightsGridContainer" url=$spotlightsGridUrl}
			</div>
		</div>
	{/if}
</div>

{include file="common/footer.tpl"}
