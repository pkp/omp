{**
 * templates/manageCatalog/index.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Header for the catalog management tabbed interface
 *}
{extends file="layouts/backend.tpl"}

{block name="page"}
	<h1 class="app__pageHeading">
		{translate key="navigation.catalog"}
	</h1>

	{if array_intersect(array(\PKP\security\Role::ROLE_ID_MANAGER), $userRoles)}
		{assign var="isManager" value=true}
	{/if}

	<tabs :track-history="true">
		<tab id="monographs" label="{translate key="navigation.catalog.allMonographs"}">
			{help file="catalog" class="pkp_help_tab"}
			<catalog-list-panel
				v-bind="components.catalog"
				@set="set"
			/>
		</tab>
		{if $isManager}
			<tab id="spotlights" label="{translate key="spotlight.spotlights"}">
				{help file="catalog" section="spotlights" class="pkp_help_tab"}
				<div class="pkp_content_panel">
					{capture assign=spotlightsGridUrl}{url router=PKPApplication::ROUTE_COMPONENT component="grid.content.spotlights.ManageSpotlightsGridHandler" op="fetchGrid" escape=false}{/capture}
					{load_url_in_div id="spotlightsGridContainer" url=$spotlightsGridUrl}
				</div>
			</tab>
		{/if}
	</tabs>
{/block}
