{**
 * templates/controllers/tab/workflow/production.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production workflow stage
 *}

<div id="production">

	{* Help Link *}
	{help file="editorial-workflow/production.md" class="pkp_help_tab"}

	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="productionNotification" requestOptions=$productionNotificationRequestOptions}

	<div class="pkp_context_sidebar">
		{include file="controllers/tab/workflow/stageParticipants.tpl"}
	</div>

	<div class="pkp_content_panel">
		{capture assign=productionReadyFilesGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="grid.files.productionReady.ProductionReadyFilesGridHandler" op="fetchGrid" submissionId=$submission->getId() stageId=$stageId escape=false}{/capture}
		{load_url_in_div id="productionReadyFilesGridDiv" url=$productionReadyFilesGridUrl}

		{capture assign=queriesGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="grid.queries.QueriesGridHandler" op="fetchGrid" submissionId=$submission->getId() stageId=$stageId escape=false}{/capture}
		{load_url_in_div id="queriesGrid" url=$queriesGridUrl}

		{capture assign=representationsGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.PublicationFormatGridHandler" op="fetchGrid" submissionId=$submission->getId() escape=false}{/capture}
		{load_url_in_div id="formatsGridContainer"|uniqid url=$representationsGridUrl}
	</div>
</div>
