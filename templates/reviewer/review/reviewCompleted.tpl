{**
 * reviewCompleted.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the review completed page.
 *
 *
 * $Id$
 *}

{strip}
{assign var="pageCrumbTitle" value="submission.review"}
{include file="reviewer/review/reviewStepHeader.tpl"}
{/strip}

<h2>{translate key="reviewer.complete"}</h2>
<br />
<div class="separator"></div>

<p>{translate key="reviewer.complete.whatNext"}</p>
<br />

</form>
</div>
{include file="common/footer.tpl"}

