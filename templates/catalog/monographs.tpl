{**
 * templates/catalog/monographs.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Present a list of monographs.
 *}

{* Generate a unique ID for this monograph list *}
{capture assign=monographContainerId}monographsContainer-{$listName}{/capture}

<script type="text/javascript">
	// Initialize JS handler.
	$(function() {ldelim}
		$('#{$monographContainerId|escape:"javascript"}').pkpHandler(
			'$.pkp.pages.catalog.MonographListHandler'
		);
	{rdelim});
</script>

<div id="{$monographContainerId|escape}">
	<div class="pkp_helpers_align_right">
		<ul class="submission_actions pkp_helpers_flatlist pkp_linkActions">
			{if $includeOrganizeAction}<li>{null_link_action id="organize" key="common.organize" image="organize"}</li>{/if}
			<li>{null_link_action id="listView" key="common.list" image="list_view"}</li>
			<li>{null_link_action id="gridView" key="common.grid" image="grid_view"}</li>
		</ul>
	</div>
	<div id="monographListContainer" class="pkp_helpers_clear">
		<ul class="pkp_catalog_monographList">
			{iterate from=publishedMonographs item=monograph}
				{include file="catalog/monograph.tpl"}
			{/iterate}
		</ul>
	</div>
</div>
