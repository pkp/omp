{**
 * plugins/blocks/languageToggle/block.tpl
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Common site sidebar menu for switching the current language.
 *
 * @uses $languageToggleLocales array Available locales as key/value pair. Value
 *       is a string representing the locale name
 * @uses $currentLocale string Name of current locale
 *}
{if $enableLanguageToggle}
<div class="pkp_block block_language">
	<h2 class="title">
		{translate key="common.language"}
	</h2>

	<div class="content">
		<ul>
			{foreach from=$languageToggleLocales item=localeName key=localeKey}
				<li class="locale_{$localeKey|escape}{if $localeKey == $currentLocale} current{/if}" lang="{$localeKey|replace:"_":"-"}">
					<a href="{url router=PKP\core\PKPApplication::ROUTE_PAGE page="user" op="setLocale" path=$localeKey source=$smarty.server.SERVER_NAME|cat:$smarty.server.REQUEST_URI}">
						{$localeName}
					</a>
				</li>
			{/foreach}
		</ul>
	</div>
</div><!-- .block_language -->
{/if}
