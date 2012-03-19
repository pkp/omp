{**
 * controllers/tab/settings/editorialStage.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Editorial Stage settings management.
 *
 *}
<h3 class="pkp_grid_title">{translate key="manager.setup.editorialLibrary"}</h3>
<p class="pkp_grid_description">{translate key="manager.setup.editorialLibraryDescription"}</p>

{url|assign:editorialLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_EDITORIAL}
{load_url_in_div id="editorialLibraryGridDiv" url=$editorialLibraryGridUrl}