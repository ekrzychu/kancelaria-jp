<?php

namespace App\Content;

use App\Media\MediaReferences;
use App\Models\Article;
use App\Models\MediaImage;
use DOMDocument;
use DOMElement;

final class ArticleFigures
{
    public function __construct(private readonly ArticleHtml $html) {}

    public function positions(?Article $article): array
    {
        $options = [0 => 'Na początku artykułu'];
        if ($article !== null) {
            $document = new DOMDocument();
            $fragment = $this->html->sanitize($article->body_html);
            $document->loadHTML('<meta charset="utf-8"><body>'.$fragment.'</body>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            $body = $document->getElementsByTagName('body')->item(0);
            foreach ($this->elements($body) as $index => $block) {
                $number = $index + 1;
                $label = trim(preg_replace('/\s+/u', ' ', $block->textContent));
                $options[$number] = 'Po bloku '.$number.($label !== '' ? ' · '.mb_strimwidth($label, 0, 85, '…') : '');
            }
        }

        return $options + [MediaReferences::END => 'Na końcu artykułu'];
    }

    public function insert(DOMDocument $document, DOMElement $body, array $article, bool $private): bool
    {
        $blocks = $article['image_blocks'] ?? [];
        if ($blocks === []) {
            return false;
        }

        $media = MediaImage::where('purpose', 'article')->where('record_id', $article['id'])
            ->whereKey(array_column($blocks, 'image'))->get()->keyBy('id');
        $anchors = $this->elements($body);
        $inserted = false;
        foreach ($blocks as $block) {
            $image = $media->get($block['image']);
            if (! $image) {
                continue;
            }
            $figure = $document->createElement('figure');
            $element = $document->createElement('img');
            $element->setAttribute('src', MediaImage::url($image->id, $private));
            $element->setAttribute('alt', $block['alt']);
            $element->setAttribute('width', (string) $image->width);
            $element->setAttribute('height', (string) $image->height);
            $element->setAttribute('loading', 'lazy');
            $figure->appendChild($element);
            if (filled($block['caption'] ?? null)) {
                $caption = $document->createElement('figcaption');
                $caption->appendChild($document->createTextNode($block['caption']));
                $figure->appendChild($caption);
            }

            $body->insertBefore($figure, $anchors[$block['position']] ?? null);
            $inserted = true;
        }

        return $inserted;
    }

    private function elements(DOMElement $body): array
    {
        return array_values(array_filter(iterator_to_array($body->childNodes), fn ($node): bool => $node instanceof DOMElement));
    }
}
