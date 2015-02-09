{**
 * templates/header/localnav.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press-Specific Navigation Bar
 *}

{capture assign="publicMenu"}
	{if $currentPress}
		{if $enableAnnouncements}
			<li><a href="{url router=$smarty.const.ROUTE_PAGE page="announcement"}">{translate key="announcement.announcements"}</a></li>
		{/if}
		<li><a href="#">{translate key="navigation.about"}</a>
			<ul>
				{if not empty($contextInfo.contact)}
					<li><a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="contact"}">{translate key="about.contact"}</a></li>
				{/if}
				{if not empty($contextInfo.description)}
					<li><a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="description"}">{translate key="about.description"}</a></li>
				{/if}
				{if not empty($contextInfo.editorialTeam)}
					<li><a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="editorialTeam"}">{translate key="about.editorialTeam"}</a></li>
				{/if}
				{if not empty($contextInfo.editorialPolicies)}
					<li><a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="editorialPolicies"}">{translate key="about.policies"}</a></li>
				{/if}
				<li><a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="submissions"}">{translate key="about.submissions"}</a></li>
				{if not empty($contextInfo.sponsorship)}
					<li><a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="sponsorship"}">{translate key="about.pressSponsorship"}</a></li>
				{/if}
			</ul>
		</li>
	{/if}
{/capture}

<div class="pkp_structure_head_localNav">
	{if $isUserLoggedIn}
		<ul class="sf-menu">
			{if array_intersect(array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_REVIEWER, ROLE_ID_AUTHOR), $userRoles)}
				<li><a href="{url router=$smarty.const.ROUTE_PAGE page="dashboard"}">{translate key="navigation.dashboard"}</a></li>
			{/if}
			{if $currentPress}
				<li><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog"}">{translate key="navigation.catalog"}</a>
				{if array_intersect(array(ROLE_ID_MANAGER), $userRoles)}
					<li>
						<a href="#">{translate key="navigation.management"}</a>
						<ul>
							<li>
								<a href="{url router=$smarty.const.ROUTE_PAGE page="management" op="settings" path="index"}">{translate key="navigation.settings"}</a>
								<ul>
									<li><a href="{url router=$smarty.const.ROUTE_PAGE page="management" op="settings" path="press"}">{translate key="context.context"}</a></li>
									<li><a href="{url router=$smarty.const.ROUTE_PAGE page="management" op="settings" path="website"}">{translate key="manager.website"}</a></li>
									<li><a href="{url router=$smarty.const.ROUTE_PAGE page="management" op="settings" path="publication"}">{translate key="manager.workflow"}</a></li>
									<li><a href="{url router=$smarty.const.ROUTE_PAGE page="management" op="settings" path="distribution"}">{translate key="manager.distribution"}</a></li>
									<li><a href="{url router=$smarty.const.ROUTE_PAGE page="management" op="settings" path="access"}">{translate key="navigation.access"}</a></li>
								</ul>
							</li>
							<li>
								<a href="{url router=$smarty.const.ROUTE_PAGE page="management" op="tools" path="index"}">{translate key="navigation.tools"}</a>
								<ul>
									<li><a href="{url router=$smarty.const.ROUTE_PAGE page="management" op="importexport"}">{translate key="navigation.tools.importExport"}</a></li>
									<li><a href="{url router=$smarty.const.ROUTE_PAGE page="management" op="tools" path="statistics"}">{translate key="navigation.tools.statistics"}</a></li>
								</ul>
							</li>
							{call_hook name="Templates::Header::Localnav::AdditionalManagementItems"}
						</ul>
					</li>
				{/if}{* ROLE_ID_MANAGER *}
				{$publicMenu}
			{/if}
		</ul>
	{else}{* !$isUserLoggedIn *}
		<ul class="sf-menu">
			<li><a href="{url router=$smarty.const.ROUTE_PAGE page="index"}">{translate key="navigation.home"}</a></li>
			<li><a href="{url router=$smarty.const.ROUTE_PAGE page="catalog"}">{translate key="navigation.catalog"}</a>
			{$publicMenu}
		</ul>
	{/if}{* $isUserLoggedIn *}
</div>
