{**
 * templates/management/settings/workflow.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The publication process settings page.
 *}
{strip}
{assign var="pageTitle" value="manager.workflow.title"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#publicationTabs').pkpHandler(
				'$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="publicationTabs" class="pkp_controllers_tab">
	<ul>
		<li><a name="genres" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="showTab" tab="genres"}">{translate key="grid.genres.title.short"}</a></li>
		<li><a name="submissionStage" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="showTab" tab="submissionStage"}">{translate key="manager.publication.submissionStage"}</a></li>
		<li><a name="reviewStage" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="showTab" tab="reviewStage"}">{translate key="manager.publication.reviewStage"}</a></li>
		<li><a name="library" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="showTab" tab="library"}">{translate key="manager.publication.library"}</a></li>
		<li><a name="productionStage" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="showTab" tab="productionStage"}">{translate key="manager.publication.productionStage"}</a></li>
		<li><a name="emailTemplates" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="showTab" tab="emailTemplates"}">{translate key="manager.publication.emails"}</a></li>
	</ul>
</div>

{include file="common/footer.tpl"}
