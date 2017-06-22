<?php

namespace Drupal\book_visit\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class SettingsForm extends ConfigFormBase {
  
  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory) 
  {
    $this->setConfigFactory($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() 
  {
    return [
      'book_visit.settings'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'book_visit_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) 
  {
    $config = $this->configFactory->get('book_visit.settings');

    $form['max_participants'] = array(
      '#type' => 'number',
      '#title' => 'Max. number of participants allowed to book',
      '#default_value' => $config->get('max_participants'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) 
  {
    $config = $this->configFactory->getEditable('book_visit.settings');
    $config->set('max_participants', $form_state->getValue('max_participants'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) 
  {
    return new static(
      $container->get('config.factory')
    );
  }
}
