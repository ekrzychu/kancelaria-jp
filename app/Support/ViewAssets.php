<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

final class ViewAssets
{
    public static function searchIcon(): HtmlString
    {
        $svg = file_get_contents(public_path('assets/icons/search.svg'));

        return new HtmlString(preg_replace('/<svg\b/', '<svg class="utility-icon" aria-hidden="true" focusable="false"', $svg, 1));
    }
}
