{**
 * index.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays the notification settings page and unchecks
 *
 *}
{strip}
{assign var="pageTitle" value="notification.mailList"}
{include file="frontend/components/header.tpl"}
{/strip}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#notificationMailListForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<p><span class="instruct">{translate key="notification.mailListDescription"}</span></p>

<form class="pkp_form" id="notificationMailListForm" method="post" action="{url op="saveSubscribeMailList"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="notificationMailListFormNotification"}

	{fbvFormArea id="notificationMailList"}
		{fbvFormSection}
			{fbvElement type="text" label="user.email" id="email" value=$email required=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}

	{url|assign:cancelUrl page="notification"}
	{fbvFormButtons submitText="form.submit" cancelUrl=$cancelUrl}
	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>

{include file="frontend/components/footer.tpl"}
