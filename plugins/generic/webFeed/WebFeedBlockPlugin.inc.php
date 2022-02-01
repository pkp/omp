<?php

/**
 * @file plugins/generic/webFeed/WebFeedBlockPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class WebFeedBlockPlugin
 * @ingroup plugins_generic_webFeed
 *
 * @brief Class for block component of web feed plugin
 */

use PKP\plugins\BlockPlugin;

class WebFeedBlockPlugin extends BlockPlugin
{
    /** @var WebFeedPlugin Parent plugin */
    protected $_parentPlugin;

    /**
     * Constructor
     *
     * @var WebFeedPlugin $parentPlugin
     */
    public function __construct($parentPlugin)
    {
        parent::__construct();
        $this->_parentPlugin = $parentPlugin;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     *
     * @return string name of plugin
     */
    public function getName()
    {
        return 'WebFeedBlockPlugin';
    }

    /**
     * Hide this plugin from the management interface (it's subsidiary)
     */
    public function getHideManagement()
    {
        return true;
    }

    /**
     * Get the display name of this plugin.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.generic.webfeed.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription()
    {
        return __('plugins.generic.webfeed.description');
    }

    /**
     * Get the supported contexts (e.g. BLOCK_CONTEXT_...) for this block.
     *
     * @return array
     */
    public function getSupportedContexts()
    {
        return [BLOCK_CONTEXT_SIDEBAR];
    }

    /**
     * Override the builtin to get the correct plugin path.
     *
     * @return string
     */
    public function getPluginPath()
    {
        return $this->_parentPlugin->getPluginPath();
    }
}
