{**
 * submitHeader.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for the manuscript submission pages.
 *
 * $Id$
 *}
{strip}
{assign var="pageCrumbTitle" value="author.submit"}
{translate|assign:"stepx" key="submission.stepX" step=$submitStep}
{translate|assign:"stepy" key=$stepTitle}
{assign var="pageTitleTranslated" value="$stepx $stepy"}
{include file="common/header.tpl"}
{/strip}


{strip}
{assign var="pageCrumbTitle" value="author.submit"}
{url|assign:"currentUrl" op="submit"}
{/strip}

<!--
	This is a representation of HTML generated via the jQueryUI framework for tabs.
	Ideally, this process should use AJAX and jQueryUI to create this dynamically.
	See: http://jqueryui.com/demos/tabs/#ajax
-->

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		{if $monographId}
			<li{if $submitStep == 1} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
				<a href="{url op="submit" path="1" mongraphId=$monographId}">1. {translate key="author.submit.prepare"}</a>
			</li>
			<li{if $submitStep == 2} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
				<a href="{url op="submit" path="2" mongraphId=$monographId}">2. {translate key="author.submit.upload"}</a>
			</li>
			<li{if $submitStep == 3} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
				<a href="{url op="submit" path="3" mongraphId=$monographId}">3. {translate key="author.submit.catalogue"}</a>
			</li>
			<li{if $submitStep == 4} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
				<a href="{url op="submit" path="4" mongraphId=$monographId}">4. {translate key="author.submit.nextSteps"}</a>
			</li>
		{else}
			<li{if $submitStep == 1} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
				<a href="{url op="submit" path="1"}">1. {translate key="author.submit.prepare"}</a>
			</li>
			<li{if $submitStep == 2} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
				<a href="{url op="submit" path="2"}">2. {translate key="author.submit.upload"}</a>
			</li>
			<li{if $submitStep == 3} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
				<a href="{url op="submit" path="3"}">3. {translate key="author.submit.catalogue"}</a>
			</li>
			<li{if $submitStep == 4} class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"{else} class="ui-state-default ui-corner-top"{/if}>
				<a href="{url op="submit" path="4"}">4. {translate key="author.submit.nextSteps"}</a>
			</li>
		{/if}
	</ul>