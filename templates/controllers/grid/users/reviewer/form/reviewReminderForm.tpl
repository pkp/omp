{**
 * reviewReminderForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the form to send a review reminder--Contains a user-editable message field (all other fields are static)
 *
 *}

<form name="sendReminderForm" id="sendReminder" method="post" action="{url op="sendReminder"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="reviewAssignmentId" value="{$reviewAssignmentId}" />

	{fbvFormSection title="user.role.reviewer"}
		{fbvElement type="text" id="reviewerName" value=$reviewerName disabled="true"}
	{/fbvFormSection}
	
	{fbvFormSection title="editor.review.personalMessageToReviewer" for="message"}
		{fbvElement type="textarea" id="message" value=$message size=$fbvStyles.size.LARGE measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}
			
	<table width="100%" style="margin-left: 12px;">
		<tr>
			<td><strong>{translate key="editor.review.responseDueDate"}</strong></td>
			<td><strong>{translate key="editor.review.dateAccepted"}</strong></td>
			<td><strong>{translate key="reviewer.monograph.reviewDueDate"}</strong></td>
		</tr>
		<tr>
			<td>{$reviewAssignment->getDateResponseDue()|date_format:$dateFormatShort}</td>
			<td>{$reviewAssignment->getDateAcknowledged()|date_format:$dateFormatShort}</td>
			<td>{$reviewAssignment->getDateDue()|date_format:$dateFormatShort}</td>
		</tr>	
	</table> 

</form>