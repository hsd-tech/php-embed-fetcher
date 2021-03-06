<?php declare(strict_types=1);

namespace Hsdt\EmbedFetcher\Provider;

use Hsdt\EmbedFetcher\Data;
use Hsdt\EmbedFetcher\Provider\Exception\FetchException;
use Hsdt\EmbedFetcher\Provider\Exception\InvalidUrlException;
use Hsdt\EmbedFetcher\ProviderInterface;
use GuzzleHttp\Exception\RequestException;
use Hsdt\SocialMedia\Provider\AbstractProvider;

class YoutubePlaylist extends AbstractProvider implements ProviderInterface
{
    public const NAME = 'youtube_playlist';
    public const REGEX = '~^(https?://)?((www|m)\.)?youtu(\.be|be\.com)/(playlist\?list=)?(?<id>[\w-]{11,})~';

    /**
     * @param string $url
     *
     * @return Data
     * @throws FetchException
     * @throws InvalidUrlException
     */
    public function fetch(string $url): Data
    {
        if (preg_match(self::REGEX, $url, $matches)) {
            throw new InvalidUrlException(self::NAME);
        }

        try {
            $response = $this->getClient()->get('https://www.googleapis.com/youtube/v3/playlistItems', [
                'query' => [
                    'key' => $this->options['google_api_key'],
                    'playlistId' => $matches['id'],
                    'part' => 'id,contentDetails,snippet,status'
                ]
            ]);
        } catch (RequestException $e) {
            throw new FetchException(self::NAME);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new FetchException('youtube', new \UnexpectedValueException('invalid json'));
        }

        return new Data(self::NAME, $data);
    }
}
