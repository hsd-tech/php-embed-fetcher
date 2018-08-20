<?php declare(strict_types=1);

namespace Hsdt\EmbedFetcher;

use GuzzleHttp\Client;
use Hsdt\EmbedFetcher\Cache\NullCache;
use Hsdt\EmbedFetcher\Exception\UnknownProviderException;
use Hsdt\EmbedFetcher\Provider\{
    Exception\InvalidUrlException,
    InstagramPost,
    TwitterTweet,
    VimeoVideo,
    YoutubeVideo,
    ApesterPoll,
    Image,
    YoutubePlaylist
};
use Psr\SimpleCache\CacheInterface;

class Fetcher
{
    private const PROVIDERS = [
        YoutubeVideo::class,
        YoutubePlaylist::class,
        VimeoVideo::class,
        InstagramPost::class,
        TwitterTweet::class,
        ApesterPoll::class,
        Image::class
    ];

    /**
     * @var array
     */
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'cache' => new NullCache(),
            'cache_ttl' => 3600,
            'google_api_key' => '',
            'guzzle_options' => [],
        ], $options);

        $this->options['http_client'] = new Client(array_replace_recursive([
            'connect_timeout' => 5,
            'timeout' => 5,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36',
                'Accept' => 'application/json'
            ]
        ], $this->options['guzzle_options']));
    }

    /**
     * @param string $url
     *
     * @return Data
     * @throws UnknownProviderException
     */
    public function fetch(string $url): Data
    {
        $cacheKey = 'embed_data_' . md5($url);
        $data = $this->getCache()->get($cacheKey);
        if ($data) {
            return $data;
        }

        foreach (self::PROVIDERS as $provider) {
            /** @var ProviderInterface $providerInstance */
            $providerInstance = new $provider($this->options);

            try {
                $data = $providerInstance->fetch($url);
            } catch (InvalidUrlException $e) {
                continue;
            }

            $this->getCache()->set($cacheKey, $data, $this->options['cache_ttl']);
            return $data;
        }

        throw new UnknownProviderException();
    }

    private function getCache(): CacheInterface
    {
        return $this->options['cache'];
    }
}
