{**
 * controllers/tab/settings/categoriesAndSeries/form/categoriesAndSeriesForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Category and series management form.
 *
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#categoriesAndSeriesForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="categoriesAndSeriesForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="categoriesAndSeries"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="categoriesAndSeriesFormNotification"}

	{url|assign:categoriesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.CategoriesListbuilderHandler" op="fetch"}
	{load_url_in_div id="categoriesContainer" url=$categoriesUrl}

	{url|assign:seriesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.series.SeriesGridHandler" op="fetchGrid"}
	{load_url_in_div id="seriesGridDiv" url=$seriesGridUrl}

	{fbvFormButtons id="categoriesAndSeriesFormSubmit" submitText="common.save" hideCancel=true}
</form>
