<?php

namespace App\Content;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class ArticleHtml
{
    public const MAX_LENGTH = 250_000;

    private HtmlSanitizer $sanitizer;
    private HtmlSanitizer $editorSanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig())
            ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->withAttributeSanitizer(new ReviewedImageSource())
            ->withMaxInputLength(self::MAX_LENGTH);

        foreach ([
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'h2', 'h3',
            'ul', 'ol', 'li', 'blockquote', 'figure', 'figcaption',
            'table', 'caption', 'thead', 'tbody', 'tfoot', 'tr', 'colgroup',
        ] as $element) {
            $config = $config->allowElement($element, ['id']);
        }

        $config = $config
            ->allowElement('a', ['id', 'href', 'title'])
            ->allowElement('img', ['id', 'src', 'width', 'height', 'alt', 'loading'])
            ->allowElement('th', ['id', 'scope', 'headers', 'colspan', 'rowspan'])
            ->allowElement('td', ['id', 'headers', 'colspan', 'rowspan'])
            ->allowElement('col', ['span'])
            ->allowAttribute('start', ['ol'])
            ->allowAttribute('value', ['li'])
            ->allowAttribute('aria-label', ['a', 'figure', 'table'])
            ->allowAttribute('aria-describedby', ['img', 'table']);

        foreach (['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template'] as $element) {
            $config = $config->dropElement($element);
        }

        $this->sanitizer = new HtmlSanitizer($config);
        $this->editorSanitizer = new HtmlSanitizer($config->dropElement('img'));
    }

    public function sanitize(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }

    public function sanitizeEditor(string $html): string
    {
        return $this->editorSanitizer->sanitize($html);
    }
}
