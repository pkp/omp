{**
 * templates/admin/settings.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Administration settings page, with tabs.
 *}
{strip}
{assign var="pageTitle" value="admin.settings"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#settingsTabs').pkpHandler('$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="settingsTabs" class="pkp_controllers_tab">
	<ul>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.AdminSettingsTabHandler" op="showTab" tab="siteSetup"}">{translate key="admin.siteSetup"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.AdminSettingsTabHandler" op="showTab" tab="languages"}">{translate key="common.languages"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.AdminSettingsTabHandler" op="showTab" tab="plugins"}">{translate key="common.plugins"}</a></li>
	</ul>
</div>

{include file="common/footer.tpl"}
