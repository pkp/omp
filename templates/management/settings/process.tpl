{**
 * templates/management/settings/website.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The website settings page.
 *}

{strip}
{assign var="pageTitle" value="manager.process.publicationProcess"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#websiteTabs').pkpHandler(
				'$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id=websiteTabs>
	<ul>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.ProcessSettingsTabHandler" op="showTab" tab="general"}">{translate key="manager.process.general"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.ProcessSettingsTabHandler" op="showTab" tab="submissionStage"}">{translate key="manager.process.submissionStage"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.ProcessSettingsTabHandler" op="showTab" tab="reviewStage"}">{translate key="manager.process.reviewStage"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.ProcessSettingsTabHandler" op="showTab" tab="editorialStage"}">{translate key="manager.process.editorialStage"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.ProcessSettingsTabHandler" op="showTab" tab="productionStage"}">{translate key="manager.process.productionStage"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.ProcessSettingsTabHandler" op="showTab" tab="emailTemplates"}">{translate key="manager.system.preparedEmails"}</a></li>
	</ul>
</div>

{include file="common/footer.tpl"}
