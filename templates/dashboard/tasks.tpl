{**
 * templates/dashboard/tasks.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Dashboard tasks tab.
 *
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#pressSubmissionForm').pkpHandler('$.pkp.controllers.dashboard.form.DashboardTaskFormHandler',
			{ldelim}
				{if $pressCount == 1}
					singlePressSubmissionUrl: '{url press=$press->getPath() page="submission" op="wizard"}',
				{/if}
				trackFormChanges: false
			{rdelim}
		);
	{rdelim});
</script>
<br />
<form class="pkp_form" id="pressSubmissionForm">
<!-- New Submission entry point -->
	{if $pressCount > 1}
		{fbvFormSection title="submission.submit.newSubmissionMultiple"}
			{capture assign="defaultLabel"}{translate key="context.select"}{/capture}
			{fbvElement type="select" id="multiplePress" from=$presses defaultValue=0 defaultLabel=$defaultLabel translate=false size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{elseif $pressCount == 1}
		{fbvFormSection}
			{capture assign="singleLabel"}{translate key="submission.submit.newSubmissionSingle" pressName=$press->getLocalizedName()}{/capture}
			{fbvElement type="button" id="singlePress" label=$singleLabel translate=false}
		{/fbvFormSection}
	{/if}

</form>
<div class="pkp_helpers_clear"></div>

{url|assign:notificationsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.notifications.NotificationsGridHandler" op="fetchGrid"}
{load_url_in_div id="notificationsGrid" url=$notificationsGridUrl}
