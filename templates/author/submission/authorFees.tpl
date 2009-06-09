{**
 * authorFees.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display of author fees and payment information
 *
 * $Id$
 *}

<h3>{translate key="manager.payment.authorFees"}</h3>
<table width="100%" class="data">
{if $currentPress->getSetting('submissionFeeEnabled')}
	<tr>
		<td width="20%">{$currentPress->getLocalizedSetting('submissionFeeName')|escape}</td>
	{if $submissionPayment}
		<td width="80%" colspan="2">{translate key="payment.paid"} {$submissionPayment->getTimestamp()|date_format:$datetimeFormatLong}</td>
	{else}
		<td width="30%">{$currentPress->getSetting('submissionFee')|string_format:"%.2f"} {$currentPress->getSetting('currency')}</td> 
		<td width="50%"><a class="action" href="{url op="paySubmissionFee" path=$submission->getMonographId()}">{translate key="payment.payNow"}</a></td>
	{/if}
 	</tr>
{/if}
{if $currentPress->getSetting('fastTrackFeeEnabled')}
	<tr>
		<td width="20%">{$currentPress->getLocalizedSetting('fastTrackFeeName')|escape}: 
	{if $fastTrackPayment}
		<td width="80%" colspan="2">{translate key="payment.paid"} {$fastTrackPayment->getTimestamp()|date_format:$datetimeFormatLong}</td>
	{else}
		<td width="30%">{$currentPress->getSetting('fastTrackFee')|string_format:"%.2f"} {$currentPress->getSetting('currency')}</td>
		<td width="50%"><a class="action" href="{url op="payFastTrackFee" path=$submission->getMonographId()}">{translate key="payment.payNow"}</a></td>
	{/if}
 	</tr>	
{/if}
{if $currentPress->getSetting('publicationFeeEnabled')}
	<tr>
		<td width="20%">{$currentPress->getLocalizedSetting('publicationFeeName')|escape}</td>
	{if $publicationPayment}
		<td width="80%" colspan="2">{translate key="payment.paid"} {$publicationPayment->getTimestamp()|date_format:$datetimeFormatLong}</td>
	{else}
		<td width="30%">{$currentPress->getSetting('publicationFee')|string_format:"%.2f"} {$currentPress->getSetting('currency')}</td>
		<td width="50%"><a class="action" href="{url op="payPublicationFee" path=$submission->getMonographId()}">{translate key="payment.payNow"}</a></td>
	{/if}
	</tr>	
{/if}
</table>