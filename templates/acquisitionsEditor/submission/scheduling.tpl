{**
 * scheduling.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the scheduling table.
 *
 * $Id$
 *}
<div id="scheduling">
<h3>{translate key="submission.scheduling"}</h3>

{if !$publicatonFeeEnabled || $publicationPayment}
	<form action="{url op="scheduleForPublication" path=$submission->getMonographId()}" method="post">
	<p>
		<label for="issueId">{translate key="editor.monograph.scheduleForPublication"}</label>
		{if $publishedMonograph}
			{assign var=issueId value=$publishedMonograph->getIssueId()}
		{else}
			{assign var=issueId value=0}
		{/if}
		<select name="issueId" id="issueId" class="selectMenu">
			<option value="">{translate key="editor.monograph.scheduleForPublication.toBeAssigned"}</option>
			{html_options options=$issueOptions|truncate:40:"..." selected=$issueId}
		</select>&nbsp;
		<input type="submit" value="{translate key="common.record"}" class="button defaultButton" />&nbsp;
		{if $issueId}
			{if $isEditor}
				<a href="{url op="issueToc" path=$issueId}" class="action">{translate key="issue.toc"}</a>
			{else}
				<a href="{url page="issue" op="view" path=$issueId}" class="action">{translate key="issue.toc"}</a>
			{/if}
		{/if}
	</p>
	</form>
{else}
	<table class="data">
    <tr>
    	<td width="50%">
    		{translate key="editor.monograph.payment.publicationFeeNotPaid"}
    	</td>
		<td align="right">
			<form action="{url op="waivePublicationFee" path=$submission->getMonographId()}" method="post">
			<input type="hidden" name="markAsPaid" value=1 />
			<input type="hidden" name="sendToScheduling" value=1 />
			<input type="submit" value="{translate key="payment.paymentReceived"}" class="button defaultButton" />&nbsp;
			</form>
		</td>
		{if $isEditor}
		<td align="left">
			<form action="{url op="waivePublicationFee" path=$submission->getMonographId()}" method="post">
			<input type="hidden" name="sendToScheduling" value=1 />
			<input type="submit" value="{translate key="payment.waive"}" class="button defaultButton" />&nbsp;
			</form>
		</td>	
		{/if}
	</tr>
	</table>
{/if}
</div>