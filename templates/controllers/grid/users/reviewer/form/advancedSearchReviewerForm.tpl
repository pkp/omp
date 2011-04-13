{**
 * templates/controllers/grid/user/reviewer/form/advancedSearchReviewerForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Advanced Search and assignment reviewer form.
 *
 *}
 
<script type="text/javascript"> 
	$(function() {ldelim}
		// Attach the form handler.
		$('#advancedSearchReviewerForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="advancedSearchReviewerForm" method="post" action="{url op="updateReviewer"}" >
	<div id="advancedSearch">
		<h3>{translate key="manager.reviewerSearch.advancedSearch"}</h3>
		{url|assign:reviewerSelectorUrl router=$smarty.const.ROUTE_COMPONENT component="reviewerSelector.ReviewerSelectorHandler" op="fetchForm" monographId=$monographId}
		{load_url_in_div id="reviewerSelectorContainer" url="$reviewerSelectorUrl"}
	</div>
	
	{include file="controllers/grid/users/reviewer/form/reviewerFormFooter.tpl"}
	
	{include file="form/formButtons.tpl" submitText="editor.monograph.addReviewer"}
</form>

