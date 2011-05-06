{**
 * controllers/tab/settings/submissionStage/form/submissionStageForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission stage management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#submissionStageForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="submissionStageForm" class="pkp_controllers_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.ProcessSettingsTabHandler" op="saveFormData" tab="submissionStage"}">
	{include file="common/formErrors.tpl"}

	<h3>{translate key="manager.setup.submissionPreparationChecklist"}</h3>
	<p>{translate key="manager.setup.submissionPreparationChecklistDescription"}</p>

	{url|assign:submissionChecklistGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.submissionChecklist.SubmissionChecklistGridHandler" op="fetchGrid"}
	{load_url_in_div id="submissionChecklistGridDiv" url=$submissionChecklistGridUrl}

	{include file="form/formButtons.tpl" submitText="common.save"}
</form>