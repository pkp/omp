{**
 * controllers/modals/submissionMetadata/form/catalogEntryTabs.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a submission's catalog entry form.
 *
 *}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#newCatalogEntryTabs').pkpHandler(
				'$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler',
				{ldelim}
					{if $selectedTab}selected:{$selectedTab},{/if}
					{if $selectedFormatId}selectedFormatId:{$selectedFormatId},{/if}
					{if $tabsUrl}tabsUrl:'{$tabsUrl}',{/if}
					{if $tabContentUrl}tabContentUrl:'{$tabContentUrl}',{/if}
					emptyLastTab: true
				{rdelim});
	{rdelim});
</script>
<div id="newCatalogEntryTabs" class="pkp_controllers_tab">
	<ul>
		<li>
			<a title="submission" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="submission" op="submissionMetadata" submissionId=$submissionId stageId=$stageId submissionVersion=$submissionVersion tabPos="0"}">{translate key="submission.catalogEntry.monographMetadata"}</a>
		</li>
		<li>
			<a title="catalog" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="catalog" op="catalogMetadata" submissionId=$submissionId submissionVersion=$submissionVersion stageId=$stageId tabPos="1"}">{translate key="submission.catalogEntry.catalogMetadata"}</a>
		</li>
		<li>
			<a name="catalog" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="identifiers" op="identifiers" submissionId=$submissionId submissionVersion=$submissionVersion stageId=$stageId tabPos="2"}">{translate key="submission.identifiers"}</a>
		</li>
		{counter start=3 assign="counter"}
		{if $citationsEnabled}
			<li>
				<a name="citations" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="citations" op="citations" submissionId=$submissionId submissionVersion=$submissionVersion stageId=$stageId tabPos=$counter}">{translate key="submission.citations"}</a>
			</li>
			{counter}
		{/if}
		{call_hook name="Templates::Controllers::Modals::SubmissionMetadata::CatalogEntryTabs::Tabs"}
		{foreach from=$publicationFormats item=format}
			<li>
				<a id="publication{$format->getId()|escape}"
					href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler"
					tab="publication"|concat:$format->getId()
					op="publicationMetadata"
					representationId=$format->getId()
					submissionId=$submissionId
					stageId=$stageId
					submissionVersion=$submissionVersion
					tabPos=$counter}">{$format->getLocalizedName()|escape}</a>
			</li>
			{counter} {* increment our counter, assign to $counter variable *}
		{/foreach}
</ul>
