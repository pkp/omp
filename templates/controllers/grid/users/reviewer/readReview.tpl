{**
 * templates/controllers/grid/users/reviewer/readReview.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Screen to let user read a review.
 *
 *}
{capture assign="reviewerRecommendations"}
	{* Not implemented in OMP *}
{/capture}

{if $reviewAssignment->getDateCompleted()}
	{assign var="reviewCompleted" value=true}
{else}
	{assign var="reviewCompleted" value=false}
{/if}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#readReviewForm').pkpHandler('$.pkp.controllers.grid.users.reviewer.ReadReviewHandler', {ldelim}
				reviewCompleted: {$reviewCompleted|json_encode}
		{rdelim});
	{rdelim});
</script>

{include file="core:controllers/grid/users/reviewer/readReview.tpl"}
