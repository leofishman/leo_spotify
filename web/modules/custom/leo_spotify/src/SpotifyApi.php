<?php

namespace Drupal\leo_spotify;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\State\StateInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\node\Entity\Node;

/**
 * Class SpotifyApi
 *
 * Performs API requests to Spotify API to get data
 *
 * @package Drupal\leo_spotify
 */
class SpotifyApi {

  /** @var \GuzzleHttp\ClientInterface */
  protected $httpClient;

  /** @var \Drupal\Core\Config\ImmutableConfig */
  protected $config;

  /** @var \Drupal\Core\State\StateInterface */
  protected $state;

  /** @var \Drupal\Core\Messenger\MessengerInterface */
  protected $messenger;

  protected const SPOTIFY_BASE_URL = 'https://accounts.spotify.com';
  protected const SPOTIFY_API_BASE_URL = 'https://api.spotify.com';

  public function __construct(ClientInterface $client, ConfigFactoryInterface $configFactory, StateInterface $state, MessengerInterface $messenger)
  {
    $this->httpClient = $client;
    $this->config = $configFactory->get('leo_spotify.settings');
    $this->state = $state;
    $this->messenger = $messenger;
  }

  /**
   * @param $artist
   *
   * @return array|mixed
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getArtist($artist) {
    $auth_token = $this->getAuthToken();
    $artist = trim($artist);
    try {
      $response = $this->httpClient->request('GET', self::SPOTIFY_API_BASE_URL . "/v1/artists/{$artist}", [
        'headers' => [
          'Authorization' => 'Bearer ' . $auth_token
        ]
      ]);
    }
    catch (GuzzleException $e) {
      $this->messenger->addMessage('Unable to get artist data: ' . $e->getMessage(), MessengerInterface::TYPE_ERROR);
      return [];
    }

    if ($response->getStatusCode() === 200 or $response->getStatusCode() === 201) {
      return json_decode($response->getBody()->getContents(), TRUE);
    }

    return [];
  }

  /**
   * @param $artist
   *
   * @return array|mixed
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getTopArtists() {
    $auth_token = $this->getAuthToken();
    $artists = [];
    $artists_id = $this->get_artist_file();
    try {
      foreach ($artists_id as $artist) {
        $artist_id = explode(': ', $artist)[1];
        $response = $this->httpClient->request('GET', self::SPOTIFY_API_BASE_URL . "/v1/artists/" . $artist_id, [
          'headers' => [
            'Authorization' => 'Bearer ' . $auth_token
          ]
        ]);
        if ($response->getStatusCode() === 200 or $response->getStatusCode() === 201) {
          $artists[] = json_decode($response->getBody()->getContents(), TRUE);
        }
      }
    }
    catch (GuzzleException $e) {
      $this->messenger->addMessage('Unable to get artist data: ' . $e->getMessage(), MessengerInterface::TYPE_ERROR);
    }

    return $artists;
  }

  public function save_artist($artist) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'artist')
      ->condition('field_spotify_id', $artist['id'])
      ->accessCheck(false);
    $nids = $query->execute();
    if (empty($nids)) {
      $tids = $this->save_genre($artist['genres']);
      $node = Node::create([
        'type' => 'artist',
        'title' => $artist['name'],
        'field_spotify_id' => $artist['id'],
        'field_popularity' => $artist['popularity'],
        'field_genres' => $tids,
        'field_followers' => $artist['followers']['total'],
        'field_poster' => [
          'target_id' => $this->save_image($artist['images'][0]['url'], $artist['name']),
          'alt' => $artist['name'],
          'title' => $artist['name'],
        ],
      ]);
      $node->save();
    }
    // else update the node
    else {
      $node = Node::load(array_pop($nids));
      $node->set('title', $artist['name']);
      $node->set('field_popularity', $artist['popularity']);
      $tids = $this->save_genre($artist);
      $node->set('field_genres', $tids);
      $node->set('field_followers', $artist['followers']['total']);
      $node->set('field_poster', [
        'target_id' => $this->save_image($artist['images'][0]['url'], $artist['name']),
        'alt' => $artist['name'],
        'title' => $artist['name'],
      ]);
      $node->save();
    }
  }

  public function save_genre($artist) {
    $genres = $artist['genres'];
    $tids = [];
    if ($genres) {
      foreach ($genres as $genre) {
        $query = \Drupal::entityQuery('taxonomy_term')
          ->condition('vid', 'genres')
          ->condition('name', $genre)
          ->accessCheck(false);
        $tid = array_keys($query->execute());
        dump(00000, $tid, $genre);
        if (empty($tid)) {
          $term = \Drupal\taxonomy\Entity\Term::create([
            'vid' => 'genres',
            'name' => $genre,
          ]);
          $term->save();
          $tid = $term->id();
          dump( 11, $tid, $term);
        }
        else {
          $tid = array_pop($tid);
        }
        $tids[] = $tid;
      }
      dump(22222,$tids);die;
      return $tids;
    }
  }
  public function save_image($image_url, $name) {
    $artist_poster = file_get_contents($image_url);
    $filename = trim($name) . '.jpeg';
    $current_user = \Drupal::currentUser();
    $file = \Drupal::service('file.repository')->writeData($artist_poster, 'public://artists/' . $filename);
    $file->save();

    return $file->id();
  }

  /**
   * @param $artist
   *  Starting Artist spotify ID.
   * @param int $limit
   *  Number of related artists to return.
   *
   * @return array
   */
  public function getRelatedArtists($artist, $limit = 9) {
    $auth_token = $this->getAuthToken();

    try {
      $response = $this->httpClient->request('GET', self::SPOTIFY_API_BASE_URL . "/v1/artists/{$artist}/related-artists", [
        'headers' => [
          'Authorization' => 'Bearer ' . $auth_token
        ],
        'query' => [
          'limit' => $limit
        ]
      ]);
    }
    catch (GuzzleException $e) {
      $this->messenger->addMessage('Unable to get related artists: ' . $e->getMessage(), MessengerInterface::TYPE_ERROR);
      return [];
    }

    if ($response->getStatusCode() === 200) {
      return json_decode($response->getBody()->getContents(), TRUE);
    }

    return [];
  }

