<?php declare(strict_types=1);

namespace Hsdt\SocialMedia\Provider;

use GuzzleHttp\Client;

abstract class AbstractProvider
{
    /**
     * @var array
     */
    protected $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function getClient(): Client
    {
        return $this->options['http_client'];
    }
}
