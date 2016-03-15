{**
 * templates/manager/categories.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press management categories list.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#categoriesGridFormContainer').pkpHandler('$.pkp.pages.manageCatalog.ManageCatalogModalHandler');
	{rdelim});
</script>
<form class="pkp_form" id="categoriesGridFormContainer">
	{help file="chapter6/context/categories.md" class="pkp_helpers_align_right"}
	<div class="pkp_helpers_clear"></div>
	{url|assign:categoriesUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.category.CategoryCategoryGridHandler" op="fetchGrid" escape=false}
	{load_url_in_div id="categoriesContainer" url=$categoriesUrl}
</form>
