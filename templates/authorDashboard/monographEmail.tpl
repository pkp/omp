{**
 * templates/authorDashboard/monographEmail.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display monograph emails to authors.
 *}

{if $monographEmails && $monographEmails->getCount()}
<form class="pkp_form">
	{fbvFormSection label="editor.review.personalMessageFromEditor"}
	{iterate from=monographEmails item=monographEmail}
		<p>
			<strong>{translate key="common.date"}:</strong>
			<span class="pkp_authorDashboard_email_date">{$monographEmail->getDateSent()|date_format:$dateFormatShort}</span><br/>
			<strong>{translate key="email.subject}:</strong>
			<span class="pkp_authorDashboard_email_subject">{$monographEmail->getSubject()|escape}</span>
		</p>
		{fbvElement type="textarea" id=$textAreaIdPrefix|concat:"-":$monographEmail->getId() value=$monographEmail->getBody() height=$fbvStyles.height.TALL disabled=true}
		<br />
	{/iterate}
	{/fbvFormSection}
</form>
{/if}
