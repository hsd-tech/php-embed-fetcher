<?php declare(strict_types=1);

namespace Hsdt\EmbedFetcher;

interface ProviderInterface
{
    public function fetch(string $url): Data;
}
