{**
 * templates/controllers/tab/authorDashboard/production.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Display the production stage on the author dashboard.
 *}
{if $submission->getData('stageId') >= $smarty.const.WORKFLOW_STAGE_ID_PRODUCTION}
	{include file="authorDashboard/submissionEmails.tpl" submissionEmails=$productionEmails}

	<!-- Display queries grid -->
	{capture assign=queriesGridUrl}{url router=PKPApplication::ROUTE_COMPONENT component="grid.queries.QueriesGridHandler" op="fetchGrid" submissionId=$submission->getId() stageId=$smarty.const.WORKFLOW_STAGE_ID_PRODUCTION escape=false}{/capture}
	{load_url_in_div id="queriesGrid" url=$queriesGridUrl}

	<!-- Display galleys grid -->
	{if $canAccessProductionStage}
		{capture assign=representationsGridUrl}{url router=PKPApplication::ROUTE_COMPONENT component="grid.catalogEntry.PublicationFormatGridHandler" op="fetchGrid" submissionId=$submission->getId() stageId=$smarty.const.WORKFLOW_STAGE_ID_PRODUCTION escape=false}{/capture}
		{load_url_in_div id="formatsGridContainer"|uniqid url=$representationsGridUrl}
	{/if}
{else}
	{translate key="submission.stageNotInitiated"}
{/if}
