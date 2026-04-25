<?php

namespace App\Actions\Catalog;

class ReplaceLandingPageShortcodesAction
{
    /**
     * @param  array<string, string>  $shortcodes
     */
    public function execute(string $content, array $shortcodes): string
    {
        if ($content === '') {
            return '';
        }

        return strtr($content, $shortcodes);
    }
}
