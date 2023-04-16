<?php

/**
 * @file classes/codelist/ONIXParserDOMHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ONIXParserDOMHandler
 *
 * @ingroup codelist
 *
 * @see XMLParser
 *
 * @brief This parser extracts a specific xs:simpleType based on a name attribute
 * representing a code list within it. It returns the xs:enumeration values
 * within it along with the xs:documentation elements which serve as textual
 * descriptions of the Codelist values.
 *
 * Example:  <xs:simpleType name="List30">...</xs:simpleType>
 */

namespace APP\codelist;

use PKP\xml\XMLNode;
use PKP\xml\XMLParserDOMHandler;

class ONIXParserDOMHandler extends XMLParserDOMHandler
{
    /** @var string the list being searched for */
    public $_listName = null;

    /** @var bool to maintain state */
    public $_foundRequestedList = false;

    /** @var array of items the parser eventually returns */
    public $_listItems = null;

    /** @var string to store the current character data  */
    public $_currentValue = null;

    /** @var bool currently inside an xs:documentation element */
    public $_insideDocumentation = false;

    /**
     * Constructor.
     *
     * @param string $listName
     */
    public function __construct($listName)
    {
        parent::__construct();
        $this->_listName = $listName;
        $this->_listItems = [];
    }

    /**
     * Callback function to act as the start element handler.
     *
     * @param XMLParser $parser
     * @param string $tag
     * @param array $attributes
     */
    public function startElement($parser, $tag, $attributes)
    {
        $this->currentData = null;

        switch ($tag) {
            case 'xs:simpleType':
                if ($attributes['name'] == $this->_listName) {
                    $this->_foundRequestedList = true;
                }
                break;
            case 'xs:enumeration':
                if ($this->_foundRequestedList) {
                    $this->_currentValue = $attributes['value'];
                    $this->_listItems[$this->_currentValue] = []; // initialize the array cell
                }
                break;
            case 'xs:documentation':
                if ($this->_foundRequestedList) {
                    $this->_insideDocumentation = true;
                }
                break;
        }

        $node = new XMLNode($tag);
        $node->setAttributes($attributes);
        if (isset($this->currentNode)) {
            $this->currentNode->addChild($node);
            $node->setParent($this->currentNode);
        } else {
            $this->rootNode = $node;
        }

        $this->currentNode = $node;
    }

    /**
     * Callback function to act as the character data handler.
     *
     * @param XMLParser $parser
     * @param string $data
     */
    public function characterData($parser, $data)
    {
        if ($this->_insideDocumentation) {
            if (count($this->_listItems[$this->_currentValue]) == 1) {
                $this->_listItems[$this->_currentValue][0] .= $data;
            } else {
                $this->_listItems[$this->_currentValue][0] = $data;
            }
        }
    }

    /**
     * Callback function to act as the end element handler.
     *
     * @param XMLParser $parser
     * @param string $tag
     */
    public function endElement($parser, $tag)
    {
        switch ($tag) {
            case 'xs:simpleType':
                $this->_foundRequestedList = false;
                break;
            case 'xs:documentation':
                $this->_insideDocumentation = false;
                break;
        }
    }

    /**
     * Returns the array of found list items
     *
     * @return array
     */
    public function getResult()
    {
        return [$this->_listName => $this->_listItems];
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\codelist\ONIXParserDOMHandler', '\ONIXParserDOMHandler');
}
