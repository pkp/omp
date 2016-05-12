{**
 * templates/frontend/pages/contact.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view the press's contact details.
 *
 * @uses $contact array Contact details for this press
 *}
{include file="frontend/components/header.tpl" pageTitle="about.contact"}

<div class="page page_contact">
	{include file="frontend/components/breadcrumbs.tpl" currentTitleKey="about.contact"}
	{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="contact" sectionTitleKey="about.contact"}

	{* Contact section *}
	<div class="contact_section">

		{if $contact.mailingAddress}
			<div class="address">
				{$contact.mailingAddress|nl2br|strip_unsafe_html}
			</div>
		{/if}

		{* Primary contact *}
		{if $contact.contactTitle || $contact.contactName || $contact.contactAffiliation || $contact.contactPhone || $contact.contactEmail}
			<div class="contact primary">
				<h3>
					{translate key="about.contact.principalContact"}
				</h3>

				{if $contact.contactName}
				<div class="name">
					{$contact.contactName|escape}
				</div>
				{/if}

				{if $contact.contactTitle}
				<div class="title">
					{$contact.contactTitle|escape}
				</div>
				{/if}

				{if $contact.contactAffiliation}
				<div class="affiliation">
					{$contact.contactAffiliation|strip_unsafe_html}
				</div>
				{/if}

				{if $contact.contactPhone}
				<div class="phone">
					<span class="label">
						{translate key="about.contact.phone"}
					</span>
					<span class="value">
						{$contact.contactPhone|escape}
					</span>
				</div>
				{/if}

				{if $contact.contactEmail}
				<div class="email">
					<a href="mailto:{$contact.contactEmail|escape}">
						{$contact.contactEmail|escape}
					</a>
				</div>
				{/if}
			</div>
		{/if}

		{* Technical contact *}
		{if $contact.supportName || $contact.supportPhone || $contact.supportEmail}
			<div class="contact support">
				<h3>
					{translate key="about.contact.supportContact"}
				</h3>

				{if $contact.supportName}
				<div class="name">
					{$contact.supportName|escape}
				</div>
				{/if}

				{if $contact.supportPhone}
				<div class="phone">
					<span class="label">
						{translate key="about.contact.phone"}
					</span>
					<span class="value">
						{$contact.supportPhone|escape}
					</span>
				</div>
				{/if}

				{if $contact.supportEmail}
				<div class="email">
					<a href="mailto:{$contact.supportEmail|escape}">
						{$contact.supportEmail|escape}
					</a>
				</div>
				{/if}
			</div>
		{/if}
	</div>

</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
