{**
 * templates/controllers/grid/user/reviewer/form/defaultReviewerForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * A default reviewer selection form.
 *
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#defaultReviewerForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="searchByNameReviewerForm" method="post" action="{url op="updateReviewer"}" >

	{include file="controllers/grid/users/reviewer/form/reviewerFormFooter.tpl"}

	{include file="form/formButtons.tpl" submitText="editor.monograph.addReviewer"}
</form>

