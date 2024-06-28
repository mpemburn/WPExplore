<?php

namespace App\Services;

class ExtractLinkIntoShortcode
{
    private static $instance = null;

    protected ?string $url = null;
    protected ?string $html = null;
    protected array $atts = [];

    private function __construct()
    {
    }

    public static function boot()
    {
        if (!self::$instance) {
            self::$instance = new static;
        }

        return self::$instance;
    }


    public static function extract(string $link): ?string
    {
        $self = self::boot();

        return $self->createShortcode($link);
    }

    protected function createShortcode(string $link): ?string
    {
        $link = trim($link);
        $this->url = $link;
        $this->html = $this->extractHtml($link);
        if ($this->html) {
            $this->atts[] = 'label="' . $this->html . '"';
            $this->url = $this->extractUrl($link);
        }

        $this->parseUrl();

        $shortcode = '[acalog ' . implode(' ', $this->atts) . ']';

        return $shortcode;
    }

    protected function extractUrl(string $link): ?string
    {
        $urlFound = preg_match('/<a\s+(?:[^>]*?\s+)?href="([^"]+\?[^"]+)"/', $link, $match);
        if ($urlFound) {
            $rawUrl = array_pop($match);

            return urldecode(html_entity_decode($rawUrl));
        }

        return null;
    }

    protected function extractHtml(string $link): ?string
    {
        $htmlFound = preg_match('/<a[^>]*>(.*?)<\/a>/', $link, $htmlMatch);
        if ($htmlFound) {
            return array_pop($htmlMatch);
        }

        return null;
    }

    protected function parseUrl(): void
    {
        $parsed = parse_url($this->url);
        $path = str_replace('/', '', $parsed['path']);
        // Make sure that this is not the default path before redefining it
        if (! empty($path) && $path !== 'preview_program.php') {
            $this->atts[] = 'path="' . $path . '"';
        }

        parse_str($parsed['query'], $queryParts);

        foreach ($queryParts as $key => $value) {
            $key = $key === 'cur_cat_oid' ? 'catoid' : $key;
            if (! is_array($value)) {
                $this->atts[] = $key . '="' . $value . '"';
            } else {
                $this->unpackArray($key, $value);
            }
        }
    }

    protected function unpackArray(string $key, array $values): void
    {
        $subKeys = [];
        foreach ($values as $subKey => $value) {
            $subKeys[] = $subKey . '=' . $value;
        }

        $this->atts[] = $key . '="' . implode('|', $subKeys) . '"';
    }
}