  /**
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Exception
   *
   * @return string
   *   Auth token
   */
  public function getAuthToken() {
    $token = $this->state->get('spotifyapi.token');
    $expiry = $this->state->get('spotifyapi.expires');

    // Check if we've already got a valid token
    if ($expiry && $token && $expiry > time()) {
      return $token;
    }

    // set using $config['spotifyapi.settings']['client_id'] in settings.php or could add a module config form
    $client_id = $this->config->get('client_id');

    // set using $config['spotifyapi.settings']['client_secret'] in settings.php or could add a module config form
    $client_secret = $this->config->get('client_secret');

    $basic_auth = base64_encode($client_id . ':' . $client_secret);

    $response = $this->httpClient->request('POST', self::SPOTIFY_BASE_URL . '/api/token', [
      'form_params' => [
        'grant_type' => 'client_credentials',
      ],
      'headers' => [
        'Authorization' => 'Basic ' . $basic_auth
      ]
    ]);

    if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
      $response_body = $response->getBody()->getContents();
      $response_data = json_decode($response_body);

      $this->state->set('spotifyapi.token', $response_data->access_token);
      $this->state->set('spotifyapi.expires', time() + $response_data->expires_in);

      return $response_data->access_token;
    }

    throw new \Exception("Did not get valid response for auth token ({$response->getStatusCode()})");
  }

  public function get_artist_file() {
    // read file in spotify_artits.txt from module folder
    $file = \Drupal::service('extension.list.module')->getPath('leo_spotify'). '/spotify_artists.txt';
    $artists = explode("\n",file_get_contents($file));

    return $artists;
  }

}

  