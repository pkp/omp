{**
 * templates/management/catalogAdmin.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Catalog administration header.
 *}
{strip}
{assign var=pageTitle value="navigation.catalog.administration"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#catalogAdminTabs').pkpHandler('$.pkp.controllers.TabHandler');
	{rdelim});
</script>

<div id="catalogAdminTabs">
	<ul>
		<li><a href="{url op="categories"}">{translate key="navigation.catalog.administration.categories"}</a></li>
		<li><a href="{url op="series"}">{translate key="navigation.catalog.administration.series"}</a></li>
	</ul>
</div>

{include file="common/footer.tpl"}
