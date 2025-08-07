<?php

declare(strict_types=1);

namespace Gedankenfolger\GedankenfolgerSitepackage\ViewHelpers\Resource;

use Closure;
use DOMDocument;
use In2code\In2template\Exception\FileException;
use SimpleXMLElement;
use Throwable;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Renders an SVG file inline by embedding its XML content directly into the output.
 *
 * It handles loading the file via FAL (File or FileReference) or by path, validates
 * that the file is non-empty and an SVG, then parses and injects attributes safely.
 *
 * Example usage:
 *  <gfv:resource.svgInline src="EXT:Sitepackage/Resources/Public/Logo.svg" width="200" />
 *  <gfv:resource.svgInline image="{fileReference}" class="icon" id="logo" viewBox="0 0 100 100" />
 *  - Pass additional data-attributes or arbitrary attributes via `data` and `additionalAttributes` arguments.
 *
 * @package   Gedankenfolger\GedankenfolgerViewhelper\ViewHelpers\Resource
 * @version   13.0.0
 * @since     13.0.0
 * @author    Niels Tiedt <niels.tiedt@gedankenfolger.de>
 * @company   Gedankenfolger GmbH
 */
class SvgInlineViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('src', 'string', 'e.g. EXT:gedankenfolger_sitepackage/Resources/Public/Images/any.svg', false, '');
        $this->registerArgument('image', 'object', 'a FAL object (File or FileReference)');
        $this->registerArgument('treatIdAsReference', 'bool', 'given src argument is a sys_file_reference record', false, false);
        $this->registerArgument('id', 'string', 'Id to set in the svg');
        $this->registerArgument('class', 'string', 'Css class(es) for the svg');
        $this->registerArgument('width', 'string', 'Width of the svg.');
        $this->registerArgument('height', 'string', 'Height of the svg.');
        $this->registerArgument('viewBox', 'string', 'Specifies the view box for the svg');
        $this->registerArgument('data', 'array', 'Array of data-attributes');
        $this->registerArgument('additionalAttributes', 'array', 'any attributes', false, []);
    }

    /**
     * @param array $arguments
     * @param Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     * @SuppressWarnings(PHPMD)
     * @throws FileException
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $image = self::getImage($arguments);
        $svgContent = $image->getContents();
        if ($svgContent === '') {
            throw new FileException('The svg file must not be empty.', 1678366388);
        }
        $attributes = [
                'id' => $arguments['id'],
                'class' => $arguments['class'],
                'width' => $arguments['width'],
                'height' => $arguments['height'],
                'viewBox' => $arguments['viewBox'],
                'data' => $arguments['data'],
            ] + $arguments['additionalAttributes'];
        return self::getInlineSvg($svgContent, $attributes);
    }

    /**
     * @param array $arguments
     * @return File|FileReference
     * @throws FileException
     */
    protected static function getImage(array $arguments): File|FileReference
    {
        if ($arguments['src'] === '' && $arguments['image'] === null) {
            throw new FileException('You must either specify a string src or a File object.', 1678366368);
        }
        try {
            $imageService = GeneralUtility::makeInstance(ImageService::class);
            $image = $imageService->getImage(
                $arguments['src'],
                $arguments['image'],
                (bool)$arguments['treatIdAsReference']
            );
        } catch (Throwable $exception) {
            throw new FileException('Could not convert given arguments to image object', 1678367678);
        }
        if ($image->getExtension() !== 'svg') {
            throw new FileException('You must provide a svg file.', 1678366371);
        }
        return $image;
    }

    protected static function getInlineSvg(string $svgContent, array $attributes = []): string
    {
        $svgElement = simplexml_load_string($svgContent);
        if ($svgElement instanceof SimpleXMLElement === false) {
            return '';
        }
        $domXml = dom_import_simplexml($svgElement);
        if ($domXml->ownerDocument instanceof DOMDocument === false) {
            return '';
        }
        foreach (self::updateAttributes($attributes) as $attributeKey => $attributeValue) {
            if ($attributeValue !== null) {
                $domXml->setAttribute($attributeKey, htmlspecialchars((string)$attributeValue));
            }
        }
        return (string)$domXml->ownerDocument->saveXML($domXml->ownerDocument->documentElement);
    }

    protected static function updateAttributes(array $attributes): array
    {
        if ($attributes['id'] !== null) {
            $attributes['id'] = htmlspecialchars(trim((string)$attributes['id']));
        }

        if (is_array($attributes['data'])) {
            foreach ($attributes['data'] as $attributeDataKey => $attributeDataValue) {
                $attributes['data-' . (string)$attributeDataKey] = htmlspecialchars((string)$attributeDataValue);
            }
            unset($attributes['data']);
        }

        return $attributes;
    }
}
