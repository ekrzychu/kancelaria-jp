<?php

namespace App\Content;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

final class ReviewedImageSource implements AttributeSanitizerInterface
{
    public function getSupportedElements(): array
    {
        return ['img'];
    }

    public function getSupportedAttributes(): array
    {
        return ['src'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        return preg_match('#\A/assets/img/[\w.-]+\.(?:avif|jpe?g|png|webp)\z#i', $value)
            && is_file(public_path(ltrim($value, '/'))) ? $value : null;
    }
}
