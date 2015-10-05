{**
 * templates/frontend/pages/index.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the front page of the site
 *
 * @uses $spotlights array Selected spotlights to promote on the homepage
 * @uses $categories array List of categories in this press
 * @uses $series array List of series in this press
 *}
{include file="common/frontend/header.tpl" isFullWidth=1}

<div class="page page_homepage">

    {* Spotlights *}
    {if count($spotlights)}
        <div class="row row_spotlights">
            {include file="frontend/components/spotlights.tpl"}
        </div>
    {/if}

    {* Search and browse section *}
    <div class="row row_find">
        {include file="frontend/components/searchForm_homepage.tpl"}

        {if count($categories) || count($series)}
            {include file="frontend/components/browseList.tpl"}
        {/if}
    </div>



</div>
{include file="common/frontend/footer.tpl" isFullWidth=1}
