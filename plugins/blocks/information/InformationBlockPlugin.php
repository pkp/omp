<?php

/**
 * @file plugins/blocks/information/InformationBlockPlugin.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class InformationBlockPlugin
 *
 * @brief Class for information block plugin
 */

namespace APP\plugins\blocks\information;

use PKP\plugins\BlockPlugin;

class InformationBlockPlugin extends BlockPlugin
{
    /**
     * Install default settings on journal creation.
     *
     * @return string
     */
    public function getContextSpecificPluginSettingsFile()
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.block.information.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription()
    {
        return __('plugins.block.information.description');
    }

    /**
     * @copydoc BlockPlugin::getContents()
     *
     * @param null|mixed $request
     */
    public function getContents($templateMgr, $request = null)
    {
        $press = $request->getPress();
        if (!$press) {
            return '';
        }

        $templateMgr->assign('forReaders', $press->getLocalizedSetting('readerInformation'));
        $templateMgr->assign('forAuthors', $press->getLocalizedSetting('authorInformation'));
        $templateMgr->assign('forLibrarians', $press->getLocalizedSetting('librarianInformation'));
        return parent::getContents($templateMgr);
    }
}
