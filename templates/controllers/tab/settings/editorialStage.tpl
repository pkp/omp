{**
 * controllers/tab/settings/editorialStage.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Editorial Stage settings management.
 *
 *}
<div class="pkp_form">
{fbvFormSection label="manager.setup.editorialLibrary" description="manager.setup.editorialLibraryDescription"}

{url|assign:editorialLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_EDITORIAL}
{load_url_in_div id="editorialLibraryGridDiv" url=$editorialLibraryGridUrl}
{/fbvFormSection}
</div>