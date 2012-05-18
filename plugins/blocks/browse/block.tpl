{**
 * plugins/blocks/browse/block.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site sidebar menu -- "browse" tools.
 *
 *}
<div class="block" id="sidebarBrowse">

	<p><span class="blockTitle"><a href="{url page="catalog"}">{translate key="navigation.catalog"}</a></span></p>
	<p><span class="blockTitle"><a href="{url page="catalog" op="newReleases"}">{translate key="navigation.newReleases"}</a></span></p>

	<form class="pkp_form" action="#">
		<div id="browseCategoryContainer">
			<select size="1" name="browseCategory" onchange="location.href=('{url|escape:"javascript" page="catalog" op="category" path="CATEGORY_PATH"}'.replace('CATEGORY_PATH', this.options[this.selectedIndex].value))" class="selectMenu">
				<option disabled="disabled"{if !$browseBlockSelectedCategory} selected="selected"{/if}>{translate key="plugins.block.browse.category"}</option>
				{iterate from=browseCategories item=browseCategory}
					<option {if $browseBlockSelectedCategory == $browseCategory->getPath()}selected="selected"{/if} value="{$browseCategory->getPath()|escape}">{if $browseCategory->getParentId()}&nbsp;&nbsp;{/if}{$browseCategory->getLocalizedTitle()}</option>
				{/iterate}
			</select>
		</div>
		<div id="browseSeriesContainer">
			<select size="1" name="browseSeries" onchange="location.href=('{url|escape:"javascript" page="catalog" op="series" path="SERIES_PATH"}'.replace('SERIES_PATH', this.options[this.selectedIndex].value))" class="selectMenu">
				<option disabled="disabled"{if !$browseBlockSelectedSeries} selected="selected"{/if}>{translate key="plugins.block.browse.series"}</option>
				{iterate from=browseSeries item=browseSeriesItem}
					<option {if $browseBlockSelectedSeries == $browseSeriesItem->getPath()}selected="selected"{/if} value="{$browseSeriesItem->getPath()|escape}">{$browseSeriesItem->getLocalizedTitle()}</option>
				{/iterate}
			</select>
		</div>
	</form>
</div>
