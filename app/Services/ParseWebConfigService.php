<?php

namespace App\Services;

use Saloon\XmlWrangler\XmlReader;

class ParseWebConfigService
{
    protected ?string $xmlContent = null;

    public function run(string $filepath): self
    {
        // Read the web.config file and remove comments
        if (file_exists($filepath)) {
            $contents = file_get_contents($filepath);
            $this->xmlContent = $this->removeComments($contents);
        }
//        exec("cat web.config | sed '/<!--.*-->/d' | sed '/<!--/,/-->/d' > webconfig.xml");

        return $this;
    }

    public function parse(): void
    {
        if (! $this->xmlContent) {
            return;
        }

        $reader = XmlReader::fromString($this->xmlContent);

        try {
            $rules = $reader->element('rules')->lazy();
            $iterator = $rules->current()->getContent();

            foreach ($iterator as $content) {
                $rules = $content->getContent();
                if (is_array($rules)) {
                    foreach ($rules as $rule) {
                        $name = $rule->getAttribute('name');
                        $match = $rule->getContent()['match']->getAttribute('url');
                        $type = $rule->getContent()['action']->getAttribute('type');
                        if ($type !== 'Redirect') {
                            continue;
                        }
                        $action = $rule->getContent()['action']->getAttribute('url');
                        $this->output($name, $match, $action);
                    }
                }
            }
        } catch (\Throwable $e) {
            echo 'Parsing failed ' . $e->getMessage();
        }
    }

    public function output(string $name, string $match, string $action): void
    {
        echo "# " . $name . "\n";
        if ($this->findRegex($match)) {
            echo "location ~ ^" . $match . "$ {\n";
            echo "    proxy_pass \"http://localhost/" . $action . "$1\";\n";
        } else {
            echo "location " . $match . " {\n";
            echo "    proxy_pass \"http://localhost/" . $action . "\";\n";
        }
        echo "    proxy_set_header Host \$http_host;\n";
        echo "}\n\n";
    }

    protected function removeComments(string $content): string
    {
        return preg_replace('/<!--(.|\s)*?-->/', '', $content);
    }

    protected function findRegex(string $str): string
    {
        $regex = ['(.*)', '[0-9]', '*'];
        return (bool)array_intersect($regex, [$str]);
    }
}
