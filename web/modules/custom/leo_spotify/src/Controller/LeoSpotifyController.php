<?php

namespace Drupal\leo_spotify\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for leo_spotify routes.
 */
class LeoSpotifyController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
