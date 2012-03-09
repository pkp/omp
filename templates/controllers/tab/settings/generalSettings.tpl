{**
 * controllers/tab/settings/generalSettings.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Publication process general settings (book file types).
 *
 *}

<div class="pkp_form">
	{fbvFormSection label="manager.setup.genres" description="manager.setup.genresDescription"}
		{url|assign:genresUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.genre.GenreGridHandler" op="fetchGrid"}
		{load_url_in_div id="genresContainer" url=$genresUrl}
	{/fbvFormSection}
</div>