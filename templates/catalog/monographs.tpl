{**
 * templates/catalog/monographs.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Present a list of monographs.
 *}

<script type="text/javascript">
	// Initialize JS handler.
	$(function() {ldelim}
		$('#monographsContainer').pkpHandler(
			'$.pkp.pages.catalog.MonographListHandler'
		);
	{rdelim});
</script>

<div id="monographsContainer">
	<div class="pkp_helpers_align_right">
		<ul class="submission_actions pkp_helpers_flatlist">
			{if $organizeAction}<li>{include file="linkAction/linkAction.tpl" action=$organizeAction}</li>{/if}
			{if $listViewAction}<li>{include file="linkAction/linkAction.tpl" action=$listViewAction}</li>{/if}
			{if $gridViewAction}<li>{include file="linkAction/linkAction.tpl" action=$gridViewAction}</li>{/if}
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
