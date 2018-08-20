<?php declare(strict_types=1);

namespace Hsdt\EmbedFetcher\Provider\Exception;

use Throwable;

class ProviderException extends \Exception
{
    /**
     * @var string
     */
    private $provider;

    public function __construct(string $provider, Throwable $previous = null)
    {
        parent::__construct('', 0, $previous);
        $this->provider = $provider;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }
}
