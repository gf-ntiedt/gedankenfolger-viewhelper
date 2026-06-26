<?php

declare(strict_types=1);

namespace Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers\Link;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Renders an <a href="tel:..."> link for a phone number.
 *
 * Normalizes the number to E.164 format (e.g. +49777777777) and uses
 * the original formatted number as link text unless child content is given.
 *
 * Example usage:
 *   <gfv:link.tel number="+49 (0) 7777 77 77 77" />
 *   <gfv:link.tel number="+49 (0) 7777 77 77 77">Call us</gfv:link.tel>
 *   {gfv:link.tel(number: '+49 (0) 7777 77 77 77')}
 *
 * @author    Niels Tiedt <niels.tiedt@gedankenfolger.de>
 * @company   Gedankenfolger GmbH
 */
final class TelViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'a';

    /**
     * Register all supported arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('number', 'string', 'Phone number in international format, e.g. +49 (0) 7777 77 77 77', true);
    }

    /**
     * Render the <a href="tel:..."> tag.
     *
     * @return string Rendered HTML anchor tag
     * @throws Exception if the number is missing or invalid
     */
    public function render(): string
    {
        $number = trim((string)$this->arguments['number']);

        if (!str_starts_with($number, '+')) {
            throw new Exception('TelViewHelper: number must start with "+" and country code, e.g. +49 (0) 7777 77 77 77', 1750938001);
        }

        $normalized = '+' . preg_replace('/\D+/', '', $number);

        if (!preg_match('/^\+\d{6,15}$/', $normalized)) {
            throw new Exception('TelViewHelper: invalid number format, e.g. +49 (0) 7777 77 77 77', 1750938002);
        }

        $this->tag->addAttribute('href', 'tel:' . $normalized);

        $content = $this->renderChildren();
        $this->tag->setContent($content !== null && $content !== '' ? (string)$content : $number);

        return $this->tag->render();
    }
}
