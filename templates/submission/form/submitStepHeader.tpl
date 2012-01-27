{**
 * submitHeader.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for the manuscript submission pages.
 *}
{strip}
{include file="common/header.tpl"}
{/strip}


{strip}
{assign var="pageCrumbTitle" value="submission.submit"}
{url|assign:"currentUrl" op="wizard"}
{/strip}

<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		{foreach from=$steps key=step item=stepLocaleKey}
			{assign var=stepUrl value="#"}
			{assign var=cssClass value=""}
			{if $step <= $submissionProgress} 
				{url|assign:stepUrl op="wizard" path=$step monographId=$monographId}					
			{/if}
			
			{if $step <= $submissionProgress && $submissionProgress != 0}
				{assign var=cssClass value="ui-state-default ui-corner-top ui-state-active"}
			{/if}
			
			{if $step > $submissionProgress}
				{assign var=cssClass value="ui-state-default ui-corner-top ui-state-disabled"}
			{/if}
			
			{if $step == $submitStep}
				{assign var=cssClass value="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"}
			{/if}			
			
			<li class="{$cssClass}">
				<a href="{$stepUrl}">{$step}. {translate key=$stepLocaleKey}</a>
			</li>
		{/foreach}
	</ul>
