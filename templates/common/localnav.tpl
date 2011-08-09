{**
 * templates/common/localnav.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press-Specific Navigation Bar
 *}

{capture assign="publicMenu"}
	{if $currentPress}
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
		<li>
			<a href="#">{translate key="navigation.catalog"}</a>
			<ul>
				<li><a href="#">{translate key="navigation.featuredBooks"}</a></li>
				<li><a href="#">{translate key="navigation.newReleases"}</a></li>
				<li><a href="#">{translate key="navigation.seriesAndEditions"}</a></li>
			</ul>
		</li>
		<li><a href="{url page="issue" op="archive"}">{translate key="navigation.backlist"}</a></li>
	{/if}
{/capture}

<div class="pkp_structure_head_localNav">
	{if $isUserLoggedIn}
		<ul class="sf-menu">
			<li><a href="{url page="dashboard"}">{translate key="navigation.dashboard"}</a></li>
			<li><a href="{url page="dashboard" op="status"}">{translate key="navigation.submissions"}</a></li>
			{if $currentPress}
				<li>
					<a href="#">{translate key="navigation.catalog"}</a>
					<ul>
						<li><a href="{url page="issue" op="archive"}">{translate key="navigation.published"}</a></li>
						<li><a href="#">{translate key="navigation.seriesAndEditions"}</a></li>
						<li><a href="#">{translate key="navigation.featuredBooks"}</a></li>
						<li><a href="#">{translate key="navigation.admin"}</a></li>
					</ul>
				</li>
				<li>
					<a href="#">{translate key="navigation.management"}</a>
					<ul>
						<li>
							<a href="{url page="management" op="settings" path="index"}">{translate key="navigation.settings"}</a>
							<ul>
								<li><a href="{url page="management" op="settings" path="press"}">{translate key="press.press"}</a></li>
								<li><a href="{url page="management" op="settings" path="website"}">{translate key="navigation.website"}</a></li>
								<li><a href="{url page="management" op="settings" path="publication"}">{translate key="navigation.publicationProcess"}</a></li>
								<li><a href="{url page="management" op="settings" path="distribution"}">{translate key="navigation.distributionProcess"}</a></li>
								{* Temporary link to manager's deprecated home page until settings pages are fully implemented, see #6196 *}
								<li><a href="{url page="manager" op="index"}">{translate key="navigation.data"}</a></li>
								{* <li><a href="{url page="manager" op="data"}">{translate key="navigation.data"}</a></li> *}
								<li><a href="{url page="management" op="settings" path="access"}">{translate key="navigation.access"}</a></li>
							</ul>
						</li>
					</ul>
				</li>
				{if $enableAnnouncements}
					<li><a href="{url page="manager" op="announcements"}">{translate key="announcement.announcements"}</a></li>
				{/if}
				<li>
					<a href="#">{translate key="navigation.publicMenu"}</a>
					<ul>{$publicMenu}</ul>
				</li>
			{/if}
		</ul>
	{else}{* !$isUserLoggedIn *}
		<ul class="sf-menu">
			{$publicMenu}
		</ul>
	{/if}{* $isUserLoggedIn *}
</div>
