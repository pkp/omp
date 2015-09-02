{**
 * templates/frontend/pages/about.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view the press's description, contact details,
 *  policies and more.
 *
 * @uses $currentPress Press The current press
 * @uses $contact array Contact details for this press
 * @uses $description string Description of this press
 * @uses $sponshorshipInfo array Sponsor and contributor details for this press
 * @uses $editorialPolicies array Focus, review policy, open access policy, etc
 * @has $editorialTeam array Info on members of the editorial team
 * @has $submissions array Info on the submission policy
 *}
{include file="common/frontend/header.tpl" pageTitle="about.aboutThePress"}

<div class="page page_about">
	<h1 class="page_title">
		{translate key="about.aboutThePress"}
		{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="masthead" sectionTitleKey="about.aboutThePress"}
	</h1>

	{if $description}
	<div class="description">
		{$description|nl2br}
	</div>
	{/if}


	{* Contact section *}
	<div class="contact_section">
		<h2>
			{translate key="about.pressContact"}
			{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="contact" sectionTitleKey="about.pressContact"}
		</h2>

		{if $contact.mailingAddress}
			<div class="address">
				{$contact.mailingAddress}
			</div>
		{/if}

		{* Primary contact *}
		{if $contact.contactTitle || $contact.contactName ||
				$contact.contactAffiliation ||
				$contact.contactMailingAddress || $contact.contactPhone ||
				$contact.contactFax || $contact.contactEmail}
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

				{if $contact.contactMailingAddress}
				<div class="address">
					{$contact.contactMailingAddress|strip_unsafe_html}
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

				{if $contact.contactFax}
				<div class="fax">
					<span class="label">
						{translate key="about.contact.fax"}
					</span>
					<span class="value">
						{$contact.contactFax|escape}
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

	{* Policies *}
	{if $editorialPolicies.focusScopeDesc}
		<div class="focus_scope">
			<h2>
				{translate key="about.focusAndScope"}
				{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="policies" sectionTitleKey="about.focusAndScope"}
			</h2>
			<div>
				{$editorialPolicies.focusScopeDesc}
			</div>
		</div>
	{/if}

	{if $editorialPolicies.reviewPolicy}
		<div class="review">
			<h2>
				{translate key="about.reviewPolicy"}
				{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="policies" sectionTitleKey="about.reviewPolicy"}
			</h2>
			{$editorialPolicies.reviewPolicy|nl2br}
		</div>
	{/if}

	{if $editorialPolicies.openAccessPolicy}
		<div class="open_access">
			<h2>
				{translate key="about.openAccessPolicy"}
				{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="policies" sectionTitleKey="about.openAccessPolicy"}
			</h2>
			{$editorialPolicies.openAccessPolicy|nl2br}
		</div>
	{/if}

	{foreach key=key from=$editorialPolicies.customAboutItems item=customAboutItem}
		{if !empty($customAboutItem.title)}
			<div class="custom custom-{$key|escape}">
				<h2>
					{$customAboutItem.title|escape}
					{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="policies" sectionTitle=$customAboutItem.title|escape}
				</h2>
				{$customAboutItem.content|nl2br}
			</div>
		{/if}
	{/foreach}

	{* Sponsors *}
	{if $sponsorship.sponsorNote || $sponsorship.sponsors}
		<div class="sponsors">
			<h2>
				{translate key="about.sponsors"}
				{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="affiliationAndSupport" sectionTitleKey="about.sponsors"}
			</h2>
			{$sponsorship.sponsorNote|nl2br}
			{if $sponsorship.sponsors}
				<ul>
					{foreach from=$sponsorship.sponsors item=sponsor}
						<li>
							<a href="{$sponsor.url|escape}">
								{$sponsor.institution|escape}
							</a>
						</li>
					{/foreach}
				</ul>
			{/if}
		</div>
	{/if}

	{* Contributors *}
	{if $sponsorship.contributorNote || $sponsorship.contributors}
		<div class="contributors">
			<h2>
				{translate key="about.contributors"}
				{include file="frontend/components/editLink.tpl" page="management" op="settings" path="press" anchor="affiliationAndSupport" sectionTitleKey="about.contributors"}
			</h2>
			{$sponsorship.contributorNote|nl2br}
			{if $sponsorship.contributors}
				<ul>
					{foreach from=$sponsorship.contributors item=contributor}
						<li>
							<a href="{$contributor.url|escape}">
								{$contributor.institution|escape}
							</a>
						</li>
					{/foreach}
				</ul>
			{/if}
		</div>
	{/if}

</div><!-- .page -->

{include file="common/frontend/footer.tpl"}
