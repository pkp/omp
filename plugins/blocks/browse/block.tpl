{**
 * plugins/blocks/browse/block.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site sidebar menu -- "browse" tools.
 *
 *}
<div class="block" id="sidebarBrowse">
	<span class="blockTitle">{translate key="navigation.browse"}</span>
	<form class="pkp_form" action="#">
		<div id="browseCategoryContainer">
			<select size="1" name="browseCategory" onchange="location.href=('{url|escape:"javascript" page="catalog" op="category" path="CATEGORY_PATH"}'.replace('CATEGORY_PATH', this.options[this.selectedIndex].value))" class="selectMenu">
				<option disabled="disabled" selected="selected">{translate key="plugins.block.browse.selectCategory"}</option>
				{iterate from=browseCategories item=browseCategory}
					<option value="{$browseCategory->getPath()|escape}">{$browseCategory->getLocalizedTitle()}</option>
				{/iterate}
			</select>
		</div>
		<div id="browseSeriesContainer">
			<select size="1" name="browseSeries" onchange="location.href=('{url|escape:"javascript" page="catalog" op="series" path="SERIES_PATH"}'.replace('SERIES_PATH', this.options[this.selectedIndex].value))" class="selectMenu">
				<option disabled="disabled" selected="selected">{translate key="plugins.block.browse.selectSeries"}</option>
				{iterate from=browseSeries item=browseSeriesItem}
					<option value="{$browseSeriesItem->getPath()|escape}">{$browseSeriesItem->getLocalizedTitle()}</option>
				{/iterate}
			</select>
		</div>
	</form>
</div>
