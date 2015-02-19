{**
 * templates/about/contact.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press / Press Contact.
 *}
{strip}
{assign var="pageTitle" value="about.pressContact"}
{include file="common/header.tpl"}
{/strip}

{url|assign:editUrl page="management" op="settings" path="press" anchor="contact"}
{include file="common/linkToEditPage.tpl" editUrl=$editUrl}

{if !empty($contactInfo.mailingAddress)}
<h3>{translate key="common.mailingAddress"}</h3>
<p>
	{$contactInfo.mailingAddress|nl2br}
</p>
<div class="separator"></div>
{/if}

{if not ($contactInfo.contactTitle == '' && $contactInfo.contactAffiliation == '' && $contactInfo.contactMailingAddress == '' && empty($contactInfo.contactPhone) && empty($contactInfo.contactFax) && empty($contactInfo.contactEmail))}
<h3>{translate key="about.contact.principalContact"}</h3>
<p>
	{if !empty($contactInfo.contactName)}
		<strong>{$contactInfo.contactName|escape}</strong><br />
	{/if}

	{assign var=s value=$contactInfo.contactTitle}
	{if $s}{$s|escape}<br />{/if}

	{assign var=s value=$contactInfo.contactAffiliation}
	{if $s}{$s|strip_unsafe_html}{/if}

	{assign var=s value=$contactInfo.contactMailingAddress}
	{if $s}{$s|strip_unsafe_html}{/if}
</p>
<p>
	{if !empty($contactInfo.contactPhone)}
		{translate key="about.contact.phone"}: {$contactInfo.contactPhone|escape}<br />
	{/if}
	{if !empty($contactInfo.contactFax)}
		{translate key="about.contact.fax"}: {$contactInfo.contactFax|escape}<br />
	{/if}
	{if !empty($contactInfo.contactEmail)}
		{translate key="about.contact.email"}: {mailto address=$contactInfo.contactEmail|escape encode="hex"}
	{/if}
</p>
<div class="separator"></div>
{/if}

{if not (empty($contactInfo.supportName) && empty($contactInfo.supportPhone) && empty($contactInfo.supportEmail))}
<h3>{translate key="about.contact.supportContact"}</h3>
<p>
	{if !empty($contactInfo.supportName)}
		<strong>{$contactInfo.supportName|escape}</strong><br />
	{/if}
	{if !empty($contactInfo.supportPhone)}
		{translate key="about.contact.phone"}: {$contactInfo.supportPhone|escape}<br />
	{/if}
	{if !empty($contactInfo.supportEmail)}
		{translate key="about.contact.email"}: {mailto address=$contactInfo.supportEmail|escape encode="hex"}<br />
	{/if}
</p>
{/if}

{include file="common/footer.tpl"}
