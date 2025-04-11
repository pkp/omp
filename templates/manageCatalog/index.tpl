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

	<tabs :track-history="true">
		<tab id="monographs" label="{translate key="navigation.catalog.allMonographs"}">
			<catalog-list-panel
				v-bind="components.catalog"
				@set="set"
			/>
		</tab>
	</tabs>
{/block}
