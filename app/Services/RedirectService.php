<?php

namespace App\Services;

use App\Facades\Curl;
use App\Facades\Reader;
use Illuminate\Support\Collection;

class RedirectService
{
    const URL_REGEX_PATTERN = '/(.*)(url=")([\w#*(){}%:\/.\?=&;-]+)(")(.*)/';
    protected ?string $domain = null;
    protected ?string $currentMatch = null;
    protected Collection $results;

    public function read(string $filename, ?string $domain = null): self
    {
        $this->domain = $domain;
        $this->results = collect();

        $reading = false;
        collect(Reader::getContentsAsArray($filename))
            ->each(function ($line) use (&$reading) {
                if (! $reading && str_contains($line, '<rule')) {
                    $reading = true;
                }
                if ($reading ) {
                    $this->getMatch($line);
                    $this->getRedirect($line);
                    if (str_contains($line, '</rule')) {
                        $reading = false;
                    }
                }
            });

        return $this;
    }

    public function render()
    {
        $this->results->each(function ($redirect, $key) {
            $reRedirect = '';
            if (str_starts_with($redirect, $this->domain)) {
                $reRedirect = Curl::getRedirect($redirect);
            }
            echo '"' . $key . '","' . $redirect . '","' . $reRedirect . '"<br>';
        });
    }

    protected function getMatch(string $line)
    {
        if (str_contains($line, '<match url=')) {
            $url = preg_replace(self::URL_REGEX_PATTERN, '$3', $line);
            if (! empty($url)  && ! str_contains($url, 'ignoreCase')) {
                $this->currentMatch = $url;
            } else {
                $this->currentMatch = null;
            }
        }
    }
    protected function getRedirect(string $line)
    {
        if (str_contains($line, '<action type="Redirect"')) {
            $url = preg_replace(self::URL_REGEX_PATTERN, '$3', $line);
            if (str_starts_with($url, '/')) {
                $url = $this->domain . $url;
            }
            $this->results->put($this->currentMatch, $url);
        }
    }
}
