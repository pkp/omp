<?php

/**
 * @file classes/core/Request.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Request
 *
 * @ingroup core
 *
 * @brief Class providing operations associated with HTTP requests.
 * Requests are assumed to be in the format http://host.tld/index.php/<press_id>/<page_name>/<operation_name>/<arguments...>
 * <press_id> is assumed to be "index" for top-level site requests.
 */

namespace APP\core;

use APP\press\Press;
use PKP\core\PKPRequest;

class Request extends PKPRequest
{
    /**
     * @see PKPPageRouter::getContext()
     */
    public function getPress(): ?Press
    {
        return $this->getContext();
    }

    /**
     * Deprecated
     *
     * @see PKPPageRouter::getContext()
     */
    public function getContext(): ?Press
    {
        return parent::getContext();
    }

    /**
     * Deprecated
     *
     * @see PKPPageRouter::url()
     *
     * @param null|mixed $pressPath
     * @param null|mixed $page
     * @param null|mixed $op
     * @param null|mixed $path
     * @param null|mixed $params
     * @param null|mixed $anchor
     */
    public function url(
        $pressPath = null,
        $page = null,
        $op = null,
        $path = null,
        $params = null,
        $anchor = null,
        $escape = false
    ) {
        return $this->_delegateToRouter(
            'url',
            $pressPath,
            $page,
            $op,
            $path,
            $params,
            $anchor,
            $escape
        );
    }
}
