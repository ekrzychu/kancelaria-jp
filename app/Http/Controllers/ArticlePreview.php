<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class ArticlePreview
{
    public function __invoke(Article $article, PublicPages $pages): Response
    {
        Gate::authorize('view', $article);

        $response = $pages->renderArticle($article, preview: true);
        if (request()->boolean('frame')) {
            $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'");
        }

        return $response;
    }
}
