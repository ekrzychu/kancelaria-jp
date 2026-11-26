<?php

namespace App\Content;

use App\Models\Article;
use Dom\Element;
use Dom\HTMLDocument;
use Illuminate\Validation\ValidationException;

final class ArticleEditing
{
    public function __construct(private readonly ArticleHtml $html) {}

    public function parts(?Article $article): array
    {
        $document = $this->document($this->html->sanitize($article?->body_html ?? ''));

        // These structures are not losslessly round-tripped by the rich editor.
        $protected = 'figure, img, caption, colgroup, tfoot, [id], [scope], [headers], [aria-label], [aria-describedby]';
        $parts = [];
        $editable = '';
        foreach ($document->body->childNodes as $node) {
            $markup = $document->saveHtml($node);
            if ($node instanceof Element && ($node->matches($protected) || $node->querySelector($protected))) {
                if (trim($editable) !== '') {
                    $parts[] = ['html' => $editable, 'locked' => false];
                }
                $parts[] = ['html' => $markup, 'locked' => true];
                $editable = '';
            } else {
                $editable .= $markup;
            }
        }

        if (trim($editable) !== '' || $parts === [] || end($parts)['locked']) {
            $parts[] = ['html' => $editable, 'locked' => false];
        }

        return $parts;
    }

    public function saveBody(?Article $article, array $submitted): string
    {
        $body = '';
        $changed = $article === null;
        foreach ($this->parts($article) as $index => $part) {
            if ($part['locked']) {
                $body .= $part['html'];
                continue;
            }

            $value = $submitted['body_'.$index] ?? null;
            if ($value === null) {
                $value = '';
            }
            if (! is_string($value) || strlen($body) + strlen($value) > ArticleHtml::MAX_LENGTH) {
                throw ValidationException::withMessages(['data.body_'.$index => 'Treść artykułu jest za długa lub nieprawidłowa.']);
            }

            $clean = $this->html->sanitize($value);
            if ($this->editorMarkup($clean) === $this->editorMarkup($part['html'])) {
                $body .= $part['html'];
            } else {
                $body .= $clean;
                $changed = true;
            }
        }

        if (strlen($body) > ArticleHtml::MAX_LENGTH || trim(strip_tags($body)) === '') {
            throw ValidationException::withMessages(['data.body_0' => 'Wprowadź treść artykułu w dopuszczalnym rozmiarze.']);
        }

        return $changed ? $body : $article->body_html;
    }

    private function document(string $html): HTMLDocument
    {
        return HTMLDocument::createFromString(
            '<!doctype html><html><body>'.$html.'</body></html>',
            LIBXML_NOERROR,
            'UTF-8',
        );
    }

    private function editorMarkup(string $html): string
    {
        $document = $this->document($html);

        // Filament may emit a single paragraph list item as inline content.
        foreach ($document->querySelectorAll('li > p:only-child') as $paragraph) {
            if (! $paragraph->hasAttributes() && trim($paragraph->parentNode->textContent) === trim($paragraph->textContent)) {
                $this->removeIndentation($paragraph->parentNode);
                $paragraph->replaceWith(...iterator_to_array($paragraph->childNodes));
            }
        }
        foreach ($document->querySelectorAll('ol, ul, blockquote, table, thead, tbody, tfoot, tr') as $container) {
            $this->removeIndentation($container);
        }
        foreach ($document->querySelectorAll('strong, em') as $mark) {
            while (! $mark->hasAttributes() && ($next = $mark->nextSibling) instanceof Element
                && $next->localName === $mark->localName && ! $next->hasAttributes()) {
                $mark->append(...iterator_to_array($next->childNodes));
                $next->remove();
            }
        }
        foreach ($document->querySelectorAll('p, li, h2, h3') as $block) {
            $first = $block->firstChild;
            $last = $block->lastChild;
            if ($first instanceof \Dom\Text) {
                $first->data = ltrim($first->data);
            }
            if ($last instanceof \Dom\Text) {
                $last->data = rtrim($last->data);
            }
        }
        $this->removeIndentation($document->body);

        return implode('', array_map(
            fn ($node): string => $document->saveHtml($node),
            iterator_to_array($document->body->childNodes),
        ));
    }

    private function removeIndentation(Element $element): void
    {
        foreach (iterator_to_array($element->childNodes) as $node) {
            if ($node instanceof \Dom\Text && trim($node->data) === '') {
                $node->remove();
            }
        }
    }
}
