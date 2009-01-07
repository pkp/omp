{**
 * submitHeader.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for the article submission pages.
 *
 * $Id$
 *}
{strip}
{assign var="pageCrumbTitle" value="author.submit"}
{include file="common/header.tpl"}
{/strip}

<ul class="steplist">
{foreach from=$steplist key=stepIndex item=step}
<li{if $submitStep == $stepIndex} class="current"{/if}>
	{if $submissionProgress >= $stepIndex and $submitStep!=$stepIndex}
		<a href="{url op="submit" path=$step.alias monographId=$monographId}">
	{/if}
	{translate key=$step.tag}
	{if $submissionProgress >= $stepIndex and $submitStep!=$stepIndex}
		</a>
	{/if}
</li>
{/foreach}
</ul>
