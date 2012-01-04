{**
 * siteMap.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press / Site Map.
 *}
{strip}
{assign var="pageTitle" value="about.siteMap"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the file upload form handler.
	$(function() {ldelim}
		$('a.openHelp').each(function() {ldelim}
			$(this).click(function() {ldelim}
				openHelp($(this).attr('href'));
				return false;
			{rdelim})
		{rdelim});
	{rdelim});
</script>

<ul class="plain">
<li>
	<a href="{url press="index" page="index" op="index"}">{translate key="navigation.home"}</a><br/>
	<a href="{url press="index" page="user"}">{translate key="navigation.userHome"}</a><br/>
	<ul class="plain">
	{if $presses|@count>1 && !$currentPress}
		{foreach from=$presses item=press}
			<li><a href="{url press=$press->getPath() page="about" op="siteMap"}">{$press->getLocalizedName()|escape}</a></li>
		{/foreach}
	{else}
		{if $presses|@count==1}
			{assign var=currentPress value=$presses[0]}
		{else}
			<li><a href="{url press="index" page="about" op="siteMap"}">{translate key="press.presses"}</a><br/>
			<ul class="plain">
			{assign var=onlyOnePress value=1}
		{/if}

		<li><a href="{url press=$currentPress->getPath()}">{$currentPress->getLocalizedName()|escape}</a><br/>
			<ul class="plain">
				<li><a href="{url press=$currentPress->getPath() page="about"}">{translate key="navigation.about"}</a></li>
				<li>
					{if $isUserLoggedIn}
						<ul class="plain">
							{assign var=currentPressId value=$currentPress->getId()}
							{foreach from=$userGroupsByPress[$currentPressId]->toArray() item=userGroup}
								<li><a href="{url press=$currentPress->getPath() page=$userGroup->getPath()}">{$userGroup->getLocalizedName()|escape}</a></li>
							{/foreach}
						</ul>
					{else}
						<ul class="plain">
							<li><a href="{url press=$currentPress->getPath() page="login"}">{translate key="navigation.login"}</a></li>
							<li><a href="{url press=$currentPress->getPath() page="register"}">{translate key="navigation.register"}</a></li>
						</ul>
					{/if}
				</li>
				<li><a href="{url press=$currentPress->getPath() page="search"}">{translate key="navigation.search"}</a><br />
					<ul class="plain">
						<li><a href="{url press=$currentPress->getPath() page="search" op="authors"}">{translate key="navigation.browseByAuthor"}</a></li>
						<li><a href="{url press=$currentPress->getPath() page="search" op="titles"}">{translate key="navigation.browseByTitle"}</a></li>
					</ul>
				</li>
				{foreach from=$navMenuItems item=navItem}
					{if $navItem.url != '' && $navItem.name != ''}<li><a href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{url page=""}{$navItem.url|escape}{/if}">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name|escape}{/if}</a></li>{/if}
				{/foreach}
			</ul>
		</li>	
		{if $onlyOnePress}</ul></li>{/if}

	{/if}
	</ul>
</li>
{if $isSiteAdmin}
	<li><a href="{url press="index" page=$isSiteAdmin->getPath()}">{translate key=$isSiteAdmin->getRoleName()}</a></li>
{/if}
<li><a href="http://pkp.sfu.ca/omp">{translate key="common.openMonographPress"}</a></li>
<li><a class="openHelp" href="{url press="index" page="help"}">{translate key="help.help"}</a></li>
</ul>

{include file="common/footer.tpl"}

