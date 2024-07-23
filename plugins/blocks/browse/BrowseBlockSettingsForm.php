<?php

/**
 * @file plugins/blocks/browse/BrowseBlockSettingsForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class BrowseBlockSettingsForm
 *
 * @ingroup plugins_blocks_browse
 *
 * @brief Form for press managers to setup Browse block plugin
 */

namespace APP\plugins\blocks\browse;

use PKP\form\Form;

class BrowseBlockSettingsForm extends Form
{
    /** @var int press ID */
    public $_pressId;

    /** @var BrowseBlockPlugin Browse block plugin */
    public $_plugin;


    /**
     * Constructor
     *
     * @param BrowseBlockPlugin $plugin
     */
    public function __construct($plugin, int $pressId)
    {
        $this->setPressId($pressId);
        $this->setPlugin($plugin);

        parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));

        $this->setData('pluginName', $plugin->getName());
        $this->setData('pluginJavaScriptPath', $plugin->getPluginPath());
    }

    /**
     * Get the Press ID.
     */
    public function getPressId(): int
    {
        return $this->_pressId;
    }

    /**
     * Set the Press ID.
     */
    public function setPressId(int $pressId)
    {
        $this->_pressId = $pressId;
    }

    /**
     * Get the plugin.
     *
     * @return BrowseBlockPlugin
     */
    public function getPlugin()
    {
        return $this->_plugin;
    }

    /**
     * Set the plugin.
     *
     * @param BrowseBlockPlugin $plugin
     */
    public function setPlugin($plugin)
    {
        $this->_plugin = $plugin;
    }

    /**
     * @see Form::initData()
     */
    public function initData()
    {
        $pressId = $this->getPressId();
        $plugin = $this->getPlugin();
        foreach ($this->_getFormFields() as $fieldName => $fieldType) {
            $this->setData($fieldName, $plugin->getSetting($pressId, $fieldName));
        }
    }

    /**
     * @see Form::readInputData()
     */
    public function readInputData()
    {
        $this->readUserVars(array_keys($this->_getFormFields()));
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        $plugin = $this->getPlugin();
        $pressId = $this->getPressId();
        foreach ($this->_getFormFields() as $fieldName => $fieldType) {
            $plugin->updateSetting($pressId, $fieldName, $this->getData($fieldName), $fieldType);
        }
        parent::execute(...$functionArgs);
    }

    public function _getFormFields()
    {
        return [
            'browseNewReleases' => 'bool',
            'browseCategories' => 'bool',
            'browseSeries' => 'bool',
        ];
    }
}
