{**
 * controllers/modals/submissionMetadata/form/catalogEntryTabs.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a submission's catalog entry form.
 *
 *}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#newCatalogEntryTabs').pkpHandler(
				'$.pkp.controllers.TabHandler', 
				{ldelim}
					{if $selectedTab}selected:{$selectedTab},{/if}
					emptyLastTab: true
				{rdelim});
	{rdelim});
</script>
<div id="newCatalogEntryTabs">
	<ul>
		<li {if $published}class="ui-state-default ui-corner-top ui-state-disabled"{/if}>
			<a title="submission" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="submission" op="submissionMetadata" monographId="$monographId" stageId="$stageId" tabPos="0"}">{translate key="submission.catalogEntry.submissionMetadata"}</a>
		</li>
		<li {if !$published}class="ui-state-default ui-corner-top ui-state-disabled"{/if}>
			<a title="catalog" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="catalog" op="catalogMetadata" monographId="$monographId" stageId="$stageId" tabPos="1"}">{translate key="submission.catalogEntry.catalogMetadata"}</a>
		</li>
		{counter start=2 assign="counter"}
		{foreach from=$publicationFormats item=format}
			<li>{* no need to bother with the published test, since unpublished monographs will not have formats assigned to them *}
				<a title="publication{$format->getAssignedPublicationFormatId()}" 
					href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" 
					tab="publication"|concat:$format->getAssignedPublicationFormatId()|escape
					op="publicationMetadata" 
					assignedPublicationFormatId=$format->getAssignedPublicationFormatId() 
					monographId="$monographId" 
					stageId="$stageId" 
					tabPos="$counter"}">{$format->getLocalizedTitle()|escape}</a>
			</li>
		{counter} {* increment our counter, assign to $counter variable *}
		{/foreach}
</ul>
