{**
 * plugins/paymethod/manual/templates/paymentForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Manual payment page
 *}
{include file="frontend/components/header.tpl" pageTitle="plugins.paymethod.manual"}


<div class="page page_payment">
	{include file="frontend/components/breadcrumbs.tpl" currentTitleKey="plugins.paymethod.manual"}
	<h1>{translate key="plugins.paymethod.manual"}</h1>

	<p>{$manualInstructions|nl2br}</p>

	<table class="data" width="100%">
		<tr>
			<td class="label" width="20%">{translate key="plugins.paymethod.manual.purchase.title"}</td>
			<td class="value" width="80%"><strong>{$itemName|escape}</strong></td>
		</tr>
		{if $itemAmount}
			<tr>
				<td class="label" width="20%">{translate key="plugins.paymethod.manual.purchase.fee"}</td>
				<td class="value" width="80%"><strong>{$itemAmount|string_format:"%.2f"}{if $itemCurrencyCode} ({$itemCurrencyCode|escape}){/if}</strong></td>
			</tr>
		{/if}
	</table>

	<p>
		<a href="{url page="payment" op="plugin" path="ManualPayment"|to_array:"notify":$queuedPaymentId}" class="action">{translate key="plugins.paymethod.manual.sendNotificationOfPayment"}</a>
	</p>

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
