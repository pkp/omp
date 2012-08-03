{**
 * templates/reviewer/review/reviewStepHeader.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for the submission review pages.
 *}
{strip}
{translate|assign:"review" key='submission.review'}
{assign var="submissionTitle" value=$submission->getLocalizedTitle()}
{assign var="pageTitleTranslated" value="$review: $submissionTitle"}
{include file="common/header.tpl"}
{/strip}

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li{if $step == 1} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="submission" path=$submission->getId() step=1}">{translate key="reviewer.reviewSteps.request"}</a>
		</li>
		<li{if $step == 2} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="submission" path=$submission->getId() step=2}">{translate key="reviewer.reviewSteps.guidelines"}</a>
		</li>
		<li{if $step == 3} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="submission" path=$submission->getId() step=3}">{translate key="reviewer.reviewSteps.download"}</a>
		</li>
		<li{if $step == 4} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
			<a href="{url op="submission" path=$submission->getId() step=4}">{translate key="reviewer.reviewSteps.completion"}</a>
		</li>
	</ul>
