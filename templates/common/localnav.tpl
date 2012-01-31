{**
 * templates/common/localnav.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
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
			{if array_intersect(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_REVIEWER, ROLE_ID_AUTHOR), $userRoles)}
			<li><a href="{url page="dashboard"}">{translate key="navigation.dashboard"}</a></li>
			{/if}
			{if $currentPress}
				{if array_intersect(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR), $userRoles)}
					<li>
						<a href="#">{translate key="navigation.catalog"}</a>
						<ul>
							<li><a href="{url page="manageCatalog"}">{translate key="navigation.catalog.manage"}</a></li>
							{if array_intersect(array(ROLE_ID_PRESS_MANAGER), $userRoles)}
								<li>
									<a href="#">{translate key="navigation.catalog.administration"}</a>
									<ul>
										<li><a href="{url page="management" op="categories"}">{translate key="navigation.catalog.administration.categories"}</a></li>
										<li><a href="{url page="management" op="series"}">{translate key="navigation.catalog.administration.series"}</a></li>
									</ul>
								</li>
							{/if}
						</ul>
					</li>
				{/if}{* ROLE_ID_PRESS_MANAGER || ROLE_ID_SERIES_EDITOR *}
				{if array_intersect(array(ROLE_ID_PRESS_MANAGER), $userRoles)}
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
									{* <li><a href="{url page="manager" op="index"}">{translate key="navigation.data"}</a></li> *}
									{* <li><a href="{url page="manager" op="data"}">{translate key="navigation.data"}</a></li> *}
									<li><a href="{url page="management" op="settings" path="access"}">{translate key="navigation.access"}</a></li>
								</ul>
							</li>
							<li>
								<a href="{url page="management" op="tools" path="index"}">{translate key="navigation.tools"}</a>
								<ul>
									<li><a href="{url page="manager" op="importexport"}">{translate key="navigation.tools.importExport"}</a></li>
								</ul>
							</li>
						</ul>
					</li>
				{/if}
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
