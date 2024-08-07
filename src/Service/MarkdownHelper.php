<?php

namespace App\Service;

use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Cache\CacheInterface;

class MarkdownHelper
{
    private $markdownParser;
    private $cache;
    private $isDebug;
    private $logger;
    private Security $security;

    public function __construct(MarkdownParserInterface $markdownParser,
                                CacheInterface          $cache,
                                bool                    $isDebug,
                                LoggerInterface         $mdLogger,
                                Security                $security)
    {
        $this->markdownParser = $markdownParser;
        $this->cache = $cache;
        $this->isDebug = $isDebug;
        $this->logger = $mdLogger;
        $this->security = $security;
    }

    public function parse(string $source): string
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')){
            $this->logger->info("Rendering markdown for {user}.",
            [
                'user' => $this->security->getUser()->getUserIdentifier(),
            ]);
        }
        if (stripos($source, 'cat') !== false) {
            $this->logger->info('Meow!');
        }

        if ($this->isDebug) {
            return $this->markdownParser->transformMarkdown($source);
        }

        return $this->cache->get('markdown_'.md5($source), function() use ($source) {
            return $this->markdownParser->transformMarkdown($source);
        });
    }
}
