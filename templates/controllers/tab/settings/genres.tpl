{**
 * controllers/tab/settings/genres.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Publication process genres (book file types).
 *
 *}

{url|assign:genresUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.genre.GenreGridHandler" op="fetchGrid"}
{load_url_in_div id="genresContainer" url=$genresUrl}