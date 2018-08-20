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

class ApesterPoll extends AbstractProvider implements ProviderInterface
{
    public const NAME = 'apester_poll';
    public const REGEX = '~^https?://(?:(www|discover)\.)?apester\.com/media/[a-zA-Z0-9]+.*$~';

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
        if (preg_match(self::REGEX, $url)) {
            throw new InvalidUrlException(self::NAME);
        }

        preg_match('~/media/(?<id>[a-zA-Z0-9]+)~', (string)$url, $matches);

        try {
            $this->getClient()->head($url);
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === HttpStatus::STATUS_NOT_FOUND) {
                throw new MediaNotFoundException(self::NAME);
            }

            throw new FetchException(self::NAME, $e);
        }

        return new Data(self::NAME, ['id' => $matches['id']]);
    }
}
