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
		<span class="pkp_authorDashboard_email_date">{$monographEmail->getDateSent()|date_format:$dateFormatShort}</span>
		<span class="pkp_authorDashboard_email_subjectLabel">{translate key="email.subject}:</span>
		<span class="pkp_authorDashboard_email_subject">{$monographEmail->getSubject()|escape}</span>
		{fbvElement type="textarea" id=$textAreaIdPrefix|concat:"-":$monographEmail->getId() value=$monographEmail->getBody() height=$fbvStyles.height.TALL disabled=true}
		<br/>
	{/iterate}
	{/fbvFormSection}
</form>
{/if}
