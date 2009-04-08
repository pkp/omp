{**
 * submissions.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
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

{if $currentPress->getSetting('pressPaymentsEnabled') && 
		($currentPress->getSetting('submissionFeeEnabled') || $currentPress->getSetting('fastTrackFeeEnabled') || $currentPress->getSetting('publicationFeeEnabled')) }
	{assign var="authorFees" value=1}
{/if}

<ul class="plain">
	<li>&#187; <a href="{url page="about" op="submissions" anchor="onlineSubmissions"}">{translate key="about.onlineSubmissions"}</a></li>
	{if $currentPress->getLocalizedSetting('authorGuidelines') != ''}<li>&#187; <a href="{url page="about" op="submissions" anchor="authorGuidelines"}">{translate key="about.authorGuidelines"}</a></li>{/if}
	{if $currentPress->getLocalizedSetting('copyrightNotice') != ''}<li>&#187; <a href="{url page="about" op="submissions" anchor="copyrightNotice"}">{translate key="about.copyrightNotice"}</a></li>{/if}
	{if $currentPress->getLocalizedSetting('privacyStatement') != ''}<li>&#187; <a href="{url page="about" op="submissions" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></li>{/if}
	{if $authorFees}<li>&#187; <a href="{url page="about" op="submissions" anchor="authorFees"}">{translate key="about.authorFees"}</a></li>{/if}	
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

<div id="submissionPreparationChecklist"><h3>{translate key="about.submissionPreparationChecklist"}</h3>
<p>{translate key="about.submissionPreparationChecklist.description"}</p>
<ol>
	{foreach from=$submissionChecklist item=checklistItem}
		<li>{$checklistItem.content|nl2br}</li>	
	{/foreach}
</ol>

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

{if $authorFees}

<div id="authorFees"><h3>{translate key="manager.payment.authorFees"}</h3>
	<p>{translate key="about.authorFeesMessage"}</p>
	{if $currentPress->getSetting('submissionFeeEnabled')}
		<p>{$currentPress->getLocalizedSetting('submissionFeeName')|escape}: {$currentPress->getSetting('submissionFee')|string_format:"%.2f"} ({$currentPress->getSetting('currency')})<br />
		{$currentPress->getLocalizedSetting('submissionFeeDescription')|nl2br}<p>
	{/if}
	{if $currentPress->getSetting('fastTrackFeeEnabled')}
		<p>{$currentPress->getLocalizedSetting('fastTrackFeeName')|escape}: {$currentPress->getSetting('fastTrackFee')|string_format:"%.2f"} ({$currentPress->getSetting('currency')})<br />
		{$currentPress->getLocalizedSetting('fastTrackFeeDescription')|nl2br}<p>	
	{/if}
	{if $currentPress->getSetting('publicationFeeEnabled')}
		<p>{$currentPress->getLocalizedSetting('publicationFeeName')|escape}: {$currentPress->getSetting('publicationFee')|string_format:"%.2f"} ({$currentPress->getSetting('currency')})<br />
		{$currentPress->getLocalizedSetting('publicationFeeDescription')|nl2br}<p>	
	{/if}
	{if $currentPress->getLocalizedSetting('waiverPolicy') != ''}
		<p>{$currentPress->getLocalizedSetting('waiverPolicy')|escape}</p>
	{/if}
</div>
{/if}
{include file="common/footer.tpl"}
