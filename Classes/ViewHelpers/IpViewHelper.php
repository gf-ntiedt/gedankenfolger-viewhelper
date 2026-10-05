<?php

declare(strict_types=1);

namespace Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to determine and output the client IP address.
 *
 * By default (secureMode="false") this ViewHelper reads client-controlled
 * HTTP headers directly, in the following order:
 *   1. HTTP_CLIENT_IP (shared internet)
 *   2. HTTP_X_FORWARDED_FOR (proxy)
 *   3. REMOTE_ADDR (direct connection)
 * and returns the first found valid IP address as a string.
 *
 * WARNING: In default mode, the returned value is NOT verified against
 * TYPO3's reverse-proxy trust configuration. Any visitor can send an
 * arbitrary value for these headers — never use the default mode's output
 * for access control, authentication, audit logging, or any other secret or
 * security-relevant decision.
 *
 *  Example usage:
 *    <gfv:ip />
 *    {gfv:ip()}
 *
 *  Compare against a specific IP:
 *    <f:if condition="{gfv:ip()} == '200.200.200.200'">...</f:if>
 *
 * secureMode="true" instead resolves the address via TYPO3's
 * NormalizedParams, which only trusts proxy headers when the request
 * actually comes from a proxy listed in
 * $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP'] (with
 * SYS/reverseProxyHeaderMultiValue also set to 'first' or 'last'):
 *
 *    {gfv:ip(secureMode: true)}
 *
 * secureMode="true" is only trustworthy once reverseProxyIP and
 * reverseProxyHeaderMultiValue are correctly configured for the environment
 * this extension runs in (e.g. the real IP ranges of the reverse
 * proxy/CDN in front of the site). Without that configuration,
 * secureMode="true" silently returns REMOTE_ADDR — which, behind a reverse
 * proxy, is the proxy's own address rather than the visitor's — with no
 * error or warning.
 *
 * @author    Niels Tiedt <niels.tiedt@gedankenfolger.de>
 * @company   Gedankenfolger GmbH
 */
final class IpViewHelper extends AbstractViewHelper
{
    /**
     * Registers the optional secureMode argument.
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'secureMode',
            'bool',
            'If true, resolve via TYPO3\'s reverse-proxy trust (NormalizedParams) instead of reading client-controlled headers directly. Only trustworthy once SYS/reverseProxyIP and SYS/reverseProxyHeaderMultiValue are correctly configured for this environment - otherwise silently returns REMOTE_ADDR instead of the visitor\'s address.',
            false,
            false
        );
    }

    /**
     * @return string Client IP address, or an empty string if it cannot be determined
     */
    public function render(): string
    {
        if ($this->arguments['secureMode']) {
            $normalizedParams = ($GLOBALS['TYPO3_REQUEST'] ?? null)?->getAttribute('normalizedParams');

            return $normalizedParams?->getRemoteAddress() ?? '';
        }

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
