<?php

namespace Drupal\leo_spotify\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\leo_spotify\SpotifyApi;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Returns responses for leo_spotify routes.
 */
class LeoSpotifyController extends ControllerBase implements ContainerInjectionInterface {

  public function __construct(private SpotifyApi $spotify_service) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('leo_spotify.spotifyapi')
    );
  }
  
  /**
   * Builds the response.
   */
  public function build() {
    $build = [];
    $artists = $this->spotify_service->getTopArtists();

    if ($artists) {
      foreach ($artists as $artist) {
        $this->spotify_service->save_artist($artist);
      }
    }
    $build['content'] = [
      '#type' => 'item',
      '#markup' => print_r($artists),
    ];

    return $build;
  }

}
