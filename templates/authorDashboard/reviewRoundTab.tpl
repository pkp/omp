{**
 * templates/authorDashboard/reviewRoundTab.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Build a review round tab markup (for any review stage).
 *}

{iterate from=reviewRounds item=reviewRound}
	<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.authorDashboard.AuthorDashboardReviewRoundTabHandler" op="fetchReviewRoundInfo" monographId=$monograph->getId() stageId=$reviewRound->getStageId() reviewRoundId=$reviewRound->getId() escape=false}">{translate key="submission.round" round=$reviewRound->getRound()}</a></li>
{/iterate}