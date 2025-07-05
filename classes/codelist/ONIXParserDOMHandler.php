<?php

/**
 * @file classes/codelist/ONIXParserDOMHandler.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2000-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ONIXParserDOMHandler
 *
 * @see XMLParser
 *
 * @brief This parser extracts a specific CodeList based on a CodeListNumber.
 * It returns the CodeValue and CodeDescription elements within it, and
 * if the code value has been deprecated in ONIX (when applicable).
 *
 * Example:
 *         <CodeList>
 *             <CodeListNumber>28</CodeListNumber>
 *             <Code>
 *                 <CodeValue>01</CodeValue>
 *                 <CodeDescription>General / adult</CodeDescription>
 *                 <DeprecatedNumber/>
 *             </Code>
 *             [...]
 */

namespace APP\codelist;

use PKP\xml\PKPXMLParser;
use PKP\xml\XMLNode;
use PKP\xml\XMLParserDOMHandler;
use XMLParser;

class ONIXParserDOMHandler extends XMLParserDOMHandler
{
    /** @var string The list being searched for */
    public string $listName;

    public bool $foundRequestedList = false;

    /** @var ?array List of items the parser eventually returns */
    public ?array $listItems = [];

    /** @var ?string to store the current character data  */
    public ?string $currentValue = null;

    /** @var bool currently inside a CodeListNumber element */
    public bool $inCodeListNumber = false;

    /** @var bool currently inside a CodeValue element */
    public bool $inCodeValue = false;

    /** @var bool currently inside a CodeDescription element */
    public bool $inDescription = false;

    /** @var bool currently inside a DeprecatedNumber element */
    public bool $inDeprecated = false;

    /**
     * Constructor.
     */
    public function __construct(string $listName)
    {
        parent::__construct();
        $this->listName = $listName;
    }

    /**
     * Callback function to act as the start element handler.
     */
    public function startElement(PKPXMLParser|XMLParser $parser, string $tag, array $attributes): void
    {
        $this->currentData = null;

        switch ($tag) {
            case 'CodeListNumber':
                $this->inCodeListNumber = true;
                break;
            case 'CodeValue':
                if ($this->foundRequestedList) {
                    $this->inCodeValue = true;
                }
                break;
            case 'CodeDescription':
                if ($this->foundRequestedList) {
                    $this->inDescription = true;
                }
                break;
            case 'DeprecatedNumber':
                if ($this->foundRequestedList) {
                    $this->inDeprecated = true;
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
     */
    public function characterData(PKPXMLParser|XMLParser $parser, string $data): void
    {
        if ($this->inCodeListNumber && $this->listName = $data) {
            $this->foundRequestedList = true;
        }

        if ($this->inCodeValue) {
            $this->currentValue = $data;
            $this->listItems[$data] = []; // initialize the array cell
        }

        if ($this->inDescription) {
            if (count($this->listItems[$this->currentValue]) == 1) {
                $this->listItems[$this->currentValue][0] .= $data;
            } else {
                $this->listItems[$this->currentValue][0] = $data;
            }
        }

        if ($this->inDeprecated) {
            $this->listItems[$this->currentValue]['deprecated'] = 1;
        }
    }

    /**
     * Callback function to act as the end element handler.
     */
    public function endElement(PKPXMLParser|XMLParser $parser, string $tag): void
    {
        switch ($tag) {
            case 'CodeListNumber':
                $this->inCodeListNumber = false;
                break;
            case 'CodeValue':
                $this->inCodeValue = false;
                break;
            case 'CodeDescription':
                $this->inDescription = false;
                break;
            case 'DeprecatedNumber':
                $this->inDeprecated = false;
                break;
        }

        $this->currentNode->setValue($this->currentData);
        $this->currentNode = & $this->currentNode->getParent();
        $this->currentData = null;
    }

    /**
     * Returns the array of found list items
     */
    public function getResult(): mixed
    {
        return [$this->listName => $this->listItems];
    }
}
