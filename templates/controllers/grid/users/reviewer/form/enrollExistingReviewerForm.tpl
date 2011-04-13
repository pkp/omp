{**
 * templates/controllers/grid/user/reviewer/form/enrollExistingReviewerForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Enroll existing user and assignment reviewer form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#enrollExistingReviewerForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="enrollExistingReviewerForm" method="post" action="{url op="updateReviewer"}" >
	<h3>{translate key="editor.review.enrollReviewer"}</h3>
	{fbvFormSection title="user.group"}
		{fbvElement type="select" name="userGroupId" id="userGroupId" from=$userGroups translate=false label="editor.review.userGroupSelect" required="true"}
	{/fbvFormSection}
	{fbvFormSection}
		{url|assign:autocompleteUrl op="getUsersNotAssignedAsReviewers" monographId=$monographId reviewType=$reviewType round=$round escape=false}
		{fbvElement type="autocomplete" autocompleteUrl=$autocompleteUrl id="userId" label="user.role.reviewer" value=$userNameString|escape}
	{/fbvFormSection}

	{include file="controllers/grid/users/reviewer/form/reviewerFormFooter.tpl"}

	{include file="form/formButtons.tpl" submitText="editor.monograph.addReviewer"}
</form>
