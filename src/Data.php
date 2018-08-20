<?php declare(strict_types=1);

namespace Hsdt\EmbedFetcher;

class Data
{
    /**
     * @var string
     */
    private $provider;

    /**
     * @var array|null
     */
    private $data;

    public function __construct(string $provider, ?array $data)
    {
        $this->provider = $provider;
        $this->data = $data;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getData(): ?array
    {
        return $this->data;
    }
}
