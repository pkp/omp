{**
 * templates/manageCatalog/index.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for the catalog management tabbed interface
 *}
{if array_intersect(array(ROLE_ID_MANAGER), $userRoles)}
	{assign var="isManager" value=true}
{/if}

<script type="text/javascript">
	// Initialize JS handler for catalog header.
	$(function() {ldelim}
		$('#catalogHeader').pkpHandler(
			'$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler',
			{ldelim}
				searchTabIndex: {if $isManager}4{else}3{/if},
				spotlightTabName: 'spotlightsTab',
				seriesFetchUrlTemplate: {url|json_encode op="series" path=SERIES_PATH escape=false},
				categoryFetchUrlTemplate: {url|json_encode op="category" path=CATEGORY_PATH escape=false},
				spotlightsUrl: {url|json_encode router=$smarty.const.ROUTE_COMPONENT component="tab.content.ContentTabHandler" op="showTab" tab="spotlights" escape=false}
			{rdelim}
		);
	{rdelim});
	// Initialize JS handler for search form.
	$(function() {ldelim}
		$('#catalogSearchForm').pkpHandler(
			'$.pkp.pages.manageCatalog.ManageCatalogSearchFormHandler',
			{ldelim}
				trackFormChanges: false
			{rdelim}
		);
	{rdelim});
	// Initialize "Select Series" form handler
	$(function() {ldelim}
		$('#selectSeriesForm').pkpHandler(
			'$.pkp.controllers.form.DropdownHandler',
			{ldelim}
				getOptionsUrl: {url|json_encode op="getSeries" escape=false},
				eventName: 'selectSeries'
			{rdelim}
		);
	{rdelim});
	// Initialize "Select Category" form handler
	$(function() {ldelim}
		$('#selectCategoryForm').pkpHandler(
			'$.pkp.controllers.form.DropdownHandler',
			{ldelim}
				getOptionsUrl: {url|json_encode op="getCategories" escape=false},
				eventName: 'selectCategory'
			{rdelim}
		);
	{rdelim});
</script>

<div id="catalogHeader">
	<div class="pkp_page_header pkp_helpers_align_right">
		<ul class="submission_actions pkp_helpers_flatlist">
			<li>{include file="linkAction/linkAction.tpl" action=$catalogEntryAction}</li>
		</ul>
	</div>
	<div class="pkp_helpers_clear"></div>
	<p>{translate key="catalog.manage.managementIntroduction"}</p>
	<div id="catalogSearchContainer">
		<form id="catalogSearchForm" class="pkp_form" action="{url op="search" path="SEARCH_TEXT_DUMMY"}" method="post">
			{fbvFormSection title="common.search"}
				{fbvElement type="text" id="catalogSearch" name="catalogSearch" inline=true size=$fbvStyles.size.LARGE}
				{fbvElement type="submit" id="submitFormButton" label="common.search" inline=true}
			{/fbvFormSection}
		</form>
	</div>

	<div class="pkp_helpers_clear"></div>
	<div id="catalogTabs" class="pkp_controllers_tab">
		<ul>
			<li><a name="manageHomepage" href="{url op="homepage"}">{translate key="catalog.manage.homepage"}</a></li>
			<li><a name="manageCategory" href="#categoryTab">{translate key="catalog.manage.category"}</a></li>
			<li><a name="manageSeries" href="#seriesTab">{translate key="catalog.manage.series"}</a></li>
			{if $isManager}<li><a name="manageSpotlights" href="#spotlightsTab">{translate key="spotlight.spotlights"}</a></li>{/if}
			<li><a name="manageSearchResults" href="{url}">{translate key="search.searchResults"}</a></li>
		</ul>
		<div id="categoryTab">
				<div class="pkp_controllers_grid">
					<div class="pkp_helpers_align_right grid_header_bar pkp_helpers_full">
						<h3 class="pkp_helpers_align_left">{translate key="catalog.selectCategory"}</h3>
						{if array_intersect(array(ROLE_ID_MANAGER), $userRoles)}
							<ul class="submission_actions pkp_helpers_flatlist pkp_linkActions pkp_helpers_align_right">
								<li>{include file="linkAction/linkAction.tpl" action=$manageCategoriesLinkAction}</li>
							</ul>
						{/if}
					</div>
				</div>

			<div class="pkp_helpers_clear"></div>
			<form id="selectCategoryForm" class="pkp_form">
				{fbvFormArea id="forCategorySelect"}
					{fbvFormSection}
						{fbvElement type="select" id="categorySelect" translate=false size=$fbvStyles.size.MEDIUM class="noStyling"}
					{/fbvFormSection}
				{/fbvFormArea}
			</form>

			<div id="categoryContainer">
				{* This will be filled via JS when a category is chosen. *}
			</div>
		</div>
		<div id="seriesTab">
				<div class="pkp_controllers_grid">
					<div class="pkp_helpers_align_right grid_header_bar pkp_helpers_full">
						<h3 class="pkp_helpers_align_left">{translate key="catalog.selectSeries"}</h3>
						{if array_intersect(array(ROLE_ID_MANAGER), $userRoles)}
							<ul class="submission_actions pkp_helpers_flatlist pkp_linkActions pkp_helpers_align_right">
								<li>{include file="linkAction/linkAction.tpl" action=$manageSeriesLinkAction}</li>
							</ul>
						{/if}
					</div>
				</div>

			<form id="selectSeriesForm" class="pkp_form">
				<div class="pkp_helpers_clear"></div>
				{fbvFormArea id="forSeriesSelect"}
					{fbvFormSection}
						{fbvElement type="select" id="seriesSelect" translate=false size=$fbvStyles.size.MEDIUM class="noStyling"}
					{/fbvFormSection}
				{/fbvFormArea}
			</form>

			<div id="seriesContainer">
				{* This will be filled via JS when a series is chosen. *}
			</div>
		</div>
		{if $isManager}
			<div id="spotlightsTab">
				<p>{translate key="catalog.manage.spotlightDescription"}</p>
				{url|assign:spotlightsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.content.spotlights.ManageSpotlightsGridHandler" op="fetchGrid" escape=false}
				{load_url_in_div id="spotlightsGridContainer" url=$spotlightsGridUrl}
			</div>
		{/if}
	</div>
</div>
