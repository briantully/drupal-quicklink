<?php

namespace Drupal\quicklink\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class QuicklinkConfig.
 */
class QuicklinkConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'quicklink.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'quicklink_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('quicklink.settings');

    // Build form elements.
    $form['settings'] = [
      '#type' => 'vertical_tabs',
      '#attributes' => ['class' => ['quicklink']],
    ];

    // Ignore tab.
    $form['ignore'] = [
      '#type' => 'details',
      '#title' => $this->t('Prefetch Ignore Settings'),
      '#description' => $this->t('On this tab, specify what Quicklink should not prefetch.'),
      '#group' => 'settings',
    ];
    $form['ignore']['prefetch_for_anonymous_users_onl'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Prefetch for anonymous users only'),
      '#description' => $this->t('Highly recommended. Only prefetch URLs for anonymous users.'),
      '#default_value' => $config->get('prefetch_for_anonymous_users_onl'),
    ];
    $form['ignore']['ignore_admin_paths'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Do not prefetch admin paths'),
      '#description' => $this->t('Highly recommended. Ignore administrative paths.'),
      '#default_value' => $config->get('ignore_admin_paths'),
    ];
    $form['ignore']['ignore_ajax_links'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Do not prefetch AJAX links'),
      '#description' => $this->t('Highly recommended. Ignore links that trigger AJAX behavior.'),
      '#default_value' => $config->get('ignore_ajax_links'),
    ];
    $form['ignore']['ignore_hashes'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Ignore paths with hashes (#) in them'),
      '#description' => $this->t('Recommended. Prevents multiple prefetches of the same page.'),
      '#default_value' => $config->get('ignore_hashes'),
    ];
    $form['ignore']['ignore_file_ext'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Ignore paths with file extensions'),
      '#description' => $this->t('Recommended. This will ignore links that end with a file extension.
        It will match strings ending with a period followed by 1-4 characters.'),
      '#default_value' => $config->get('ignore_file_ext'),
    ];
    $form['ignore']['url_patterns_to_ignore'] = [
      '#type' => 'textarea',
      '#title' => $this->t('URL patterns to ignore (optional)'),
      '#description' => $this->t('Quicklink will not fetch data if the URL contains any of these patterns. One per line.'),
      '#default_value' => $config->get('url_patterns_to_ignore'),
      '#attributes' => [
        'style' => 'max-width: 600px;',
      ],
    ];

    $options = [];
    $types = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
    foreach ($types as $type) {
      $options[$type->id()] = $type->label();
    }
    $ignored_types = !empty($config->get('ignored_content_types')) ? $config->get('ignored_content_types') : [];
    $form['ignore']['ignored_content_types'] = [
      '#title' => $this->t('Ignored content types'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $ignored_types,
    ];

    // Overrides tab.
    $form['overrides'] = [
      '#type' => 'details',
      '#title' => $this->t('Optional Overrides'),
      '#description' => $this->t('On this tab, specify various overrides.'),
      '#group' => 'settings',
    ];
    $form['overrides']['selector'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Override parent selector (optional)'),
      '#description' => $this->t('Quicklink will search this CSS selector for URLs to prefetch (ex. <code>.body-inner</code>). Defaults to the whole document.'),
      '#maxlength' => 128,
      '#size' => 128,
      '#default_value' => $config->get('selector'),
      '#attributes' => [
        'style' => 'max-width: 600px;',
      ],
    ];
    $form['overrides']['allowed_domains'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Override allowed domains (optional)'),
      '#description' => $this->t('List of domains to prefetch from. If empty, Quicklink will only prefetch links from the origin domain.
        If you configure this, be sure to input the origin domain. Add <code>true</code> here to allow <em>every</em> origin.'),
      '#default_value' => $config->get('allowed_domains'),
      '#attributes' => [
        'style' => 'max-width: 600px;',
      ],
    ];

    // Polyfill tab.
    $form['polyfill'] = [
      '#type' => 'details',
      '#title' => $this->t('Extended Browser Support'),
      '#description' => $this->t('On this tab, include support of additional browsers via polyfill.'),
      '#group' => 'settings',
    ];
    $form['polyfill']['load_polyfill'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Load <em>Intersection Observer</em> polyfill'),
      '#description' => $this->t('This checkbox will enable loading of the <a href="https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API" target="_blank">
        Intersection Observer</a> polyfill from <a href="https://polyfill.io" target="_blank">polyfill.io</a>. This will enable usage of Quicklink in Safari and Microsoft Edge browsers.'),
      '#default_value' => $config->get('load_polyfill'),
    ];

    // Debug tab.
    $form['debug'] = [
      '#type' => 'details',
      '#title' => $this->t('Debug'),
      '#description' => $this->t('On this tab, enable debug logging.'),
      '#group' => 'settings',
    ];
    $form['debug']['enable_debug_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable debug mode'),
      '#description' => $this->t("Log Quicklink development information to the HTML and JavaScript console. You may need to Drupal's cache after changing this value."),
      '#default_value' => $config->get('enable_debug_mode'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('quicklink.settings')
      ->set('ignored_content_types', $form_state->getValue('ignored_content_types'))
      ->set('selector', trim($form_state->getValue('selector')))
      ->set('url_patterns_to_ignore', trim($form_state->getValue('url_patterns_to_ignore')))
      ->set('prefetch_for_anonymous_users_onl', $form_state->getValue('prefetch_for_anonymous_users_onl'))
      ->set('ignore_admin_paths', $form_state->getValue('ignore_admin_paths'))
      ->set('ignore_ajax_links', $form_state->getValue('ignore_ajax_links'))
      ->set('ignore_hashes', $form_state->getValue('ignore_hashes'))
      ->set('ignore_file_ext', $form_state->getValue('ignore_file_ext'))
      ->set('allowed_domains', trim($form_state->getValue('allowed_domains')))
      ->set('load_polyfill', $form_state->getValue('load_polyfill'))
      ->set('enable_debug_mode', $form_state->getValue('enable_debug_mode'))
      ->save();
  }

}
