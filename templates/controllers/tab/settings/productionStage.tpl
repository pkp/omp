{**
 * controllers/tab/settings/productionStage.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production Stage settings management.
 *
 *}

<h3>{translate key="manager.setup.productionLibrary"}</h3>

{url|assign:productionLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_PRODUCTION}
{load_url_in_div id="productionLibraryGridDiv" url=$productionLibraryGridUrl}

<div class="separator"></div>

<h3>{translate key="manager.setup.publicationFormats"}</h3>

<p>{translate key="manager.setup.publicationFormatsDescription"}</p>

{url|assign:publicationFormatsUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.PublicationFormatsListbuilderHandler" op="fetch"}
{load_url_in_div id="publicationFormatsContainer" url=$publicationFormatsUrl}

<div class="separator"></div>

<h3>{translate key="manager.setup.productionTemplates"}</h3>

{url|assign:productionTemplateLibraryUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_PRODUCTION_TEMPLATE}
{load_url_in_div id="productionTemplateLibraryDiv" url=$productionTemplateLibraryUrl}