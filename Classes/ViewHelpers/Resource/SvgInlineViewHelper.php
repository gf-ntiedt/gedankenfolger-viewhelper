<?php

declare(strict_types=1);

namespace Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers\Resource;

use Closure;
use Throwable;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as ViewHelperException;

/**
 * Renders an SVG file inline by embedding its <svg> XML in the HTML output.
 *
 * It resolves the SVG via FAL (File/FileReference) or a given `src` (e.g. EXT:.../icon.svg),
 * parses it with DOMDocument (network disabled), sanitizes high-risk constructs and injects
 * allowlisted attributes onto the root <svg> element.
 *
 * Security notice:
 * Inline SVG is XSS-sensitive. This ViewHelper applies a conservative, icon-focused sanitization.
 * For untrusted SVG sources (e.g. editor uploads), a dedicated sanitizer on file-level is recommended.
 *
 * @package   Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers\Resource
 * @version   13.1.0
 * @since     13.0.0
 * @author    Niels Tiedt <niels.tiedt@gedankenfolger.de>
 * @company   Gedankenfolger GmbH
 */
class SvgInlineViewHelper extends AbstractViewHelper
{
    /**
     * SVG output must not be HTML-escaped by Fluid, otherwise the XML will be broken.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Per-request (runtime) cache.
     *
     * @var array<string,string>
     */
    private static array $runtimeCache = [];

    /**
     * Registers ViewHelper arguments.
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument('src', 'string', 'e.g. EXT:sitepackage/Resources/Public/Images/any.svg', false, '');
        $this->registerArgument('image', 'object', 'A FAL object (File or FileReference)');
        $this->registerArgument('treatIdAsReference', 'bool', 'Given src argument is a sys_file_reference record', false, false);

        $this->registerArgument('id', 'string', 'Id to set on the root <svg>');
        $this->registerArgument('class', 'string', 'CSS class(es) for the root <svg>');
        $this->registerArgument('width', 'string', 'Width of the svg');
        $this->registerArgument('height', 'string', 'Height of the svg');
        $this->registerArgument('viewBox', 'string', 'viewBox attribute for the svg');

        $this->registerArgument('data', 'array', 'Array of data-attributes (key => value)');
        $this->registerArgument('additionalAttributes', 'array', 'Additional attributes for the root <svg>', false, []);
    }

    /**
     * Renders the inline SVG.
     *
     * @param array<string,mixed> $arguments
     * @param Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string Inline SVG (root <svg> element) or empty string on parse failure
     * @throws ViewHelperException When arguments are invalid or file is not a non-empty SVG
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $image = self::getImage($arguments);
        $svgContent = $image->getContents();

        if ($svgContent === '') {
            throw new ViewHelperException('The svg file must not be empty.', 1678366388);
        }

        /** @var array<string,mixed> $attributes */
        $attributes = [
            'id' => $arguments['id'] ?? null,
            'class' => $arguments['class'] ?? null,
            'width' => $arguments['width'] ?? null,
            'height' => $arguments['height'] ?? null,
            'viewBox' => $arguments['viewBox'] ?? null,
            'data' => $arguments['data'] ?? null,
        ] + (($arguments['additionalAttributes'] ?? []) ?: []);

        $cacheKey = self::buildRuntimeCacheKey($image, $attributes);
        if (isset(self::$runtimeCache[$cacheKey])) {
            return self::$runtimeCache[$cacheKey];
        }

        $result = self::getInlineSvg($svgContent, $attributes);
        self::$runtimeCache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Resolves the SVG file via Extbase ImageService.
     *
     * @param array<string,mixed> $arguments
     * @return File|FileReference
     * @throws ViewHelperException
     */
    protected static function getImage(array $arguments): File|FileReference
    {
        $src = (string)($arguments['src'] ?? '');
        $imageArg = $arguments['image'] ?? null;

        if ($src === '' && $imageArg === null) {
            throw new ViewHelperException('You must either specify a string src or a File object.', 1678366368);
        }

        try {
            $imageService = GeneralUtility::makeInstance(ImageService::class);
            $image = $imageService->getImage(
                $src,
                $imageArg,
                (bool)($arguments['treatIdAsReference'] ?? false)
            );
        } catch (Throwable $exception) {
            throw new ViewHelperException('Could not convert given arguments to image object.', 1678367678);
        }

        if (strtolower((string)$image->getExtension()) !== 'svg') {
            throw new ViewHelperException('You must provide an svg file.', 1678366371);
        }

        return $image;
    }

    /**
     * Parses SVG XML, sanitizes it and injects safe root attributes.
     *
     * Notes:
     *  - Uses LIBXML_NONET to prevent network access during parsing.
     *  - Does not pre-escape attribute values: DOMDocument::saveXML() escapes them when serializing.
     *
     * @param string $svgContent Raw SVG XML
     * @param array<string,mixed> $attributes
     * @return string
     */
    private static function getInlineSvg(string $svgContent, array $attributes = []): string
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML(
            $svgContent,
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded || !$dom->documentElement instanceof \DOMElement) {
            return '';
        }

