{**
 * controllers/tab/settings/paymentMethod/form/paymentMethodForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Payment method management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#paymentMethodForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="paymentMethodForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.DistributionSettingsTabHandler" op="saveFormData" tab="paymentMethod"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="indexingFormNotification"}
	{include file="controllers/tab/settings/wizardMode.tpl" wizardMode=$wizardMode}

	<h3>{translate key="manager.paymentMethod.title"}</h3>

	<p>{translate key="manager.paymentMethod.description"}</p>

	{fbvFormArea id="paymentMethod"}
		{fbvFormSection title="manager.paymentMethod.method"}
			{* FIXME: Include payment method configuration here *}
		{/fbvFormSection}
	{/fbvFormArea}

	<div class="separator"></div>

	{if !$wizardMode}
		{fbvFormButtons id="indexingFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>
