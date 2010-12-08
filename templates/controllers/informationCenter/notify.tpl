{**
 * notify.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a form to notify other users about this file.
 *}
<script type="text/javascript">
	<!--
	{literal}
	$(function() {
		$('.button').button();
		$('#notifyForm').last().ajaxForm({
			dataType: 'json',
	        success: function(returnString) {
	    		if (returnString.status == true) {
		    		// Notify that email was sent and clear form fields
		    		$("#notifyForm").find(':input').each(function() {
						$(this).val('');
			    	});
		    		$("#notifyWarning").remove();
		    		// FIXME: Display system notification that the message was sent
	    		} else {
	    			$("#message").last().after("<p id='notifyWarning'>"+returnString.content+"</p>");
	    		}
	        }
	    });

	});
	{/literal}
	// -->
</script>
<div id="informationCenterNotifyTab">
	<form name="notifyForm" id="notifyForm" action="{url op="sendNotification" monographId=$monographId itemId=$itemId}" method="post">
		{fbvFormArea id="notifyFormArea"}
			{fbvFormSection title="email.to" for="notifyUsersContainer" required="true"}
				{url|assign:notifyUsersUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.NotifyUsersListbuilderHandler" op="fetch" monographId=$monographId}
				{load_url_in_div id="notifyUsersContainer" url=$notifyUsersUrl}
			{/fbvFormSection}

			{fbvFormSection title="informationCenter.notify.message" for="supportPhone" required="true"}
				{fbvElement type="textarea" id="message" size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.1OF1}
			{/fbvFormSection}
			{fbvButton type="submit" id="notifyButton" label="common.notify" align=$fbvStyles.align.RIGHT}
		{/fbvFormArea}
	</form>
</div>
