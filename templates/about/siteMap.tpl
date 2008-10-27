{**
 * siteMap.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press / Site Map.
 *
 * TODO: Show the site map.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="about.siteMap"}
{include file="common/header.tpl"}
{/strip}

<ul class="plain">
<li>
	<a href="{url context="index" page="index" op="index"}">{translate key="navigation.home"}</a><br/>
	<ul class="plain">
	{if $presses|@count>1 && !$currentPress}
		{foreach from=$presses item=press}
			<li><a href="{url context=`$press->getPath()` page="about" op="siteMap"}">{$press->getPressName()|escape}</a></li>
		{/foreach}
	{else}
		{if $presses|@count==1}
			{assign var=currentPress value=$presses[0]}
		{else}
			<li><a href="{url context="index" page="about" op="siteMap"}">{translate key="press.presses"}</a><br/>
			<ul class="plain">
			{assign var=onlyOnePress value=1}
		{/if}

		<li><a href="{url context=`$currentPress->getPath()`}">{$currentPress->getPressName()|escape}</a><br/>
			<ul class="plain">
				<li><a href="{url context=`$currentPress->getPath()` page="about"}">{translate key="navigation.about"}</a></li>
				<li>
					{if $isUserLoggedIn}
						<ul class="plain">
							<li><a href="{url context=`$currentPress->getPath()` page="user"}">{translate key="navigation.userHome"}</a><br/>
								<ul class="plain">
									{assign var=currentPressId value=$currentPress->getPressId()}
									{foreach from=$rolesByPress[$currentPressId] item=role}
									{translate|assign:"roleName" key=$role->getRoleName()}
										<li><a href="{url context=`$currentPress->getPath()` page=`$role->getRolePath()`}">{$roleName|escape}</a></li>
									{/foreach}
								</ul>
							</li>
						</ul>
					{else}
						<ul class="plain">
							<li><a href="{url context=`$currentPress->getPath()` page="login"}">{translate key="navigation.login"}</a></li>
							<li><a href="{url context=`$currentPress->getPath()` page="register"}">{translate key="navigation.register"}</a></li>
						</ul>
					{/if}
				</li>
				<li><a href="{url context=`$currentPress->getPath()` page="search"}">{translate key="navigation.search"}</a><br />
					<ul class="plain">
						<li><a href="{url context=`$currentPress->getPath()` page="search" op="authors"}">{translate key="navigation.browseByAuthor"}</a></li>
						<li><a href="{url context=`$currentPress->getPath()` page="search" op="titles"}">{translate key="navigation.browseByTitle"}</a></li>
					</ul>
				</li>
		<!--		<li>{translate key="issue.issues"}<br/>
					<ul class="plain">
						<li><a href="{url context=`$currentPress->getPath()` page="issue" op="current"}">{translate key="press.currentIssue"}</a></li>
						<li><a href="{url context=`$currentPress->getPath()` page="issue" op="archive"}">{translate key="navigation.archives"}</a></li>
					</ul>
				</li>
		-->		{foreach from=$navMenuItems item=navItem}
					{if $navItem.url != '' && $navItem.name != ''}<li><a href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{url page=""}{$navItem.url|escape}{/if}">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name|escape}{/if}</a></li>{/if}
				{/foreach}
			</ul>
		</li>	
		{if $onlyOnePress}</ul></li>{/if}

	{/if}
	</ul>
</li>
{if $isSiteAdmin}
	<li><a href="{url context="index" page=$isSiteAdmin->getRolePath()}">{translate key=$isSiteAdmin->getRoleName()}</a></li>
{/if}
<li><a href="http://pkp.sfu.ca/omp">{translate key="common.openMonographPress"}</a></li>
<li><a href="javascript:openHelp('{url context="index" page="help"}')">{translate key="help.help"}</a></li>
</ul>

{include file="common/footer.tpl"}
