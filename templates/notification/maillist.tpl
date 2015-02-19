{**
 * index.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays the notification settings page and unchecks
 *
 *}
{strip}
{assign var="pageTitle" value="notification.mailList"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#notificationMailListForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<p><span class="instruct">{translate key="notification.mailListDescription"}</span></p>

<form class="pkp_form" id="notificationMailListForm" method="post" action="{url op="saveSubscribeMailList"}">
	{include file="common/formErrors.tpl"}

	{fbvFormArea id="notificationMailList"}
		{fbvFormSection title="user.email" for="email" required="true"}
			{fbvElement type="text" id="email" value=$email size=$fbvStyles.size.MEDIUM} <br />
			{fbvElement type="text" label="user.confirmEmail" id="confirmEmail" value=$confirmEmail size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}

	{url|assign:cancelUrl page="notification"}
	{fbvFormButtons submitText="form.submit" cancelUrl=$cancelUrl}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
