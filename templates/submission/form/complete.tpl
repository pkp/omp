{**
 * complete.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The submission process has been completed; notify the author.
 *}
{strip}
{assign var="pageTitle" value="submission.submit.nextSteps"}
{include file="submission/form/submitStepHeader.tpl"}
{/strip}

<h2>{translate key="submission.submit.submissionComplete"}</h2>
<h4>{translate key="submission.submit.submissionCompleteThanks" pressName=$press->getLocalizedName()}</h4>
<br />
<div class="separator"></div>

<h3>{translate key="submission.submit.whatNext"}</h3>
<p>{translate key="submission.submit.whatNext.description"}</p>
<p>{translate key="submission.submit.whatNext.forNow"}</p>

<ul class="plain">
<li>&#187; <a href={$reviewSubmissionUrl}>{translate key="submission.submit.whatNext.review"}</a></li>
<li>&#187; <a href={url page="submission" op="wizard"}>{translate key="submission.submit.whatNext.create"}</a></li>
<li>&#187; <a href={url page="dashboard"}>{translate key="submission.submit.whatNext.return"}</a></li>
</ul>

</div>
{include file="common/footer.tpl"}

