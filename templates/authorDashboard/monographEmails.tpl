{**
 * templates/authorDashboard/monographEmails.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display monograph emails to authors.
 *}

{if $monographEmails && $monographEmails->getCount()}
	<div class="pkp_monographEmails">
		<h3>{translate key="editor.review.personalMessageFromEditor"}</h3>
		{assign var="monographEmail" value=$monographEmails->next()}
		{include file="authorDashboard/monographEmail.tpl" monographEmail=$monographEmail}
		{if $monographEmails->getCount() > 1} {* more than one, display the rest as a list *}
			<table width="100%" class="pkp_listing">
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr><td colspan="3"><h3>{translate key="submission.previousAuthorEmail"}</h3></td></tr>
				<tr valign="top" class="heading">
					<td>{translate key="common.date"}</td>
					<td>{translate key="common.subject"}</td>
				</tr>
				{iterate from=monographEmails item=monographEmail}
				{* Generate a unique ID for this monograph email *}
				{capture assign=monographEmailLinkId}monographEmail-{$monographEmail->getId()}{/capture}
					<script type="text/javascript">
						// Initialize JS handler.
						$(function() {ldelim}
							$('#{$monographEmailLinkId|escape:"javascript"}').pkpHandler(
								'$.pkp.pages.authorDashboard.MonographEmailHandler',
								{ldelim}
									{* Parameters for parent LinkActionHandler *}
									actionRequest: '$.pkp.classes.linkAction.ModalRequest',
									actionRequestOptions: {ldelim}
										titleIcon: 'modal_information',
										title: '{$monographEmail->getSubject()|escape:"javascript"}',
										modalHandler: '$.pkp.controllers.modal.AjaxModalHandler',
										url: '{url|escape:"javascript" router=$smarty.const.ROUTE_PAGE page="authorDashboard" op="readMonographEmail" monographId=$monograph->getId() stageId=$stageId reviewRoundId=$reviewRoundId monographEmailId=$monographEmail->getId() escape=false}'
									{rdelim}
								{rdelim}
							);
						{rdelim});
					</script>
					<tr><td>{$monographEmail->getDateSent()|date_format:$datetimeFormatShort}</td><td><div id="{$monographEmailLinkId}">{null_link_action key=$monographEmail->getSubject()|escape id="monographEmail-"|concat:$monographEmail->getId() translate=false}</div></td></tr>
				{/iterate}
			</table>
		{/if}
	</div>
{/if}
