{**
 * templates/manager/series.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press management series list.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#seriesGridFormContainer').pkpHandler('$.pkp.pages.manageCatalog.ManageCatalogModalHandler');
	{rdelim});
</script>
<form class="pkp_form" id="seriesGridFormContainer">
	{url|assign:seriesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.series.SeriesGridHandler" op="fetchGrid" escape=false}
	{load_url_in_div id="seriesGridContainer" url=$seriesGridUrl}
	{if !$hideClose}
		<div class="pkp_helpers_align_right">
			{fbvElement type="button" label="common.close" id="cancelFormButton"}
		</div>
	{/if}
</form>
