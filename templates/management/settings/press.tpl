{**
 * templates/management/settings/press.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The press settings page.
 *}

{strip}
{assign var="pageTitle" value="manager.settings.pressSettings"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#accessTabs').pkpHandler(
				'$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id=accessTabs>
	<ul>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="masthead"}">{translate key="manager.setup.masthead"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="contact"}">{translate key="about.contact"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="policies"}">{translate key="about.policies"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="guidelines"}">{translate key="reviewer.reviewSteps.guidelines"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="affiliationAndSupport"}">{translate key="manager.affiliationAndSupport"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="divisionsAndSeries"}">{translate key="manager.setup.divisionsAndSeries"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="showTab" tab="pressIdentification"}">{translate key="manager.press.identification"}</a></li>
	</ul>
</div>

{include file="common/footer.tpl"}
