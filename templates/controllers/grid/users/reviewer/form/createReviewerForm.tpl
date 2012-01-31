{**
 * templates/controllers/grid/users/reviewer/crateReviewerForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Create a reviewer and assign to a submission form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#createReviewerForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="createReviewerForm" method="post" action="{url op="updateReviewer"}" >
	<h3>{translate key="editor.review.createReviewer"}</h3>
	{fbvFormSection title="user.group"}
		{fbvElement type="select" name="userGroupId" id="userGroupId" from=$userGroups translate=false label="editor.review.userGroupSelect" required="true"}
	{/fbvFormSection}
	{fbvFormSection title="common.name"}
		{fbvElement type="text" label="user.firstName" id="firstname" value=$firstName required="true"}
		{fbvElement type="text" label="user.middleName" id="middlename" value=$middleName}
		{fbvElement type="text" label="user.lastName" id="lastname" value=$lastName required="true"}
	{/fbvFormSection}

	{fbvFormSection title="user.affiliation" for="affiliation"}
		{fbvElement type="textarea" id="affiliation" value=$affiliation}
	{/fbvFormSection}

	{fbvFormSection title="manager.reviewerSearch.interests"}
		{fbvElement type="interests" id="interests" interestKeywords=$interestsKeywords interestsTextOnly=$interestsTextOnly}
	{/fbvFormSection}

	{fbvFormSection title="user.accountInformation"}
		{fbvElement type="text" label="user.username" id="username" value=$username required="true"} <br />
	{/fbvFormSection}

	{fbvFormSection title="user.email"}
		{fbvElement type="text" id="email" class="email" value=$email required="true"}
	{/fbvFormSection}

	{include file="controllers/grid/users/reviewer/form/reviewerFormFooter.tpl"}

	{fbvFormButtons submitText="editor.monograph.addReviewer"}
</form>