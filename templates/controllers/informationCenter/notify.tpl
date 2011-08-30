{**
 * templates/controllers/informationCenter/notify.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a form to notify other users about this file.
 *}
<script type="text/javascript">
	// Attach the file upload form handler.
	$(function() {ldelim}
		$('#notifyForm').pkpHandler(
			'$.pkp.controllers.form.AjaxFormHandler'
		);
	{rdelim});
</script>
<div id="informationCenterNotifyTab">
	<form class="pkp_form" id="notifyForm" action="{url op="sendNotification" params=$linkParams}" method="post">
		{include file="controllers/notification/inPlaceNotification.tpl" notificationId="notifyFormNotification"}
		{fbvFormArea id="notifyFormArea"}
			{fbvFormSection title="email.to" for="notifyUsersContainer" required="true"}
				{url|assign:notifyUsersUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.NotifyUsersListbuilderHandler" op="fetch" params=$linkParams escape=false}
				{load_url_in_div id="notifyUsersContainer" url=$notifyUsersUrl}
			{/fbvFormSection}

			{fbvFormSection title="informationCenter.notify.message" for="supportPhone" required="true"}
				{fbvElement type="textarea" id="message"}
			{/fbvFormSection}
			{fbvFormButtons id="notifyButton" hideCancel=true submitText="common.notify"}
		{/fbvFormArea}
	</form>
</div>
