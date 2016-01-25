{**
 * templates/manageCatalog/monographs.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
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
			'$.pkp.pages.manageCatalog.MonographManagementListHandler'
		);
	{rdelim});
</script>
{if $messageKey}<p>{translate key=$messageKey}</p>{/if}
<div id="{$monographContainerId|escape}">
	{if $category}
		<div id="categoryDescription">
			{$category->getLocalizedDescription()}
		</div>
	{elseif $series}
		<div id="seriesDescription">
			{$series->getLocalizedDescription()}
		</div>
	{/if}
	<div class="pkp_helpers_align_right">
		<div class="submission_actions pkp_linkActions">
			{if $includeFeatureAction && !$publishedMonographs->wasEmpty()}{null_link_action id="feature-$monographContainerId" key="common.feature" image="feature"}{/if}
		</div>
	</div>
	<br />
	<br />
	<ul class="pkp_manageCatalog_monographList pkp_helpers_container_center">
		{if !$publishedMonographs->wasEmpty()}
			{iterate from=publishedMonographs item=monograph}
				{include file="manageCatalog/monograph.tpl"}
			{/iterate}
		{else}
			<p>{translate key="catalog.manage.noMonographs"}</p>
		{/if}
	</ul>
</div>
