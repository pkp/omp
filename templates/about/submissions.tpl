{**
 * templates/about/submissions.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press / Submissions.
 *}
{strip}
{assign var="pageTitle" value="about.submissions"}
{include file="common/header.tpl"}
{/strip}

<div id="onlineSubmissions">
	<h3>{translate key="about.onlineSubmissions"}</h3>
	<p>
		{translate key="about.onlineSubmissions.haveAccount" pressTitle=$siteTitle|escape}<br />
		<a href="{url page="login"}" class="action">{translate key="about.onlineSubmissions.login"}</a>
	</p>
	<p>
		{translate key="about.onlineSubmissions.needAccount"}<br />
		<a href="{url page="user" op="register"}" class="action">{translate key="about.onlineSubmissions.registration"}</a>
	</p>
	<p>{translate key="about.onlineSubmissions.registrationRequired"}</p>
</div>
<div class="separator"></div>

{if $submissionInfo.authorGuidelines}
	<div id="authorGuidelines">
		<h3>{translate key="about.authorGuidelines"}</h3>

		{url|assign:editUrl page="management" op="settings" path="press" anchor="guides"}
		{include file="common/linkToEditPage.tpl" editUrl=$editUrl}

		<p>{$submissionInfo.authorGuidelines|nl2br}</p>
	</div>
	<div class="separator"></div>
{/if}

{if $submissionInfo.checklist}
	<div id="submissionPreparationChecklist">
		<h3>{translate key="about.submissionPreparationChecklist"}</h3>

		{url|assign:editUrl page="management" op="settings" path="publication" anchor="submissionStage"}
		{include file="common/linkToEditPage.tpl" editUrl=$editUrl}

		<p>{translate key="about.submissionPreparationChecklist.description"}</p>
		<ul class="pkp_helpers_bulletlist">
			{foreach from=$submissionInfo.checklist item=checklistItem}
				<li>{$checklistItem.content|nl2br}</li>
			{/foreach}
		</ul>
	</div>
	<div class="separator"></div>
{/if}

{if $submissionInfo.copyrightNotice}
	<div id="copyrightNotice">
		<h3>{translate key="about.copyrightNotice"}</h3>

		{url|assign:editUrl page="management" op="settings" path="press" anchor="policies"}
		{include file="common/linkToEditPage.tpl" editUrl=$editUrl}

		<p>{$submissionInfo.copyrightNotice|nl2br}</p>
	</div>
	<div class="separator"></div>
{/if}

{if $submissionInfo.privacyStatement}
	<div id="privacyStatement">
		<h3>{translate key="about.privacyStatement"}</h3>

		{url|assign:editUrl page="management" op="settings" path="press" anchor="policies"}
		{include file="common/linkToEditPage.tpl" editUrl=$editUrl}

		<p>{$submissionInfo.privacyStatement|nl2br}</p>
	</div>
	<div class="separator"></div>
{/if}

{if $submissionInfo.reviewPolicy}
	<div id="reviewPolicy">
		<h3>{translate key="about.reviewPolicy"}</h3>

		{url|assign:editUrl page="management" op="settings" path="press" anchor="policies"}
		{include file="common/linkToEditPage.tpl" editUrl=$editUrl}

		<p>{$submissionInfo.reviewPolicy|nl2br}</p>
	</div>
	<div class="separator"></div>
{/if}

{include file="common/footer.tpl"}
