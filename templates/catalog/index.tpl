{**
 * templates/catalog/index.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header that contains details about the submission
 *}
{strip}
{assign var="pageTitle" value="catalog.manage"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Initialize JS handler for catalog header.
	$(function() {ldelim}
		$('#catalogHeader').pkpHandler(
			'$.pkp.pages.catalog.CatalogHeaderHandler',
			{ldelim}
				searchTabIndex: 4
			{rdelim}
		);
	{rdelim});
	// Initialize JS handler for search form.
	$(function() {ldelim}
		$('#catalogSearchForm').pkpHandler(
			'$.pkp.pages.catalog.CatalogSearchFormHandler'
		);
	{rdelim});
</script>

<div id="catalogHeader">
	<div id="catalogSearchContainer">
		<form id="catalogSearchForm" class="pkp_form" action="{url op="search" path="SEARCH_TEXT_DUMMY"}" method="post">
			{fbvFormSection title="common.search"}
				{fbvElement type="text" id="catalogSearch" name="catalogSearch"}
			{/fbvFormSection}
		</form>
	</div>

	<div class="pkp_submission_header pkp_helpers_align_right">
		<ul class="submission_actions pkp_helpers_flatlist">
			<li>{include file="linkAction/linkAction.tpl" action=$catalogEntryAction}</li>
		</ul>
	</div>

	<div id="catalogTabs">
		<ul>
			<li><a href="{url op="features"}">{translate key="catalog.manage.features"}</a></li>
			<li><a href="{url op="newReleases"}">{translate key="catalog.manage.newReleases"}</a></li>
			<li><a href="{url op="category"}">{translate key="catalog.manage.category"}</a></li>
			<li><a href="{url op="series"}">{translate key="catalog.manage.series"}</a></li>
			<li><a href="{url}">{translate key="search.searchResults"}</a></li>
		</ul>
	</div>
</div>

{include file="common/footer.tpl"}
