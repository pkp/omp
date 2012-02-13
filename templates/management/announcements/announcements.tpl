{**
 * templates/management/announcements/announcements.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Announcements management page.
 *
 *}

{strip}
{assign var="pageTitle" value="manager.announcements"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#announcementsTabs').pkpHandler(
				'$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id=announcementsTabs>
	<ul>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.announcements.AnnouncementTabHandler" op="showTab" tab="announcements"}">{translate key="manager.announcements"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.announcements.AnnouncementTabHandler" op="showTab" tab="announcementTypes"}">{translate key="manager.announcementTypes"}</a></li>
	</ul>
</div>

{include file="common/footer.tpl"}