{**
 * templates/management/settings/distribution.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The distribution process settings page.
 *}

{strip}
{assign var="pageTitle" value="manager.distribution.title"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#distributionTabs').pkpHandler('$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="distributionTabs" class="pkp_controllers_tab">
	<ul>
		<li><a name="indexing" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.DistributionSettingsTabHandler" op="showTab" tab="indexing"}">{translate key="manager.distribution.indexing"}</a></li>
		<li><a name="paymentMethod" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.DistributionSettingsTabHandler" op="showTab" tab="paymentMethod"}">{translate key="manager.paymentMethod"}</a></li>
		<li><a name="permissions" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.DistributionSettingsTabHandler" op="showTab" tab="permissions"}">{translate key="submission.permissions"}</a></li>
	</ul>
</div>

{include file="common/footer.tpl"}
