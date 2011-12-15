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
				'$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="newCatalogEntryTabs">
	<ul>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="submission" op="submissionMetadata" monographId="$monographId" stageId="$stageId"}">{translate key="submission.catalogEntry.submissionMetadata"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="catalog" op="catalogMetadata" monographId="$monographId" stageId="$stageId"}">{translate key="submission.catalogEntry.catalogMetadata"}</a></li>
		{foreach from=$publicationFormats item=format}
			<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="publication" op="publicationMetadata" publicationFormatId=$format->getId() monographId="$monographId" stageId="$stageId"}">{$format->getLocalizedName()|escape}</a></li>
		{/foreach}
</ul>
