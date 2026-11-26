<?php

namespace App\Http\Controllers;

use App\Content\SiteContent;
use Symfony\Component\HttpFoundation\Response;

final class SitemapController
{
    public function __invoke(SiteContent $content): Response
    {
        $origin = rtrim($content->site()['url'], '/');
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        foreach ($content->sitemapPaths() as $path) {
            $location = htmlspecialchars($origin.$path, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $xml .= "  <url><loc>{$location}</loc></url>\n";
        }

        return response($xml."</urlset>\n", 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
