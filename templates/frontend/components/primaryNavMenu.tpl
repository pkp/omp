{**
 * templates/frontend/components/primaryNavMenu.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Primary navigation menu list for OMP
 *}

<ul id="navigationPrimary" class="pkp_navigation_primary pkp_nav_list">

	{if $enableAnnouncements}
		<li>
			<a href="{url router=$smarty.const.ROUTE_PAGE page="announcement"}">
				{translate key="announcement.announcements"}
			</a>
		</li>
	{/if}

	<li>
		<a href="{url router=$smarty.const.ROUTE_PAGE page="catalog"}">
			{translate key="navigation.catalog"}
		</a>
	</li>

	{if $currentPress && ($currentPress->getLocalizedSetting('editorialTeam') || $currentPress->getLocalizedSetting('submissions'))}
		{assign var="hasSubmenu" value=true}
	{/if}
	<li>
		<a href="{url router=$smarty.const.ROUTE_PAGE page="about"}">
			{translate key="navigation.about"}
		</a>
		{if $hasSubmenu}
		<ul>
			<li>
				<a href="{url router=$smarty.const.ROUTE_PAGE page="about"}">
					{translate key="about.aboutContext"}
				</a>
			</li>
			{if $currentPress && $currentPress->getLocalizedSetting('editorialTeam') != ''}
				<li>
					<a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="editorialTeam"}">
						{translate key="about.editorialTeam"}
					</a>
				</li>
			{/if}
			<li>
				<a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="submissions"}">
					{translate key="about.submissions"}
				</a>
			</li>
			{if $currentPress && ($currentPress->getSetting('mailingAddress') || $currentPress->getSetting('contactName'))}
				<li>
					<a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="contact"}">
						{translate key="about.contact"}
					</a>
				</li>
			{/if}
		</ul>
		{/if}
	</li>
</ul>
