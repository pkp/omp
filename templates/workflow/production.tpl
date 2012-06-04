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
				accordionUrl: '{url|escape:"javascript" op="productionFormatsAccordion" monographId=$monograph->getId() stageId=$smarty.const.WORKFLOW_STAGE_ID_PRODUCTION escape=false}'
			{rdelim}
		);
	{rdelim});
</script>

<div id="production">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="productionNotification" requestOptions=$productionNotificationRequestOptions}

	<p>{translate key="editor.monograph.production.introduction"}</p>

	{url|assign:productionReadyFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.productionReady.ProductionReadyFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId escape=false}
	{load_url_in_div id="productionReadyFilesGridDiv" url=$productionReadyFilesGridUrl}

	<h3 class="pkp_grid_title">{translate key="editor.monograph.production.approvalAndPublishing"}</h3>

	<p class="pkp_grid_description">{translate key="editor.monograph.production.approvalAndPublishingDescription"}</p>

	{fbvFormArea id="publicationFormats"}
		{fbvFormSection}
			<!--  Formats -->
			{url|assign:formatGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.catalogEntry.PublicationFormatGridHandler" op="fetchGrid" monographId=$monograph->getId()}
			{load_url_in_div id="formatsGridContainer"|uniqid url=$formatGridUrl}
		{/fbvFormSection}
	{/fbvFormArea}

	<div id="publicationFormatContainer">
		{* Will be filled in by Javascript *}
	</div>
</div>
</div>

{include file="common/footer.tpl"}
