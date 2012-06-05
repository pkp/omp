{**
 * templates/manager/series.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press management series list.
 *}

<p>{translate key="manager.setup.series.description"}</p>

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#seriesGridFormContainer').pkpHandler('$.pkp.pages.manageCatalog.ManageCatalogModalHandler');
	{rdelim});
</script>
<form class="pkp_form" id="seriesGridFormContainer">
	{url|assign:seriesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.series.SeriesGridHandler" op="fetchGrid"}
	{load_url_in_div id="seriesGridContainer" url=$seriesGridUrl}
	{fbvElement type="link" class="cancelFormButton" id="cancelFormButton" label="common.cancel"}
</form>

