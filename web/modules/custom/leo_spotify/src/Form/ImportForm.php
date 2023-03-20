<?php

namespace Drupal\leo_spotify\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\leo_spotify\SpotifyApi;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Import artists, albums and song from spotify.
 */
class ImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'leo_spotify_import_form';
  }

  // inject service container
  public function __construct(private SpotifyApi $spotify_service) {
    $this->spotify_service = $spotify_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('leo_spotify.spotifyapi')
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['import_type'] = [
      '#type' => 'radios',
      '#title' => t('Import type'),
      '#options' => [
        'artists' => t('Artists from file'),
        'artist' => t('Artist with Spotify Id'),
        'albums' => t('Albums'),
        'songs' => t('Songs'),
      ],
      '#default_value' => 'artists',
    ];

    $form['artist'] = [
      '#type' => 'textfield',
      '#title' => t('Artist Spotify Id'),
      '#description' => t('Enter the Spotify Id of the artist you want to import'),
      '#states' => [
        'visible' => [
          ':input[name="import_type"]' => ['value' => 'artist'],
        ],
      ]
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Import'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('import_type') == 'artist') {
      if (empty($form_state->getValue('artist'))) {
        $form_error = new TranslatableMarkup('<b>@message!</b> <br> <a href="@link!">click more info</a>', array(
          '@message' => 'Please enter a Spotify Id for the artist' ,
          '@link' => 'https://support.tunecore.com/hc/en-us/articles/360040325651-How-do-I-find-my-Artist-ID-for-Spotify-and-iTunes-'
        ));
        $form_state->setErrorByName('artist', $form_error);
      }
    }
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if ($values['import_type'] == 'artists') {
      $artists = $this->spotify_service->getTopArtists();
      if ($artists) {
        foreach ($artists as $artist) {
          $this->spotify_service->save_artist($artist);
        }
        $this->messenger()->addStatus('Artists Imported');
      }
    }
    if ($values['import_type'] == 'artist') {
      $artist = $this->spotify_service->getArtist($values['artist']);
      if ($artist) {
        $this->spotify_service->save_artist($artist);
        $this->messenger()->addStatus('Artist Imported');
      }
    }
  }

}
