{**
 * templates/frontend/components/primaryNavMenu.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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

	{if $contextInfo.editorialTeam || $contextInfo.submissions}
		{assign var="submenu_class_attr" value=" class='has_submenu'"}
	{/if}
	<li{$submenu_class_attr}>
		<a href="{url router=$smarty.const.ROUTE_PAGE page="about"}">
			{translate key="navigation.about"}
		</a>
		{if $submenu_class_attr}
		<ul>
			<li>
				<a href="{url router=$smarty.const.ROUTE_PAGE page="about"}">
					{translate key="about.aboutThePress"}
				</a>
			</li>
			{if not empty($contextInfo.editorialTeam)}
			<li>
				<a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="editorialTeam"}">
					{translate key="about.editorialTeam"}
				</a>
			</li>
			{/if}
			{if not empty($contextInfo.submissions)}
			<li>
				<a href="{url router=$smarty.const.ROUTE_PAGE page="about" op="submissions"}">
					{translate key="about.submissions"}
				</a>
			</li>
			{/if}
		</ul>
		{/if}
	</li>
</ul>
