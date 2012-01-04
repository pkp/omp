{**
 * templates/common/sitenav.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site-Wide Navigation Bar
 *}

<div class="pkp_structure_head_siteNav">
	<ul class="pkp_helpers_flatlist pkp_helpers_align_left">
		<li class="home"><a href="{url page="index"}">{$siteTitle}</a></li>
		{if $isUserLoggedIn}
			{if array_intersect(array(ROLE_ID_SITE_ADMIN), $userRoles)}
				<li><a href="{url page="admin" op="index"}">{translate key="navigation.admin"}</a></li>
			{/if}
			{if $hasOtherPresses}
				<li>{include file="common/pressSwitcher.tpl"}</li>
			{/if}
		{/if}
	</ul>
	<ul class="pkp_helpers_flatlist pkp_helpers_align_right">
		{if $isUserLoggedIn}
			<li class="profile">{translate key="user.hello"}&nbsp;<a href="{url page="user" op="profile"}">{$loggedInUsername|escape}</a></li>
			<li><a href="{url page="login" op="signOut"}">{translate key="user.logOut"}</a></li>
		{else}
			<li><a href="{url page="login"}">{translate key="navigation.login"}</a></li>
			<li><a href="{url page="user" op="register"}">{translate key="navigation.register"}</a></li>
		{/if}
	</ul>
</div>
