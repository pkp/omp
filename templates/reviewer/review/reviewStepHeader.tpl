{**
 * reviewStepHeader.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for the submission review pages.
 *}
{strip}
{translate|assign:"review" key='submission.review'}
{assign var="submissionTitle" value=$submission->getLocalizedTitle()}
{assign var="pageTitleTranslated" value="$review: $submissionTitle"}

{assign var="dateNotified" value=$submission->getDateNotified()|date_format:$dateFormatShort}
{assign var="dateDue" value=$submission->getDateDue()|date_format:$dateFormatShort}
{translate|assign:"notifiedLabel" key="reviewer.monograph.reviewRequestDate"}
{translate|assign:"dueLabel" key="reviewer.monograph.reviewDueDate"}
{assign var="additionalHeading" value="
		<table class='data' style='margin-left: 10px;'>
			<tr><td style='padding-right: 20px;'>
				<strong>$notifiedLabel</strong><br />
				<p>$dateNotified</p>
			</td>
			<td>
				<strong>$dueLabel</strong><br />
				<p>$dateDue</p>
			</td></tr>
		</table>
"}
{include file="common/header.tpl"}
{/strip}


<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li{if $step == 1} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="submission" path=$submission->getReviewId() step=1}">1. {translate key="submission.request"}</a>
		</li>
		<li{if $step == 2} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="submission" path=$submission->getReviewId() step=2}">2. {translate key="reviewer.reviewSteps.guidelines"}</a>
		</li>
		<li{if $step == 3} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="submission" path=$submission->getReviewId() step=3}">3. {translate key="reviewer.reviewSteps.download"}</a>
		</li>
		<li{if $step == 4} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="submission" path=$submission->getReviewId() step=4}">4. {translate key="reviewer.reviewSteps.nextSteps"}</a>
		</li>
	</ul>
