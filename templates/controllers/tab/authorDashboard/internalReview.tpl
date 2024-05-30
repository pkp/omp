{**
 * templates/authorDashboard/stages/internalReview.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Display the internal review stage on the author dashboard.
 *}

{if $submission->getData('stageId') >= $smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW && count($reviewRounds)}
	{include file="authorDashboard/reviewRoundTab.tpl" reviewRounds=$reviewRounds reviewRoundTabsId="internalReviewRoundTabs" lastReviewRoundNumber=$lastReviewRoundNumber}

	<!-- Display queries grid -->
	{capture assign=queriesGridUrl}{url router=PKP\core\PKPApplication::ROUTE_COMPONENT component="grid.queries.QueriesGridHandler" op="fetchGrid" submissionId=$submission->getId() stageId=$smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW escape=false}{/capture}
	{load_url_in_div id="queriesGrid" url=$queriesGridUrl}
{else}
	{translate key="submission.stageNotInitiated"}
{/if}
