{**
 * plugins/generic/customBlockManager/settingsForm.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Custom Block Manager -- CustomBlockGrid.
 *
 *}


{url|assign:customBlockGridUrl router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.customBlockManager.controllers.grid.CustomBlockGridHandler" op="fetchGrid" escape=false}
{load_url_in_div id="customBlockGridUrlGridContainer" url=$customBlockGridUrl}

