{**
 * plugins/blocks/languageToggle/block.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Common site sidebar menu for switching the current language.
 *
 * @uses $languageToggleLocales array Available locales as key/value pair. Value
 *       is a string representing the locale name
 * @uses $currentLocale string Name of current locale
 *}
{if $enableLanguageToggle}
<div class="pkp_block block_language">
	<span class="title">
		{translate key="common.language"}
	</span>

	<div class="content">
		<ul>
			{foreach from=$languageToggleLocales item=localeName key=localeKey}
				<li class="locale_{$localeKey|escape}{if $localeKey == $currentLocale} current{/if}">
					<a href="{url router=$smarty.const.ROUTE_PAGE page="user" op="setLocale" path=$localeKey source=$smarty.server.REQUEST_URI}">
						{$localeName}
					</a>
				</li>
			{/foreach}
		</ul>
	</div>
</div><!-- .block_language -->
{/if}
