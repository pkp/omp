{**
 * notify.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a form to notify other users about this file.
 *
 * $Id$
 *}
<script type="text/javascript">
	{literal}
	$(function() {
		$('.button').button();
		$('#notifyForm').ajaxForm({
			dataType: 'json',
			data: // FIXME: Need to serialize the listbuilder's grid to get the userIds to send to
	        success: function(returnString) {
	    		if (returnString.status == true) {
		    		// Notify that email was sent and clear form fields
		    		$("#notifyForm").find(':input').each(function() {
						$(this).val('');
			    	});
		    		// FIXME: Display notification
	    		} else {
	    			var localizedButton = ['{/literal}{translate key="common.ok"}{literal}'];
	    			modalAlert(returnString.content, localizedButton);
	    		}
	        }
	    });
	});
	{/literal}
</script>
<div id="informationCenterNotifyTab">
	<form name="notifyForm" id="notifyForm" action="{url router=$smarty.const.ROUTE_PAGE page="informationCenter" op="sendNotification" fileId=$fileId}" method="post">
		{fbvFormArea id="notifyFormArea"}
			{fbvFormSection title="email.to" for="supportName" required="true"}
				{url|assign:notifyUsersUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.NotifyUsersListbuilderHandler" op="fetch" fileId=$fileId}
				{load_url_in_div id="notifyUsersContainer" url=$notifyUsersUrl}
			{/fbvFormSection}
			{fbvFormSection title="informationCenter.notify.template" for="supportName" required="true"}
				{fbvElement type="text" id="supportName" value=$supportName maxlength="60"}
			{/fbvFormSection}
			{fbvFormSection title="common.subject" for="supportEmail" required="true"}
				{fbvSelect id="template" from=$notifyTemplates translate=false}
			{/fbvFormSection}
			{fbvFormSection title="informationCenter.notify.message" for="supportPhone" required="true"}
				{fbvElement type="text" id="subject" maxlength="255"}
			{/fbvFormSection}
			{fbvFormSection title="informationCenter.notify.message" for="supportPhone" required="true"}
				{fbvElement type="textarea" id="message" size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}
			{/fbvFormSection}
			<div style="float:right;">{fbvButton type="submit" id="notifyButton" label="common.notify" float=$fbvStyles.float.RIGHT}</div>
		{/fbvFormArea}
	</form>
</div>