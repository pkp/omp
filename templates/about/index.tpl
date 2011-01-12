{**
 * index.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press index.
 *}
{strip}
{assign var="pageTitle" value="about.aboutThePress"}
{include file="common/header.tpl"}
{/strip}

<h3>{translate key="about.people"}</h3>
<ul class="plain">
	{if not (empty($pressSettings.mailingAddress) && empty($pressSettings.contactName) && empty($pressSettings.contactAffiliation) && empty($pressSettings.contactMailingAddress) && empty($pressSettings.contactPhone) && empty($pressSettings.contactFax) && empty($pressSettings.contactEmail) && empty($pressSettings.supportName) && empty($pressSettings.supportPhone) && empty($pressSettings.supportEmail))}
		<li>&#187; <a href="{url op="contact"}">{translate key="about.contact"}</a></li>
	{/if}
	<li>&#187; <a href="{url op="editorialTeam"}">{translate key="about.editorialTeam"}</a></li>
	{call_hook name="Templates::About::Index::People"}
</ul>

<h3>{translate key="about.policies"}</h3>
<ul class="plain">
	{if $currentPress->getLocalizedSetting('focusScopeDesc') != ''}<li>&#187; <a href="{url op="editorialPolicies" anchor="focusAndScope"}">{translate key="about.focusAndScope"}</a></li>{/if}
	{if $seriesCount > 0}<li>&#187; <a href="{url op="editorialPolicies" anchor="seriesArrrangementPolicies"}">{translate key="about.seriesPolicies"}</a></li>{/if}
	{if $currentPress->getLocalizedSetting('reviewPolicy') != ''}<li>&#187; <a href="{url op="editorialPolicies" anchor="peerReviewProcess"}">{translate key="about.peerReviewProcess"}</a></li>{/if}
	{if $currentPress->getLocalizedSetting('pubFreqPolicy') != ''}<li>&#187; <a href="{url op="editorialPolicies" anchor="publicationFrequency"}">{translate key="about.publicationFrequency"}</a></li>{/if}
	{if $currentPress->getLocalizedSetting('openAccessPolicy') != ''}<li>&#187; <a href="{url op="editorialPolicies" anchor="openAccessPolicy"}">{translate key="about.openAccessPolicy"}</a></li>{/if}
	{foreach key=key from=$customAboutItems item=customAboutItem}
		{if $customAboutItem.title!=''}<li>&#187; <a href="{url op="editorialPolicies" anchor=custom-$key}">{$customAboutItem.title|escape}</a></li>{/if}
	{/foreach}
	{call_hook name="Templates::About::Index::Policies"}
</ul>

<h3>{translate key="about.submissions"}</h3>
<ul class="plain">
	<li>&#187; <a href="{url op="submissions" anchor="onlineSubmissions"}">{translate key="about.onlineSubmissions"}</a></li>
	{if $currentPress->getLocalizedSetting('authorGuidelines') != ''}<li>&#187; <a href="{url op="submissions" anchor="authorGuidelines"}">{translate key="about.authorGuidelines"}</a></li>{/if}
	{if $currentPress->getLocalizedSetting('copyrightNotice') != ''}<li>&#187; <a href="{url op="submissions" anchor="copyrightNotice"}">{translate key="about.copyrightNotice"}</a></li>{/if}
	{if $currentPress->getLocalizedSetting('privacyStatement') != ''}<li>&#187; <a href="{url op="submissions" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></li>{/if}
	{call_hook name="Templates::About::Index::Submissions"}
</ul>

<h3>{translate key="about.other"}</h3>
<ul class="plain">
	{if not ($currentPress->getLocalizedSetting('contributorNote') == '' && empty($pressSettings.contributors) && $currentPress->getLocalizedSetting('sponsorNote') == '' && empty($pressSettings.sponsors))}<li>&#187; <a href="{url op="pressSponsorship"}">{translate key="about.pressSponsorship"}</a></li>{/if}
	<li>&#187; <a href="{url op="siteMap"}">{translate key="about.siteMap"}</a></li>
	<li>&#187; <a href="{url op="aboutThisPublishingSystem"}">{translate key="about.aboutThisPublishingSystem"}</a></li>
	{call_hook name="Templates::About::Index::Other"}
</ul>

{include file="common/footer.tpl"}

