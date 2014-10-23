{**
 * templates/locale.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List of component locales to edit for a particular locale
 *}
<p>{translate key="plugins.generic.translator.localeDescription"}</p>

<a name="localeFiles"></a>

{url|assign:localeFileGridUrl router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.translator.controllers.grid.LocaleFileGridHandler" op="fetchGrid" locale=$locale tabsSelector=$tabsSelector escape=false}
{load_url_in_div id="localeFileGridContainer-"|uniqid url=$localeFileGridUrl}

<a name="miscFiles"></a>

{url|assign:miscFileGridUrl router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.translator.controllers.grid.MiscTranslationFileGridHandler" op="fetchGrid" locale=$locale tabsSelector=$tabsSelector escape=false}
{load_url_in_div id="miscFileGridContainer-"|uniqid url=$miscFileGridUrl}

<a name="emails"></a>

{url|assign:emailGridUrl router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.translator.controllers.grid.EmailGridHandler" op="fetchGrid" locale=$locale tabsSelector=$tabsSelector escape=false}
{load_url_in_div id="emailGridContainer-"|uniqid url=$emailGridUrl}

</table>