        $root = $dom->documentElement;
        if (strtolower((string)$root->localName) !== 'svg') {
            return '';
        }

        self::sanitizeSvgDom($dom);

        foreach (self::filterAndNormalizeAttributes($attributes) as $name => $value) {
            $root->setAttribute($name, $value);
        }

        return (string)$dom->saveXML($root);
    }

    /**
     * Conservative sanitization for inline usage.
     *
     * Removes:
     *  - doctype
     *  - script / foreignObject / iframe / object / embed
     *  - image / feImage (prevents external loads)
     *  - event-handler attributes (on*)
     *  - style attributes
     *  - href/xlink:href unless fragment-only (#...)
     *  - values containing url(http/https/data/javascript:...) (conservative)
     *
     * @param \DOMDocument $dom
     * @return void
     */
    private static function sanitizeSvgDom(\DOMDocument $dom): void
    {
        if ($dom->doctype !== null) {
            $dom->removeChild($dom->doctype);
        }

        $xpath = new \DOMXPath($dom);

        $dangerous = $xpath->query(
            '//*[local-name()="script" or local-name()="foreignObject" or local-name()="iframe" or local-name()="object" or local-name()="embed"]'
        );
        if ($dangerous !== false) {
            for ($i = $dangerous->length - 1; $i >= 0; $i--) {
                $node = $dangerous->item($i);
                if ($node?->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        $external = $xpath->query('//*[local-name()="image" or local-name()="feImage"]');
        if ($external !== false) {
            for ($i = $external->length - 1; $i >= 0; $i--) {
                $node = $external->item($i);
                if ($node?->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        $all = $xpath->query('//*');
        if ($all === false) {
            return;
        }

        foreach ($all as $node) {
            if (!$node instanceof \DOMElement || !$node->hasAttributes()) {
                continue;
            }

            $toRemove = [];
            foreach ($node->attributes as $attr) {
                $name = (string)$attr->nodeName;
                $lname = strtolower($name);
                $value = (string)$attr->nodeValue;

                if (str_starts_with($lname, 'on')) {
                    $toRemove[] = $name;
                    continue;
                }

                if ($lname === 'style') {
                    $toRemove[] = $name;
                    continue;
                }

                if ($lname === 'href' || $lname === 'xlink:href') {
                    $trim = ltrim($value);
                    if ($trim === '' || $trim[0] !== '#') {
                        $toRemove[] = $name;
                    }
                    continue;
                }

                if (preg_match('/url\(\s*[\'"]?(?:https?:|data:|javascript:)/i', $value) === 1) {
                    $toRemove[] = $name;
                    continue;
                }
            }

            foreach ($toRemove as $attrName) {
                $node->removeAttribute($attrName);
            }
        }
    }

    /**
     * Normalizes passed attributes and allowlists attribute names for the root <svg>.
     *
     * - Converts `data` array into `data-*` attributes
     * - Drops disallowed attribute names (prevents onload=..., xmlns*, namespaced injection, etc.)
     *
     * @param array<string,mixed> $attributes
     * @return array<string,string>
     */
    private static function filterAndNormalizeAttributes(array $attributes): array
    {
        $out = [];

        $data = $attributes['data'] ?? null;
        unset($attributes['data']);

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if ($v === null) {
                    continue;
                }
                $key = 'data-' . trim((string)$k);
                if ($key !== 'data-' && self::isAllowedAttributeName($key)) {
                    $out[$key] = (string)$v;
                }
            }
        }

        foreach ($attributes as $k => $v) {
            if ($v === null) {
                continue;
            }
            $key = trim((string)$k);
            if ($key === '' || !self::isAllowedAttributeName($key)) {
                continue;
            }
            $out[$key] = trim((string)$v);
        }

        return $out;
    }

    /**
     * Allowlist for root <svg> attributes supplied via arguments/additionalAttributes.
     *
     * @param string $name
     * @return bool
     */
    private static function isAllowedAttributeName(string $name): bool
    {
        $n = strtolower(trim($name));
        if ($n === '') {
            return false;
        }

        if (str_starts_with($n, 'on') || str_starts_with($n, 'xmlns') || str_contains($n, ':')) {
            return false;
        }

        if (in_array($name, ['id', 'class', 'width', 'height', 'viewBox'], true)) {
            return true;
        }

        if (str_starts_with($n, 'data-') || str_starts_with($n, 'aria-')) {
            return true;
        }

        return in_array($n, ['role', 'tabindex', 'focusable'], true);
    }

    /**
     * Builds a per-request cache key from file identity/mtime and normalized attributes.
     *
     * @param File|FileReference $file
     * @param array<string,mixed> $attributes
     * @return string
     */
    private static function buildRuntimeCacheKey(File|FileReference $file, array $attributes): string
    {
        $identifier = method_exists($file, 'getIdentifier') ? (string)$file->getIdentifier() : '';
        $mtime = method_exists($file, 'getModificationTime') ? (string)$file->getModificationTime() : '';

        $normalized = self::filterAndNormalizeAttributes($attributes);
        ksort($normalized);

        return sha1($identifier . '|' . $mtime . '|' . serialize($normalized));
    }
}
