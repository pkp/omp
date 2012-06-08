{**
 * templates/workflow/production.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production workflow stage
 *}
{strip}
{include file="workflow/header.tpl"}
{/strip}

<script type="text/javascript">
	// Initialise JS handler.
	$(function() {ldelim}
		$('#production').pkpHandler(
			'$.pkp.pages.workflow.ProductionHandler',
			{ldelim}
				formatsTabContainerSelector: '#publicationFormatTabsContainer'
			{rdelim}
		);
	{rdelim});
</script>

<div id="production">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="productionNotification" requestOptions=$productionNotificationRequestOptions}

	<p>{translate key="editor.monograph.production.introduction"}</p>

	{url|assign:productionReadyFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.productionReady.ProductionReadyFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId escape=false}
	{load_url_in_div id="productionReadyFilesGridDiv" url=$productionReadyFilesGridUrl}

	{fbvFormArea id="publicationFormats"}
		{fbvFormSection}
			<!--  Formats -->
			{url|assign:formatGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.catalogEntry.PublicationFormatGridHandler" op="fetchGrid" monographId=$monograph->getId()}
			{load_url_in_div id="formatsGridContainer"|uniqid url=$formatGridUrl}
		{/fbvFormSection}
	{/fbvFormArea}

	<div id='publicationFormatTabsContainer'>
		{include file="workflow/productionFormatsTab.tpl" formatTabsId=$formatTabsId publicationFormats=$publicationFormats}
	</div>
</div>

{include file="common/footer.tpl"}
