<!-- templates/about/contact.tpl -->

{**
 * contact.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press / Press Contact.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="about.pressContact"}
{include file="common/header.tpl"}
{/strip}

{if !empty($pressSettings.mailingAddress)}
<h3>{translate key="common.mailingAddress"}</h3>
<p>
	{$pressSettings.mailingAddress|nl2br}
</p>
{/if}

{if not ($currentPress->getLocalizedSetting('contactTitle') == '' && $currentPress->getLocalizedSetting('contactAffiliation') == '' && $currentPress->getLocalizedSetting('contactMailingAddress') == '' && empty($pressSettings.contactPhone) && empty($pressSettings.contactFax) && empty($pressSettings.contactEmail))}
<h3>{translate key="about.contact.principalContact"}</h3>
<p>
	{if !empty($pressSettings.contactName)}
		<strong>{$pressSettings.contactName|escape}</strong><br />
	{/if}

	{assign var=s value=$currentPress->getLocalizedSetting('contactTitle')}
	{if $s}{$s|escape}<br />{/if}

	{assign var=s value=$currentPress->getLocalizedSetting('contactAffiliation')}
	{if $s}{$s|escape}<br />{/if}

	{assign var=s value=$currentPress->getLocalizedSetting('contactMailingAddress')}
	{if $s}{$s|nl2br}<br />{/if}

	{if !empty($pressSettings.contactPhone)}
		{translate key="about.contact.phone"}: {$pressSettings.contactPhone|escape}<br />
	{/if}
	{if !empty($pressSettings.contactFax)}
		{translate key="about.contact.fax"}: {$pressSettings.contactFax|escape}<br />
	{/if}
	{if !empty($pressSettings.contactEmail)}
		{translate key="about.contact.email"}: {mailto address=$pressSettings.contactEmail|escape encode="hex"}<br />
	{/if}
</p>
{/if}

{if not (empty($pressSettings.supportName) && empty($pressSettings.supportPhone) && empty($pressSettings.supportEmail))}
<h3>{translate key="about.contact.supportContact"}</h3>
<p>
	{if !empty($pressSettings.supportName)}
		<strong>{$pressSettings.supportName|escape}</strong><br />
	{/if}
	{if !empty($pressSettings.supportPhone)}
		{translate key="about.contact.phone"}: {$pressSettings.supportPhone|escape}<br />
	{/if}
	{if !empty($pressSettings.supportEmail)}
		{translate key="about.contact.email"}: {mailto address=$pressSettings.supportEmail|escape encode="hex"}<br />
	{/if}
</p>
{/if}

{include file="common/footer.tpl"}

<!-- / templates/about/contact.tpl -->

