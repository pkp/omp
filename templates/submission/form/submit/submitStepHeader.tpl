{**
 * submitHeader.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for the manuscript submission pages.
 *}
{strip}
{assign var="pageCrumbTitle" value="submission.submit"}
{translate|assign:"stepx" key="submission.stepX" step=$submitStep}
{translate|assign:"stepy" key=$stepTitle}
{assign var="pageTitleTranslated" value="$stepx $stepy"}
{include file="common/header.tpl"}
{/strip}


{strip}
{assign var="pageCrumbTitle" value="submission.submit"}
{url|assign:"currentUrl" op="wizard"}
{/strip}

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		{if $monographId}
			<li{if $submitStep == 1} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active" {elseif $submissionProgress != 0} class="ui-state-default ui-corner-top ui-state-active"{else} class="ui-state-default ui-corner-top ui-state-disabled"{/if}>
				<a href="{url op="wizard" path="1" monographId=$monographId}">1. {translate key="submission.submit.prepare"}</a>
			</li>
			<li{if $submitStep == 2} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active" {elseif $submissionProgress > 1 && $submissionProgress != 0} class="ui-state-default ui-corner-top ui-state-active"{else} class="ui-state-default ui-corner-top ui-state-disabled"{/if}>
				<a href="{url op="wizard" path="2" monographId=$monographId}">2. {translate key="submission.submit.upload"}</a>
			</li>
			<li{if $submitStep == 3} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active" {elseif $submissionProgress > 2 && $submissionProgress != 0} class="ui-state-default ui-corner-top ui-state-active"{else} class="ui-state-default ui-corner-top ui-state-disabled"{/if}>
				<a href="{url op="wizard" path="3" monographId=$monographId}">3. {translate key="submission.submit.catalogue"}</a>
			</li>
			<li{if $submitStep == 4} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active" {elseif $submissionProgress > 3 && $submissionProgress != 0} class="ui-state-default ui-corner-top ui-state-active"{else} class="ui-state-default ui-corner-top ui-state-disabled"{/if}>
				<a href="{url op="wizard" path="4" monographId=$monographId}">4. {translate key="submission.submit.nextSteps"}</a>
			</li>
		{else}
			{** The submission is just starting -- disable all but the first tab **}
			<li class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active">
				<a href="{url op="wizard" path="1"}">1. {translate key="submission.submit.prepare"}</a>
			</li>
			<li class="ui-state-default ui-corner-top ui-state-disabled">
				<a href="{url op="wizard" path="1"}">2. {translate key="submission.submit.upload"}</a>
			</li>
			<li class="ui-state-default ui-corner-top ui-state-disabled">
				<a href="{url op="wizard" path="1"}">3. {translate key="submission.submit.catalogue"}</a>
			</li>
			<li class="ui-state-default ui-corner-top ui-state-disabled">
				<a href="{url op="wizard" path="1"}">4. {translate key="submission.submit.nextSteps"}</a>
			</li>
		{/if}
	</ul>

	<script type="text/javascript">
		$(function() {ldelim}
			// Attach the form handler.
			$('#submitStepForm').pkpHandler('$.pkp.controllers.FormHandler');
		{rdelim});
	</script>
