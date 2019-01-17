{**
 * templates/controllers/grid/manageCatalog/form/catalogMonographsFilterForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Catalog monographs grid filter form.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#catalogMonographsFilterForm-{$grid->getId()}').pkpHandler('$.pkp.controllers.form.ToggleFormHandler');
	{rdelim});
</script>

<form class="pkp_form filter" id="catalogMonographsFilterForm-{$grid->getId()}">
	{fbvFormArea id="catalogMonographFilterElements"}
		{fbvFormSection list=true}
		{if $filterData.categoryId}
			{fbvElement type="select" label="catalog.selectCategory" id=categoryId from=$filterData.categoryOptions selected=$filterData.categoryId translate=false size=$fbvStyles.size.SMALL inline=true}
		{elseif $filterData.seriesId}
			{fbvElement type="select" label="catalog.selectSeries" id=seriesId from=$filterData.seriesOptions selected=$filterData.seriesId translate=false size=$fbvStyles.size.SMALL inline=true}
		{/if}
			{fbvElement type="text" id="searchText" value=$filterData.searchText label="catalog.manage.filter.searchByAuthorOrTitle" size=$fbvStyles.size.SMALL inline=true}
			{fbvElement type="checkbox" id="featured" checked=$filterData.featured value=1 label="common.feature" inline=true}
			{fbvElement type="checkbox" id="newReleased" checked=$filterData.newReleased value=1 label="catalog.manage.newReleases" inline=true}
			{fbvFormButtons submitText="common.search" hideCancel=true}
		{/fbvFormSection}
	{/fbvFormArea}
</form>

