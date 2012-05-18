{**
 * templates/management/navigation/navigation.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Navigation management page.
 *
 *}
{strip}
{assign var="pageTitle" value="common.navigation"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#navigationTabs').pkpHandler(
				'$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="navigationTabs">
	<ul>
		{if $announcementsEnabled}
			<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.content.ContentTabHandler" op="showTab" tab="announcements"}">{translate key="manager.announcements"}</a></li>
			<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.content.ContentTabHandler" op="showTab" tab="announcementTypes"}">{translate key="manager.announcementTypes"}</a></li>
		{/if}
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.content.ContentTabHandler" op="showTab" tab="navigation"}">{translate key="navigation.linksAndMedia"}</a></li>
	</ul>
</div>

{include file="common/footer.tpl"}