{**
 * templates/workflow/expedite.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Confirmation check for expedite action
 *}
<script type="text/javascript">
        $(function() {ldelim}
                // Attach the form handler.
                $('#confirmExpediteForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
        {rdelim});
</script>

<form class="pkp_form" id="confirmExpediteForm" method="post" action="{url router=$smarty.const.ROUTE_PAGE save=true path=$submissionId|to_array:$stageId}">

	{translate key="submission.submit.expediteSubmission.description"}

	{fbvFormButtons submitText="common.ok" hideCancel=true}
</form>
