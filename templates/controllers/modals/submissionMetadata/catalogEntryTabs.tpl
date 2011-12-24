{**
 * controllers/modals/submissionMetadata/form/catalogEntryTabs.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
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
					{if $selectedTab}selected:{$selectedTab}{/if}
				{rdelim});
	{rdelim});
</script>
<div id="newCatalogEntryTabs">
	<ul>
		<li {if $pubId}class="ui-state-default ui-corner-top ui-state-disabled"{/if}>
			<a title="submission" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="submission" op="submissionMetadata" monographId="$monographId" stageId="$stageId"}">{translate key="submission.catalogEntry.submissionMetadata"}</a>
		</li>
		<li {if !$pubId}class="ui-state-default ui-corner-top ui-state-disabled"{/if}>
			<a title="catalog" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="catalog" op="catalogMetadata" monographId="$monographId" stageId="$stageId"}">{translate key="submission.catalogEntry.catalogMetadata"}</a>
		</li>
		{foreach from=$publicationFormats item=format}
			<li>{* no need to bother with the pubId test, since unpublished monographs will not have formats assigned to them *}
				<a title="publication{$format->getId()}" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="publication" op="publicationMetadata" publicationFormatId=$format->getId() monographId="$monographId" stageId="$stageId"}">{$format->getLocalizedName()|escape}</a>
			</li>
		{/foreach}
</ul>
