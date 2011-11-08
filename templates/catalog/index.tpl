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
	// Initialise JS handler.
	$(function() {ldelim}
		$('#catalogHeader').pkpHandler(
			'$.pkp.pages.catalog.CatalogHeaderHandler'
		);
	{rdelim});
</script>

<div id="catalogHeader" class="pkp_submission_header">
	<div style="float:right">
		<ul class="submission_actions pkp_helpers_flatlist">
			<li>{include file="linkAction/linkAction.tpl" action=$catalogEntryAction}</li>
		</ul>
	</div>
	<div id="catalogTabs">
		<ul>
			<li><a href="#">{translate key="catalog.manage.features"}</a></li>
			<li><a href="#">{translate key="catalog.manage.newReleases"}</a></li>
			<li><a href="#">{translate key="catalog.manage.category"}</a></li>
			<li><a href="#">{translate key="catalog.manage.series"}</a></li>
		</ul>
	</div>
</div>

{include file="common/footer.tpl"}
