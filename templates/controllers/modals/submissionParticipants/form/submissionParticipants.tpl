{**
 * submissionParticipants.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to show all participants in a submission.
 * FIXME #5876 -- Enhance when we have a spec for this
 *
 *}
{modal_title id="#submissionParticipants" key='submission.submit.allParticipants' iconClass="fileManagement" canClose=1}
<div id="submissionParticipants">
	<!-- Available submission files -->
	{url|assign:submissionParticipantsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.submissionParticipant.SubmissionParticipantGridHandler" op="fetchGrid" monographId=$monographId stageId=$monograph->getStageId() escape=false}
	{load_url_in_div id="submissionParticipantsGrid" url=$submissionParticipantsGridUrl}
</div>

{fbvFormButtons}

