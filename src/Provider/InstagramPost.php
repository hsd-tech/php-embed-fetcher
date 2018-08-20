<?php declare(strict_types=1);

namespace Hsdt\EmbedFetcher\Provider;

use Fig\Http\Message\StatusCodeInterface as HttpStatus;
use GuzzleHttp\Exception\RequestException;
use Hsdt\EmbedFetcher\Data;
use Hsdt\EmbedFetcher\Provider\Exception\FetchException;
use Hsdt\EmbedFetcher\Provider\Exception\InvalidUrlException;
use Hsdt\EmbedFetcher\Provider\Exception\MediaNotFoundException;
use Hsdt\EmbedFetcher\ProviderInterface;
use Hsdt\SocialMedia\Provider\AbstractProvider;

class InstagramPost extends AbstractProvider implements ProviderInterface
{
    public const NAME = 'instagram_post';
    public const REGEX = '~^https?://(?:www\.)?instagram\.com/p/[\w-_]+.*$~';

    /**
     * @param string $url
     *
     * @return Data
     * @throws FetchException
     * @throws InvalidUrlException
     * @throws MediaNotFoundException
     */
    public function fetch(string $url): Data
    {
        if (!preg_match(self::REGEX, $url)) {
            throw new InvalidUrlException(self::NAME);
        }

        try {
            $response = $this->getClient()->get('https://api.instagram.com/oembed', [
                'query' => [
                    'url' => $url
                ]
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === HttpStatus::STATUS_NOT_FOUND) {
                throw new MediaNotFoundException(self::NAME);
            }

            throw new FetchException(self::NAME, $e);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new FetchException(self::NAME, new \UnexpectedValueException('invalid json'));
        }

        return new Data(self::NAME, $data);
    }
}
