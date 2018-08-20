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

class Image extends AbstractProvider implements ProviderInterface
{
    public const NAME = 'image';
    public const REGEX = '~^https?://.+~';

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
            $response = $this->getClient()->head($url);
            if (!preg_match('~image/(jpeg|jpg|png|gif)~', $response->getHeaderLine('Content-Type'))) {
                throw new InvalidUrlException(self::NAME);
            }
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === HttpStatus::STATUS_NOT_FOUND) {
                throw new MediaNotFoundException(self::NAME);
            }
            throw new FetchException(self::NAME, $e);
        }

        $contentLength = $response->getHeaderLine('Content-Length');
        $size = !empty($contentLength) ? intval($contentLength) : 0;

        return new Data(self::NAME, [
            'url' => $url,
            'size' => $size,
            'type' => $response->getHeaderLine('Content-Type')
        ]);
    }
}
