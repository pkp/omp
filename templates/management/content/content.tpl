{**
 * templates/management/content/content.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Content management page.
 *
 *}

{strip}
{assign var="pageTitle" value="common.content"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#contentTabs').pkpHandler(
				'$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="contentTabs">
	<ul>
		{if $announcementsEnabled}
			<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.content.ContentTabHandler" op="showTab" tab="announcements"}">{translate key="manager.announcements"}</a></li>
			<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.content.ContentTabHandler" op="showTab" tab="announcementTypes"}">{translate key="manager.announcementTypes"}</a></li>
		{/if}
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.content.ContentTabHandler" op="showTab" tab="spotlights"}">{translate key="spotlight.spotlights"}</a></li>
	</ul>
</div>

{include file="common/footer.tpl"}