<!-- templates/about/submissions.tpl -->

{**
 * submissions.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press / Submissions.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="about.submissions"}
{include file="common/header.tpl"}
{/strip}

<ul class="plain">
	<li>&#187; <a href="{url page="about" op="submissions" anchor="onlineSubmissions"}">{translate key="about.onlineSubmissions"}</a></li>
	{if $currentPress->getLocalizedSetting('authorGuidelines') != ''}<li>&#187; <a href="{url page="about" op="submissions" anchor="authorGuidelines"}">{translate key="about.authorGuidelines"}</a></li>{/if}
	{if $currentPress->getLocalizedSetting('copyrightNotice') != ''}<li>&#187; <a href="{url page="about" op="submissions" anchor="copyrightNotice"}">{translate key="about.copyrightNotice"}</a></li>{/if}
	{if $currentPress->getLocalizedSetting('privacyStatement') != ''}<li>&#187; <a href="{url page="about" op="submissions" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></li>{/if}
</ul>

<div id="onlineSubmissions"><h3>{translate key="about.onlineSubmissions"}</h3>
<p>
	{translate key="about.onlineSubmissions.haveAccount" pressTitle=$siteTitle|escape}<br />
	<a href="{url page="login"}" class="action">{translate key="about.onlineSubmissions.login"}</a>
</p>
<p>
	{translate key="about.onlineSubmissions.needAccount"}<br />
	<a href="{url page="user" op="register"}" class="action">{translate key="about.onlineSubmissions.registration"}</a>
</p>
<p>{translate key="about.onlineSubmissions.registrationRequired"}</p>

<div class="separator">&nbsp;</div>
</div>

{if $currentPress->getLocalizedSetting('authorGuidelines') != ''}
<div id="authorGuidelines"><h3>{translate key="about.authorGuidelines"}</h3>
<p>{$currentPress->getLocalizedSetting('authorGuidelines')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

{if $submissionChecklist}
	<div id="submissionPreparationChecklist"><h3>{translate key="about.submissionPreparationChecklist"}</h3>
	<p>{translate key="about.submissionPreparationChecklist.description"}</p>
	<ol>
		{foreach from=$submissionChecklist item=checklistItem}
			<li>{$checklistItem.content|nl2br}</li>	
		{/foreach}
	</ol>
{/if}{* $submissionChecklist *}

<div class="separator">&nbsp;</div>
</div>

{if $currentPress->getLocalizedSetting('copyrightNotice') != ''}
<div id="copyrightNotice"><h3>{translate key="about.copyrightNotice"}</h3>
<p>{$currentPress->getLocalizedSetting('copyrightNotice')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

{if $currentPress->getLocalizedSetting('privacyStatement') != ''}<div id="privacyStatement"><h3>{translate key="about.privacyStatement"}</h3>
<p>{$currentPress->getLocalizedSetting('privacyStatement')|nl2br}</p>

<div class="separator">&nbsp;</div>
</div>
{/if}

{include file="common/footer.tpl"}

<!-- / templates/about/submissions.tpl -->

