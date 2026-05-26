<?php

declare(strict_types=1);

namespace Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers\Link;

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
 * Example numbers:
 * +49 2817 333-278, +49 2817 333-459, +49 2817 333-661, +49 2817 333-297, +49 2817 333-8090, +49 2817 333-250, +49 2817 333-233, +49 2817 333-210, +49 2817 333-262, +49 2817 333-610, +49 (0) 7777 77 77 77, +1 973 914 1306, +1 862 200 8073, +1 862 345 0739, +86 158 2141 5697, +33 6 32 85 82 98, +91 9146003489, +65 9746 0800
 *
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

        $this->registerArgument('number', 'string', 'Phone number to be formatted', true);
        // Allowed schemes: tel:, mailto:, sms:, callto:
        $this->registerArgument('scheme', 'string', 'URL scheme to prepend. Allowed: tel:, mailto:, sms:, callto:', false, 'tel:');
    }

    /**
     * Render the ViewHelper content.
     *
     * This method validates the provided phone number and formats it according to
     * the specified scheme. It then generates an HTML link with the formatted number.
     *
     * @return string The rendered HTML string with the formatted phone number link.
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception If the scheme is not allowed or the number format is invalid.
     */
    public function render(): string
    {
        $number = trim((string)$this->arguments['number']);
        $scheme = (string)$this->arguments['scheme'];

        $allowedSchemes = ['tel:', 'mailto:', 'sms:', 'callto:'];
        if (!in_array($scheme, $allowedSchemes, true)) {
            throw new Exception('UrlschemeViewHelper: invalid scheme "' . $scheme . '". Allowed: ' . implode(', ', $allowedSchemes), 1748953200);
        }

        // Require international format starting with '+'
        if (!str_starts_with($number, '+')) {
            throw new Exception('UrlschemeViewHelper: number must start with "+" and country code, e.g. +49 (0) 7777 77 77 77', 1700485661);
        }

        // Normalize: keep leading '+' and all digits only
        $formattedNumber = '+' . preg_replace('/\D+/', '', $number);

        // Validate resulting normalized number (6-15 digits after '+')
        if (!preg_match('/^\+\d{6,15}$/', $formattedNumber)) {
            throw new Exception('UrlschemeViewHelper: invalid number format, e.g. +49 (0) 7777 77 77 77', 1700485662);
        }

        $this->tag->addAttribute('href', $scheme . $formattedNumber);
        // Set the content of the tag to the original (unformatted) phone number
        $this->tag->setContent($number);

        // Render and return the complete HTML tag
        return $this->tag->render();
    }
}
