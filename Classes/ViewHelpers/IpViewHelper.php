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
 *  Compare against a specific IP:
 *    <f:if condition="{gfv:ip()} == '200.200.200.200'">...</f:if>
 *
 * @package   Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers
 * @version   13.2.1
 * @since     13.0.0
 * @author    Niels Tiedt <niels.tiedt@gedankenfolger.de>
 * @company   Gedankenfolger GmbH
 */
final class IpViewHelper extends AbstractViewHelper
{
    public function render(): string
    {
        $candidates = [
            $_SERVER['HTTP_CLIENT_IP'] ?? '',
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? '',
        ];

        foreach ($candidates as $candidate) {
            // X-Forwarded-For may contain a comma-separated list; take the first entry
            $ip = trim(explode(',', $candidate)[0]);
            if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                return $ip;
            }
        }

        return '';
    }
}
