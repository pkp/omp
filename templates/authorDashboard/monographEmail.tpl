{**
 * templates/authorDashboard/monographEmail.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Render a single monograph email.
 *}
<div id="email-{$monographEmail->getId()}">
	<table width="100%">
		<tr valign="top">
			<td colspan="2">
				{translate key="email.subject}: {$monographEmail->getSubject()|escape}<br />
				<span class="pkp_controllers_informationCenter_itemLastEvent">{$monographEmail->getDateSent()|date_format:$datetimeFormatShort}</span>
			</td>
		</tr>
		<tr valign="top">
			{assign var="contents" value=$monographEmail->getBody()}
			<td colspan="3"><br />
				{$monographEmail->getBody()|nl2br|strip_unsafe_html}
			</td>
		</tr>
	</table>
</div>