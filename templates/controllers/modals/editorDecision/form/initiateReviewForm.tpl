{**
 * initiateReviewForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to initiate the first review round.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#initiateReview').pkpHandler('$.pkp.controllers.form.FormHandler', null);
	{rdelim});
</script>

<p>{translate key="editor.monograph.initiateReviewDescription"}</p>
<form id="initiateReview" method="post" action="{url op="saveInitiateReview"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />

	<!-- Available submission files -->
	{url|assign:filesForReviewUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submission.SelectableSubmissionDetailsFilesGridHandler" op="fetchGrid" monographId=$monographId escape=false}
	{load_url_in_div id="filesForReviewGrid" url=$filesForReviewUrl}
	{include file="form/formButtons.tpl" submitText="editor.monograph.createNewRound"}
</form>


