<?php

declare(strict_types=1);

namespace Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers\Link;

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * UrlschemeViewHelper
 *
 * This ViewHelper generates a hyperlink for phone numbers by formatting them
 * according to a specific scheme (e.g., tel:). It validates the phone number
 * format and ensures it is properly formatted before creating the link.
 *
 * Example usage:
 * <gfv:link.urlscheme number="+49 (0) 7777 77 77 77" />
 *
 * @package Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers\Link
 * @version 13.0.4
 * @since 12.2.0
 * @author    Niels Tiedt <niels.tiedt@gedankenfolger.de>
 * @company   Gedankenfolger GmbH
 */
final class UrlschemeViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @var string The tag name used by this ViewHelper, typically 'a' for links.
     */
    protected $tagName = 'a';

    /**
     * Initialize arguments for the ViewHelper.
     *
     * Registers the necessary attributes and arguments used by this ViewHelper.
     * This method is called automatically by TYPO3 when the ViewHelper is initialized.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        // Register the 'number' argument as a required string input
        $this->registerArgument('number', 'string', 'Phone number to be formatted', true);
        // Register the 'scheme' argument as an optional string input with a default value of 'tel:'
        $this->registerArgument('scheme', 'string', 'URL scheme to prepend, e.g., tel:', false, 'tel:');
    }

    /**
     * Render the ViewHelper content.
     *
     * This method validates the provided phone number and formats it according to
     * the specified scheme. It then generates an HTML link with the formatted number.
     *
     * @return string The rendered HTML string with the formatted phone number link.
     * @throws Exception If the phone number format is invalid.
     */
    public function render(): string
    {
        // Retrieve the phone number from the arguments
        $number = $this->arguments['number'];

        // Define a regex pattern to validate international phone number formats
        $pattern = '/^\+(\d{1,3})\s?(\(0\))?\s?\d{1,4}[\s.-]?\d{1,4}[\s.-]?\d{1,4}[\s.-]?\d{1,4}$/';

        // Check if the phone number matches the expected format
        if (preg_match($pattern, $number)) {
            // Format the phone number by removing spaces, parentheses, and dashes
            $formattedNumber = preg_replace('/\s+|\(0\)|[\s.-]/', '', $number);
        } else {
            // Throw an exception if the phone number is in an invalid format
            throw new Exception('ERROR: Invalid format. Required format e.g. +49 (0) 7777 77 77 77', 1700485661);
        }

        // Add the formatted phone number as the href attribute with the specified scheme
        $this->tag->addAttribute('href', $this->arguments['scheme'] . $formattedNumber);
        // Set the content of the tag to the original (unformatted) phone number
        $this->tag->setContent($number);

        // Render and return the complete HTML tag
        return $this->tag->render();
    }
}
