{**
 * submissionStatus.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission status cell for submissionsList grid
 *
 *}
{assign var="status" value=$submission->getSubmissionStatus()}
			
{if $status==STATUS_ARCHIVED}{translate key="submissions.archived"}
{elseif $status==STATUS_QUEUED_UNASSIGNED}{translate key="submissions.queuedUnassigned"}
{elseif $status==STATUS_QUEUED_EDITING}<a href="{url op="submissionEditing" path=$monographId}" class="action">{translate key="submissions.queuedEditing"}</a>
{elseif $status==STATUS_QUEUED_REVIEW}<a href="{url op="submissionReview" path=$monographId}" class="action">{translate key="submissions.queuedReview"}</a>
{elseif $status==STATUS_PUBLISHED}{translate key="submissions.published"}
{elseif $status==STATUS_DECLINED}{translate key="submissions.declined"}
{/if}
