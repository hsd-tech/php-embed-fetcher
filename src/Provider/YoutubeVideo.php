<?php declare(strict_types=1);

namespace Hsdt\EmbedFetcher\Provider;

use Hsdt\EmbedFetcher\Data;
use Hsdt\EmbedFetcher\Provider\Exception\FetchException;
use Hsdt\EmbedFetcher\Provider\Exception\InvalidUrlException;
use Hsdt\EmbedFetcher\Provider\Exception\MediaNotFoundException;
use Hsdt\EmbedFetcher\ProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Hsdt\SocialMedia\Provider\AbstractProvider;

class YoutubeVideo extends AbstractProvider implements ProviderInterface
{
    public const NAME = 'youtube_video';
    public const REGEX = '~^(https?://)?((www|m)\.)?youtu(\.be|be\.com)/(watch\?v=|watch\?.+&v=|embed/)?(?<id>[\w-]{11,})~';

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
        if (!preg_match(self::REGEX, $url, $matches)) {
            throw new InvalidUrlException(self::NAME);
        }

        try {
            $response = $this->getClient()->get('https://www.googleapis.com/youtube/v3/videos', [
                'query' => [
                    'key' => $this->options['google_api_key'],
                    'id' => $matches['id'],
                    'part' => 'id,statistics,contentDetails,snippet'
                ]
            ]);
        } catch (RequestException $e) {
            throw new FetchException(self::NAME);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new FetchException(self::NAME, new \UnexpectedValueException('invalid json'));
        }

        if (count($data['items']) === 0) {
            throw new MediaNotFoundException(self::NAME);
        }

        return new Data(self::NAME, $data['items'][0]);
    }
}
