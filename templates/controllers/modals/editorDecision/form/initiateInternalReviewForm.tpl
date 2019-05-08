{**
 * templates/controllers/modals/editorDecision/form/initiateInternalReviewForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to initiate the first review round of an internal review.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#initiateReview').pkpHandler('$.pkp.controllers.form.AjaxFormHandler', null);
	{rdelim});
</script>

<p>{translate key="editor.monograph.internalReviewDescription"}</p>
<form class="pkp_form" id="initiateReview" method="post" action="{url op="saveInternalReview"}" >
	{csrf}
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />

	<!-- Available submission files -->
	{capture assign=filesForReviewUrl}{url router=$smarty.const.ROUTE_COMPONENT component="grid.files.submission.SelectableSubmissionDetailsFilesGridHandler" op="fetchGrid" submissionId=$submissionId stageId=$stageId escape=false}{/capture}
	{load_url_in_div id="filesForReviewGrid" url=$filesForReviewUrl}
	{fbvFormButtons submitText="editor.submission.decision.sendInternalReview"}
</form>
