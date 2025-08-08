<?php

declare(strict_types=1);

namespace Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers\Cloudflare;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Generates an <iframe> element to embed a Cloudflare Stream video.
 *
 * Constructs the iframe src URL by concatenating:
 *   - streamid (Cloudflare Stream video ID)
 *   - customerid (Cloudflare customer/account ID)
 *   - optional parameters: preload, loop, muted, autoplay
 *
 * Example usage:
 *   <gfv:streamIframe streamid="abc123" customerid="42" preload="auto" loop="true" muted="false" autoplay="true" />
 *
 * @package   Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers\Cloudflare
 * @version   13.0.0
 * @since     13.0.0
 * @author    Niels Tiedt <niels.tiedt@gedankenfolger.de>
 * @company   Gedankenfolger GmbH
 */
final class StreamIframeViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * HTML tag name
     * @var string
     */
    protected $tagName = 'iframe';

    /**
     * Register and define all supported arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument('streamid', 'string', 'Cloudflare Stream video ID', true);
        $this->registerArgument('customerid', 'string', 'Cloudflare customer ID', true);
        $this->registerArgument('preload', 'string', 'Video preload mode', false, 'none');
        $this->registerArgument('loop', 'bool', 'Loop video after end', false, false);
        $this->registerArgument('muted', 'bool', 'Start muted', false, false);
        $this->registerArgument('autoplay', 'bool', 'Autoplay video', false, null);
        $this->registerArgument('poster', 'int', 'Poster timestamp in seconds', false, null);
    }

    /**
     * Render and return the <iframe> element.
     *
     * @return string Rendered <iframe> HTML tag
     * @throws Exception if required arguments are missing or invalid
     */
    public function render(): string
    {
        $streamid   = $this->arguments['streamid'];
        $customerid = $this->arguments['customerid'];
        $preload    = $this->arguments['preload'];
        $loop       = $this->arguments['loop'];
        $muted      = $this->arguments['muted'];
        $autoplay   = $this->arguments['autoplay'];
        $poster     = $this->arguments['poster'];

        // Base URL using concatenation (no extra encoding needed)
        $baseUrl = 'https://customer-' . $customerid . '.cloudflarestream.com/' . $streamid . '/iframe';

        // Build query parameters
        $params = [];
        if ($preload)  { $params['preload']  = $preload; }
        if ($loop)     { $params['loop']     = 'true'; }
        if ($muted)    { $params['muted']    = 'true'; }
        if ($autoplay) { $params['autoplay'] = 'true'; }
        // poster handling commented until thumbnail support
        // if ($poster) { $params['poster'] = $poster; }

        // Add src attribute and render
        $this->tag->addAttribute('src', $baseUrl . '?' . http_build_query($params));

        // Force closing tag
        $this->tag->forceClosingTag(true);

        return $this->tag->render();
    }
}
