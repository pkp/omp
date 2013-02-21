{**
 * templates/authorDashboard/monographEmail.tpl
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Render a single monograph email.
 *}
<blockquote>
	<div id="email-{$monographEmail->getId()}">
		<table width="100%">
			<tr valign="top">
				<td>
					{translate key="email.subject}: {$monographEmail->getSubject()|escape}<br />
					<span class="pkp_controllers_informationCenter_itemLastEvent">{$monographEmail->getDateSent()|date_format:$datetimeFormatShort}</span>
				</td>
			</tr>
			<tr valign="top">
				{assign var="contents" value=$monographEmail->getBody()}
				<td><br />
					{$monographEmail->getBody()|nl2br|strip_unsafe_html}
				</td>
			</tr>
		</table>
	</div>
</blockquote>