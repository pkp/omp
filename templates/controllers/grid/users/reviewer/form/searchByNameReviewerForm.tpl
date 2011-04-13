{**
 * templates/controllers/grid/user/reviewer/form/searchByNameReviewerForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Search By Name and assignment reviewer form
 *
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#searchByNameReviewerForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="searchByNameReviewerForm" method="post" action="{url op="updateReviewer"}" >
	<h3>{translate key="manager.reviewerSearch.searchByName"}</h3>
	{fbvFormSection}
		{url|assign:autocompleteUrl op="finishFileSubmission" op="getReviewersNotAssignedToMonograph" monographId=$monographId reviewType=$reviewType round=$round escape=false}
		{fbvElement type="autocomplete" autocompleteUrl=$autocompleteUrl id="reviewerId" label="user.role.reviewer" value=$userNameString|escape}
	{/fbvFormSection}

	{include file="controllers/grid/users/reviewer/form/reviewerFormFooter.tpl"}

	{include file="form/formButtons.tpl" submitText="editor.monograph.addReviewer"}
</form>

