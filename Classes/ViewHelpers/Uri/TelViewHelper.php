<?php

declare(strict_types=1);

namespace Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers\Uri;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Returns a tel: URI for a phone number without rendering an HTML tag.
 *
 * Normalizes the number to E.164 format and returns the resulting URI string.
 * Use this when you need the href value only, e.g. to pass it to another ViewHelper.
 *
 * Example usage:
 *   {gfv:uri.tel(number: '+49 (0) 7777 77 77 77')}
 *   <a href="{gfv:uri.tel(number: phoneNumber)}">Call</a>
 *
 * @author    Niels Tiedt <niels.tiedt@gedankenfolger.de>
 * @company   Gedankenfolger GmbH
 */
final class TelViewHelper extends AbstractViewHelper
{
    /**
     * Register all supported arguments.
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('number', 'string', 'Phone number in international format, e.g. +49 (0) 7777 77 77 77', true);
    }

    /**
     * Return the normalized tel: URI string.
     *
     * @return string Normalized tel: URI, e.g. tel:+49777777777
     * @throws Exception if the number is missing or invalid
     */
    public function render(): string
    {
        $number = trim((string)$this->arguments['number']);

        if (!str_starts_with($number, '+')) {
            throw new Exception('Uri\TelViewHelper: number must start with "+" and country code, e.g. +49 (0) 7777 77 77 77', 1750938101);
        }

        $normalized = '+' . (string)preg_replace('/\D+/', '', $number);

        if (!preg_match('/^\+\d{6,15}$/', $normalized)) {
            throw new Exception('Uri\TelViewHelper: invalid number format, e.g. +49 (0) 7777 77 77 77', 1750938102);
        }

        return 'tel:' . $normalized;
    }
}
