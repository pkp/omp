{**
 * navbar.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Navigation Bar
 *
 *}

{if $isUserLoggedIn}
<div class="navigation">
	<ul class="sf-menu">
		<li><a href="{url page="index"}">{translate key="navigation.pressHome"}</a>
			<ul>
				{if $enableAnnouncements}
					<li><a href="{url page="announcement"}">{translate key="announcement.announcements"}</a></li>
				{/if}
				<li>
					<a href="{url page="about"}">{translate key="about.aboutThePress"}<span class="sf-sub-indicator"> »</span></a>
					<ul>
						{if not (empty($pressSettings.mailingAddress) && empty($pressSettings.contactName) && empty($pressSettings.contactAffiliation) && empty($pressSettings.contactMailingAddress) && empty($pressSettings.contactPhone) && empty($pressSettings.contactFax) && empty($pressSettings.contactEmail) && empty($pressSettings.supportName) && empty($pressSettings.supportPhone) && empty($pressSettings.supportEmail))}
							<li><a href="{url page="about" op="contact"}">{translate key="about.contact"}</a></li>
						{/if}
						<li><a href="{url page="about" op="editorialTeam"}">{translate key="about.editorialTeam"}</a></li>
						<li><a href="{url page="about" op="editorialPolicies"}">{translate key="about.policies"}</a></li>
						<li><a href="{url page="about" op="submissions"}">{translate key="about.submissions"}</a></li>
					</ul>
				</li>
				<li><a href="{url page="issue" op="current"}">{translate key="navigation.currentCatalogue"}</a></li>
				<li><a href="{url page="issue" op="archive"}">{translate key="navigation.backCatalogue"}</a></li>
				<li><a href="{url page="search"}">{translate key="navigation.search"}</a></li>
			</ul>
		</li>
		<li><a href="{url page="dashboard"}">{translate key="navigation.dashboard"}</a></li>
		<li>
			<a href="#">!In Progress</a>
			<ul>
				<li><a href="#">!Submission</a></li>
				<li><a href="#">!Review</a></li>
				<li><a href="#">!Editorial</a></li>
				<li><a href="#">!Production</a></li>
			</ul>
		</li>
		<li><a href="{url page="issue" op="archive"}">{translate key="navigation.published"}</a></li>
		<li>
			<a href="#">!Settings</a>
			<ul>
				<li><a href="{url page="manager" op="setup"}">Press Setup</a></li>
				<li><a href="#">!Data</a></li>
				<li><a href="#">!System</a></li>
				<li><a href="#">!User Management</a></li>
			</ul>
		</li>
		<li><a href="{url page="search"}">!{translate key="navigation.search"}</a></li>
	</ul>
</div>
{else}
<div class="navigation">
	<ul class="sf-menu">
		<li><a href="{url page="index"}">{translate key="navigation.pressHome"}</a></li>
		{if $enableAnnouncements}
			<li><a href="{url page="announcement"}">{translate key="announcement.announcements"}</a></li>
		{/if}
		<li>
			<a href="{url page="about"}">{translate key="about.aboutThePress"}<span class="sf-sub-indicator"> »</span></a>
			<ul>
				{if not (empty($pressSettings.mailingAddress) && empty($pressSettings.contactName) && empty($pressSettings.contactAffiliation) && empty($pressSettings.contactMailingAddress) && empty($pressSettings.contactPhone) && empty($pressSettings.contactFax) && empty($pressSettings.contactEmail) && empty($pressSettings.supportName) && empty($pressSettings.supportPhone) && empty($pressSettings.supportEmail))}
					<li><a page="about" href="{url page="about" op="contact"}">{translate key="about.contact"}</a></li>
				{/if}
				<li><a href="{url page="about" op="editorialTeam"}">{translate key="about.editorialTeam"}</a></li>
				<li><a page="about" href="{url page="about" op="editorialPolicies"}">{translate key="about.policies"}</a></li>
				<li><a page="about" href="{url page="about" op="submissions"}">{translate key="about.submissions"}</a></li>
			</ul>
		</li>
		<li><a href="{url page="issue" op="current"}">{translate key="navigation.currentCatalogue"}</a></li>
		<li><a href="{url page="issue" op="archive"}">{translate key="navigation.backCatalogue"}</a></li>
		<li><a href="{url page="search"}">{translate key="navigation.search"}</a></li>
	</ul>
</div>
{/if}