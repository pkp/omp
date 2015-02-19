{**
 * templates/authorDashboard/stages/internalReview.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the internal review stage on the author dashboard.
 *}
{if array_key_exists($smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW, $accessibleWorkflowStages)}
	<div class="pkp_authorDashboard_stageContainer" id="internalReview">
		<h3><a href="#">{translate key='workflow.review.internalReview'}</a></h3>
		<div id="internalReviewContent">
			{if $stageId >= $smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW && !$internalReviewRounds->wasEmpty()}
				{include file="authorDashboard/reviewRoundTab.tpl" reviewRounds=$internalReviewRounds reviewRoundTabsId="internalReviewRoundTabs" lastReviewRoundNumber=$lastReviewRoundNumber.internalReview}
			{else}
				{translate key="submission.stageNotInitiated"}
			{/if}
		</div>
	</div>
{/if}
