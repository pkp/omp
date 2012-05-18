{**
 * templates/manageCatalog/monographs.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Present a list of monographs for management.
 *}

{* Generate a unique ID for this monograph list *}
{capture assign=monographContainerId}monographsContainer-{$listName}{/capture}

<script type="text/javascript">
	// Initialize JS handler.
	$(function() {ldelim}
		$('#{$monographContainerId|escape:"javascript"}').pkpHandler(
			'$.pkp.pages.manageCatalog.MonographListHandler'
		);
	{rdelim});
</script>
{if $messageKey}<p>{translate key=$messageKey}</p>{/if}
<div id="{$monographContainerId|escape}">
	<div class="pkp_helpers_align_right">
		<ul class="submission_actions pkp_helpers_flatlist pkp_linkActions">
			{if $includeFeatureAction}<li>{null_link_action id="feature-$monographContainerId" key="common.feature" image="feature"}</li>{/if}
		</ul>
	</div>
	{if $category}
		<div id="categoryDescription">
			{$category->getLocalizedDescription()}
		</div>
	{elseif $series}
		<div id="seriesDescription">
			{$series->getLocalizedDescription()}
		</div>
	{/if}
	<br />
	<br />
	<ul class="pkp_manageCatalog_monographList pkp_helpers_container_center">
		{iterate from=publishedMonographs item=monograph}
			{include file="manageCatalog/monograph.tpl"}
		{/iterate}
	</ul>
</div>
