<?php

declare(strict_types=1);

namespace Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to determine and output the client IP address.
 *
 * This ViewHelper checks in the following order:
 *   1. HTTP_CLIENT_IP (shared internet)
 *   2. HTTP_X_FORWARDED_FOR (proxy)
 *   3. REMOTE_ADDR (direct connection)
 * and returns the first found IP address as a string.
 *
 *  Example usage:
 *    <gfv:ip />
 *    {gfv:ip()}
 *
 * @package   Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers
 * @version   13.0.4
 * @since     13.0.0
 * @author    Niels Tiedt <niels.tiedt@gedankenfolger.de>
 * @company   Gedankenfolger GmbH
 */
final class IpViewHelper extends AbstractViewHelper
{
    /** @var bool Indicates that output is not escaped, since it is an IP address */
    protected $escapeOutput = false;

    /**
     * Returns the determined client IP address.
     *
     * @return string The IP address of the requesting client
     */
    public function render(): string
    {
        // If the user is behind a shared internet service
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        // If the user is behind a proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // Default: direct connection
        return $_SERVER['REMOTE_ADDR'];
    }
}
