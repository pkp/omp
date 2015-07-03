{**
 * plugins/generic/addThis/statistics.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The statistics setting tab for the AddThis plugin.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#statisticsDisplayForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>
<p>{translate key="plugins.generic.addThis.statistics.instructions"}</p>

<form class="pkp_form" id="statisticsDisplayForm">
	{url|assign:addThisStatisticsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.plugins.SettingsPluginGridHandler" op="plugin" category="generic" plugin="AddThisPlugin" verb="showStatistics" escape=false}
	{load_url_in_div id="addThisStatisticsGridContainer" url=$addThisStatisticsGridUrl}
	{fbvElement type="button" id="cancelFormButton" label="common.close"}
</form>
