{**
 * templates/management/tools/form/statisticsSettingsForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Statistics settings form.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#statisticsSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="statisticsSettingsForm" method="post" action="{url op="tools" path="saveStatisticsSettings"}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="statisticsSettingsNotification"}
	
	{include file="core:statistics/defaultMetricTypeFormElements.tpl"}
	
	{fbvFormButtons id="statisticsSettingsFormSubmit" submitText="common.save" hideCancel=true}
</form>
