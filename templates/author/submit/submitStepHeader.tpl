{**
 * submitHeader.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for the manuscript submission pages.
 *
 * $Id$
 *}
{strip}
{assign var="pageCrumbTitle" value="author.submit"}
{translate|assign:"pageTitleTranslated" key=$stepTitle}
{include file="common/header.tpl"}
{/strip}

<ul class="steplist">
{foreach from=$steplist key=stepIndex item=step}
{assign var="id" value=$step.identity}
{if !$step.context}
<li{if $submitStep == $stepIndex} class="current"{/if}>
	{if $submissionProgress >= $stepIndex and $submitStep!=$stepIndex}
		<a href="{url op="submit" path=$step.alias monographId=$monographId}">
	{/if}
	{$stepIndex}. {translate key=$step.tag}{$id}
	{if $submissionProgress >= $stepIndex and $submitStep!=$stepIndex}
		</a>
	{/if}
</li>
{/if}
{/foreach}
</ul>
{if isset($contextSteps)}
{foreach from=$contextSteps item=contextStep}
	<a href="{url op="submit" path=$steplist[$contextStep.step].alias monographId=$monographId}">
		{translate key=$steplist[$contextStep.step].tag}
	</a>
{/foreach}
{/if}
