{**
 * controllers/tab/settings/editorialStage.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Editorial Stage settings management.
 *
 *}

<h3>{translate key="manager.setup.editorialLibrary"}</h3>

{url|assign:editorialLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_EDITORIAL}
{load_url_in_div id="editorialLibraryGridDiv" url=$editorialLibraryGridUrl}