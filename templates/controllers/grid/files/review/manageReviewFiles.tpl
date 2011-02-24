{**
 * manageReviewFiles.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Allows editor to add more file to the review (that weren't added when the submission was sent to review)
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#manageReviewFilesForm').pkpHandler('$.pkp.controllers.FormHandler');
	{rdelim});
</script>

<!-- Current review files -->
<h4>{translate key="editor.submissionArchive.currentFiles" round=$round}</h4>

<div id="existingFilesContainer">
	<form id="manageReviewFilesForm" action="{url component="grid.files.review.SelectableEditorReviewFilesGridHandler" op="updateReviewFiles" monographId=$monographId|escape reviewType=$reviewType|escape round=$round|escape}" method="post">
		<!-- Available submission files -->
		{url|assign:availableReviewFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.review.SelectableEditorReviewFilesGridHandler" op="fetchGrid" monographId=$monographId reviewType=$reviewType round=$round escape=false}
		{load_url_in_div id="availableReviewFilesGrid" url=$availableReviewFilesGridUrl}
		{init_button_bar id="#existingFilesContainer"}
	</form>
</div>

