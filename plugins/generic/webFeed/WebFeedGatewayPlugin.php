<?php

/**
 * @file plugins/generic/webFeed/WebFeedGatewayPlugin.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class WebFeedGatewayPlugin
 * @brief Gateway component of web feed plugin
 *
 */

namespace APP\plugins\generic\webFeed;

use APP\facades\Repo;
use APP\submission\Collector;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\plugins\GatewayPlugin;
use PKP\db\DAORegistry;

class WebFeedGatewayPlugin extends GatewayPlugin
{
    /** @var WebFeedPlugin Parent plugin */
    public $_parentPlugin;

    /**
     * Constructor
     *
     * @param WebFeedPlugin $parentPlugin
     */
    public function __construct($parentPlugin)
    {
        parent::__construct();
        $this->_parentPlugin = $parentPlugin;
    }

    /**
     * Hide this plugin from the management interface (it's subsidiary)
     *
     * @return bool
     */
    public function getHideManagement()
    {
        return true;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     *
     * @return string name of plugin
     */
    public function getName()
    {
        return 'WebFeedGatewayPlugin';
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName()
    {
        return __('plugins.generic.webfeed.displayName');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription()
    {
        return __('plugins.generic.webfeed.description');
    }

    /**
     * Override the builtin to get the correct plugin path.
     */
    public function getPluginPath()
    {
        return $this->_parentPlugin->getPluginPath();
    }

    /**
     * Get whether or not this plugin is enabled. (Should always return true, as the
     * parent plugin will take care of loading this one when needed)
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->_parentPlugin->getEnabled();
    }

    /**
     * Handle fetch requests for this plugin.
     *
     * @param array $args Arguments.
     * @param PKPRequest $request Request object.
     */
    public function fetch($args, $request)
    {
        if (!$this->_parentPlugin->getEnabled()) {
            return false;
        }

        // Make sure the feed type is specified and valid
        $type = array_shift($args);
        $typeMap = [
            'rss' => 'rss.tpl',
            'rss2' => 'rss2.tpl',
            'atom' => 'atom.tpl'
        ];
        $mimeTypeMap = [
            'rss' => 'application/rdf+xml',
            'rss2' => 'application/rss+xml',
            'atom' => 'application/atom+xml'
        ];
        if (!isset($typeMap[$type])) {
            return false;
        }

        $templateMgr = TemplateManager::getManager($request);
        $context = $request->getContext();

        $collector = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$context->getId()])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->orderBy(Collector::ORDERBY_DATE_PUBLISHED);

        $recentItems = (int) $this->_parentPlugin->getSetting($context->getId(), 'recentItems');
        if ($recentItems > 0) {
            $collector->limit($recentItems);
        }
        $submissions = Repo::submission()->getMany($collector);
        $templateMgr->assign('submissions', $submissions->toArray());

        $versionDao = DAORegistry::getDAO('VersionDAO'); /** @var VersionDAO $versionDao */
        $version = $versionDao->getCurrentVersion();
        $templateMgr->assign('ompVersion', $version->getVersionString());

        $templateMgr->display($this->getTemplateResource($typeMap[$type]), $mimeTypeMap[$type]);

        return true;
    }
}
