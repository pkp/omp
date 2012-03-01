{**
 * templates/controllers/tab/content/announcements/form/announcementTypeForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Announcement types management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#announcementTypeForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="announcementTypeForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.content.ContentTabHandler" op="saveFormData" tab="announcementTypes"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="seriesFormNotification"}

	{url|assign:announcementTypeListbuilderUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.content.announcements.AnnouncementTypeListbuilderHandler" op="fetch"}
	{load_url_in_div id="announcementTypeListbuilderContainer" url="$announcementTypeListbuilderUrl"}

	{fbvFormButtons submitText="common.save"}
</form>
