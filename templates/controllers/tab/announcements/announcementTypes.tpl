{**
 * templates/controllers/tab/announcements/announcementTypes.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Announcement types management.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#announcementTypesForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="announcementTypesForm"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="seriesFormNotification"}

	{url|assign:announcementTypeListbuilderUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.announcements.AnnouncementTypeListbuilderHandler" op="fetch"}
	{load_url_in_div id="announcementTypeListbuilderContainer" url="$announcementTypeListbuilderUrl"}

	{fbvFormButtons submitText="common.save"}
</form>