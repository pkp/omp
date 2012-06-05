{**
 * templates/manageCatalog/index.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for the catalog management tabbed interface
 *}
{strip}
{assign var="pageTitle" value="catalog.manage"}
{include file="common/header.tpl"}
{/strip}

{if array_intersect(array(ROLE_ID_PRESS_MANAGER), $userRoles)}
	{assign var="isPressManager" value=true}
{/if}

<script type="text/javascript">
	// Initialize JS handler for catalog header.
	$(function() {ldelim}
		$('#catalogHeader').pkpHandler(
			'$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler',
			{ldelim}
				searchTabIndex: {if $isPressManager}4{else}3{/if},
				spotlightTabName: 'spotlightsTab',
				seriesFetchUrlTemplate: '{url|escape:"javascript" op="series" path=SERIES_PATH escape=false}',
				categoryFetchUrlTemplate: '{url|escape:"javascript" op="category" path=CATEGORY_PATH escape=false}',
				spotlightsUrl: '{url router=$smarty.const.ROUTE_COMPONENT component="tab.content.ContentTabHandler" op="showTab" tab="spotlights"}'
			{rdelim}
		);
	{rdelim});
	// Initialize JS handler for search form.
	$(function() {ldelim}
		$('#catalogSearchForm').pkpHandler(
			'$.pkp.pages.manageCatalog.ManageCatalogSearchFormHandler'
		);
	{rdelim});
	// Initialize "Select Series" form handler
	$(function() {ldelim}
		$('#selectSeriesForm').pkpHandler(
			'$.pkp.controllers.form.DropdownFormHandler',
			{ldelim}
				getOptionsUrl: '{url|escape:"javascript" op="getSeries" escape=false}',
				eventName: 'selectSeries'
			{rdelim}
		);
	{rdelim});
	// Initialize "Select Category" form handler
	$(function() {ldelim}
		$('#selectCategoryForm').pkpHandler(
			'$.pkp.controllers.form.DropdownFormHandler',
			{ldelim}
				getOptionsUrl: '{url|escape:"javascript" op="getCategories" escape=false}',
				eventName: 'selectCategory'
			{rdelim}
		);
	{rdelim});
</script>

<div id="catalogHeader">
	<p>{translate key="catalog.manage.managementIntroduction"}</p>
	<div id="catalogSearchContainer">
		<form id="catalogSearchForm" class="pkp_form" action="{url op="search" path="SEARCH_TEXT_DUMMY"}" method="post">
			{fbvFormSection title="common.search"}
				{fbvElement type="text" id="catalogSearch" name="catalogSearch"}
			{/fbvFormSection}
		</form>
	</div>

	<div class="pkp_page_header pkp_helpers_align_right">
		<ul class="submission_actions pkp_helpers_flatlist">
			<li>{include file="linkAction/linkAction.tpl" action=$catalogEntryAction}</li>
		</ul>
	</div>

	<div id="catalogTabs">
		<ul>
			<li><a href="{url op="homepage"}">{translate key="catalog.manage.homepage"}</a></li>
			<li><a href="#categoryTab">{translate key="catalog.manage.category"}</a></li>
			<li><a href="#seriesTab">{translate key="catalog.manage.series"}</a></li>
			{if $isPressManager}<li><a href="#spotlightsTab">{translate key="spotlight.spotlights"}</a></li>{/if}
			<li><a href="{url}">{translate key="search.searchResults"}</a></li>
		</ul>
		<div id="categoryTab">
			<form id="selectCategoryForm" class="pkp_form">
				{if array_intersect(array(ROLE_ID_PRESS_MANAGER), $userRoles)}
					<div class="pkp_helpers_align_right">
						<ul class="submission_actions pkp_helpers_flatlist pkp_linkActions">
							<li>{include file="linkAction/linkAction.tpl" action=$manageCategoriesLinkAction}</li>
						</ul>
					</div><br />
				{/if}
				{fbvFormArea id="forCategorySelect"}
					{fbvFormSection}
						{fbvElement type="select" id="categorySelect" from="catalog.selectCategory"|translate|to_array translate=false}
					{/fbvFormSection}
				{/fbvFormArea}
			</form>

			<div id="categoryContainer">
				{* This will be filled via JS when a category is chosen. *}
			</div>
		</div>
		<div id="seriesTab">
			<form id="selectSeriesForm" class="pkp_form">
			{if array_intersect(array(ROLE_ID_PRESS_MANAGER), $userRoles)}
				<div class="pkp_helpers_align_right">
					<ul class="submission_actions pkp_helpers_flatlist pkp_linkActions">
						<li>{include file="linkAction/linkAction.tpl" action=$manageSeriesLinkAction}</li>
					</ul>
				</div><br />
			{/if}
				{fbvFormArea id="forSeriesSelect"}
					{fbvFormSection}
						{fbvElement type="select" id="seriesSelect" from="catalog.selectSeries"|translate|to_array translate=false}
					{/fbvFormSection}
				{/fbvFormArea}
			</form>

			<div id="seriesContainer">
				{* This will be filled via JS when a series is chosen. *}
			</div>
		</div>
		{if $isPressManager}
			<div id="spotlightsTab">
				{* Filled in when the spotlights tab is selected *}
			</div>
		{/if}
	</div>
</div>

{include file="common/footer.tpl"}
