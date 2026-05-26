<?php

declare(strict_types=1);

namespace Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers\Resource;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\Security\SvgSanitizer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as ViewHelperException;

/**
 * Renders an SVG file inline by embedding its <svg> XML in the HTML output.
 *
 * Resolves the SVG via FAL (File/FileReference) or a given `src` (e.g. EXT:.../icon.svg),
 * delegates sanitization to the TYPO3 Core SvgSanitizer, then injects allowlisted attributes
 * onto the root <svg> element.
 *
 * @version   13.2.5
 * @since     13.0.0
 * @author    Niels Tiedt <niels.tiedt@gedankenfolger.de>
 * @company   Gedankenfolger GmbH
 */
final class SvgInlineViewHelper extends AbstractViewHelper
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
     * @return string Inline SVG (root <svg> element) or empty string on parse failure
     * @throws ViewHelperException When arguments are invalid or file is not a non-empty SVG
     */
    public function render(): string
    {
        $image = self::getImage($this->arguments);
        $svgContent = $image->getContents();

        if ($svgContent === '') {
            throw new ViewHelperException('The svg file must not be empty.', 1678366388);
        }

        /** @var array<string,mixed> $attributes */
        $attributes = [
            'id' => $this->arguments['id'] ?? null,
            'class' => $this->arguments['class'] ?? null,
            'width' => $this->arguments['width'] ?? null,
            'height' => $this->arguments['height'] ?? null,
            'viewBox' => $this->arguments['viewBox'] ?? null,
            'data' => $this->arguments['data'] ?? null,
        ] + (($this->arguments['additionalAttributes'] ?? []) ?: []);

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
     * @return FileInterface
     * @throws ViewHelperException
     */
    private static function getImage(array $arguments): FileInterface
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
        } catch (\Throwable $exception) {
            throw new ViewHelperException('SvgInlineViewHelper: could not resolve image from given arguments.', 1678367678, $exception);
        }

        if (strtolower((string)$image->getExtension()) !== 'svg') {
            throw new ViewHelperException('You must provide an svg file.', 1678366371);
        }

        return $image;
    }

    /**
     * Sanitizes, parses and injects safe root attributes into the SVG.
     *
     * Sanitization is delegated to the TYPO3 Core SvgSanitizer which uses
     * enshrined/svg-sanitize under the hood.
     *
     * @param string $svgContent Raw SVG XML
     * @param array<string,mixed> $attributes
     * @return string
     */
    private static function getInlineSvg(string $svgContent, array $attributes = []): string
    {
        $svgContent = GeneralUtility::makeInstance(SvgSanitizer::class)->sanitizeContent($svgContent);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($svgContent, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded || !$dom->documentElement instanceof \DOMElement) {
            return '';
        }

        $root = $dom->documentElement;
        if (strtolower((string)$root->localName) !== 'svg') {
            return '';
        }

        foreach (self::filterAndNormalizeAttributes($attributes) as $name => $value) {
            $root->setAttribute($name, $value);
        }

        return (string)$dom->saveXML($root);
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
     * @param FileInterface $file
     * @param array<string,mixed> $attributes
     * @return string
     */
    private static function buildRuntimeCacheKey(FileInterface $file, array $attributes): string
    {
        $identifier = (string)$file->getIdentifier();
        $mtime = (string)$file->getModificationTime();

        $normalized = self::filterAndNormalizeAttributes($attributes);
        ksort($normalized);

        return sha1($identifier . '|' . $mtime . '|' . serialize($normalized));
    }
}
