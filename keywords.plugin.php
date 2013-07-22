<?php

/**
 * Keywords
 * Keywords plugin adds keywords, description and robots meta tags to each page.
 * Different meta tags can be set up for different page types.
 * 
 * @package Keywords
 * @version 1.6
 * @author Petr Stuchlik
 * @link http://stuchl4n3k.net/keywords-plugin Plugin homepage
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */
class Keywords extends Plugin {

  /**
   * @var array Currenlty supported meta tags
   */
  private $defined_meta_tags = array(
      'keywords',
      'description',
      'robots',
  );
  
  /**
   * @var array Available rules (page types), you can set different meta tags
   * for each rule.
   * @see RewriteRules::add_system_rules() 
   */
  private $defined_rules = array(
      'display_home',
      'display_entry',
      'display_post',
      'display_page',
      'display_entries',
      'display_entries_by_date',
      'display_entries_by_tag',
      'display_search',
      'display_404',
  );

  /**
   * @var Theme An active Theme instance
   */
  private $theme;

  /////////////////////////////
  // HOOKS
  /////////////////////////////

  public function action_init() {
    $this->load_text_domain(__CLASS__);
    $this->load_options();
  }

  public function action_admin_header($theme) {
    Stack::add('admin_header_javascript', array($this->get_url() . '/keywords_admin.js'), 'keywords_admin', 'jquery');
    Stack::add('admin_stylesheet', array($this->get_url() . '/keywords_admin.css', 'screen'));
  }

  public function action_template_header($theme) {
    $this->theme = $theme;
    $rule = $this->get_matched_rule();
    $tags = $this->get_meta_tags($rule);
    print "\n\n" . implode("\n", $tags) . "\n\n";
  }

  /**
   * Add additional controls to the publish page tab.
   *
   * @param FormUI $form The form that is used on the publish page
   * @param Post $post The post being edited
   */
  public function action_form_publish($form, $post) {
    $fieldset = $form->publish_controls->append('fieldset', 'meta', 'Meta');

    $keywords = $fieldset->append('text', 'keywords', 'null:null', 'Keywords');
    $keywords->value = strlen($post->info->keywords) ? $post->info->keywords : '';
    $keywords->template = 'tabcontrol_text';

    $description = $fieldset->append('textarea', 'description', 'null:null', 'Description');
    $description->value = isset($post->info->description) ? $post->info->description : '';
    $description->template = 'tabcontrol_textarea';
  }

  /**
   * Modify a post before it is updated.
   *
   * @param Post $post The post being saved, by reference
   * @param FormUI $form The form that was submitted on the publish page
   */
  public function action_publish_post($post, $form) {
    if (strlen($form->meta->keywords->value)) {
      $post->info->keywords = htmlspecialchars(Utils::truncate(strip_tags($form->meta->keywords->value), 200, false), ENT_COMPAT, 'UTF-8');
    } else {
      $post->info->__unset('keywords');
    }

    if (strlen($form->meta->description->value)) {
      $post->info->description = htmlspecialchars(Utils::truncate(strip_tags($form->meta->description->value), 200, false), ENT_COMPAT, 'UTF-8');
    } else {
      $post->info->__unset('description');
    }
  }

  /**
   * Configuration settings to appear on the plugin page.
   * 
   * @return object FormUI object
   */
  public function configure() {
    URL::get_matched_rule();
    $ui = new FormUI('keywords_configuration');

    foreach ($this->defined_meta_tags as $type) {
      $tagslug = Utils::slugify($type, '');
      $fieldset_tag = $ui->append('fieldset', 'group_' . $tagslug, $this->t('Meta tag %s', array($type)));

      $fieldset_tag->append('text', $tagslug . '_default', 'option:keywords__data_' . $type . '_default', $this->t('Default content:'));

      $fieldset_advanced = $fieldset_tag->append('fieldset', 'group_' . $tagslug . '_advanced', $this->t('Advanced'));
      foreach ($this->defined_rules as $rule) {
        $fieldset_advanced->append('text', $tagslug . '_' . $rule, 'option:keywords__data_' . $type . '_' . $rule, $this->t('Content for %s:', array($rule)));
      }
    }

    $ui->append('submit', 'save', 'Save');

    return $ui;
  }

  /////////////////////////////
  // HELPERS
  /////////////////////////////

  /**
   * Creates an array of printable meta tags.
   * 
   * @param string $rule
   * @return array Array of meta tags
   */
  public function get_meta_tags($rule) {
    if (!in_array($rule, $this->defined_rules)) {
      return NULL;
    }

    $tags = array();
    foreach ($this->defined_meta_tags as $type) {
      $tag_data = $this->get_meta_tag_data($type, $rule);
      $tags[$type] = $this->create_meta_tag($tag_data);
    }
    return $tags;
  }

  public function get_meta_tag_data($type, $rule) {
    $data = array();
    if (substr($type, 0, 3) === 'og:' || substr($type, 0, 3) === 'fb:' || substr($type, 0, 8) === 'twitter:') {
      $data['property'] = $type;
    } else {
      $data['name'] = $type;
    }
    $data['content'] = $this->get_meta_tag_content($type, $rule);
    return $data;
  }

  public function get_meta_tag_content($type, $rule) {
    $content = NULL;

    if ($type === 'keywords') {
      $content = $this->get_keywords_content($rule);
    } else if ($type === 'description') {
      $content = $this->get_description_content($rule);
    } else {
      $content = Options::get('keywords__data_' . $type . '_' . $rule, '');
    }

    if (strlen($content) === 0) {
      $content = Options::get('keywords__data_' . $type . '_default', '');
    }

    return htmlspecialchars(strip_tags($content), ENT_COMPAT, 'UTF-8');
  }

  /**
   * Returns keywords metatag for the given URL rule.
   * 
   * @param string $rule URL rule
   * @return string complete meta tag
   */
  public function get_keywords_content($rule) {
    $content = NULL;

    if ($rule === 'display_entry' || $rule === 'display_page') {
      if (isset($this->theme->post)) {
        $post = $this->theme->post;
        if (strlen($post->info->keywords)) {
          $content = $post->info->keywords;
        } else if (count($post->tags) > 0) {
          $content = implode(',', (array) $post->tags);
        }
      }
    }

    if ($content === NULL || strlen($content) === 0) {
      $content = Options::get('keywords__data_keywords_' . $rule, '');
    }

    return $content;
  }

  /**
   * Returns description metatag for the given URL rule.
   * 
   * @param string $rule URL rule
   * @return string complete meta tag
   */
  public function get_description_content($rule) {
    $content = '';

    if ($rule === 'display_entry' || $rule === 'display_page') {
      if (isset($this->theme->post)) {
        $post = $this->theme->post;
        $content = $post->info->description;
      }
    }

    if ($content === NULL || strlen($content) === 0) {
      $content = Options::get('keywords__data_description_' . $rule, '');
    }

    return $content;
  }

  private function get_matched_rule() {
    $matched_rule = URL::get_matched_rule();
    if (is_object($matched_rule)) {
      $rule = $matched_rule->name;
      return $rule;
    }
    return NULL;
  }

  private function create_meta_tag(array $tag_data) {
    $tag = '<meta';
    foreach ($tag_data as $key => $value) {
      $tag .= ' ' . $key . '="' . $value . '"';
    }
    $tag .= '>';
    return $tag;
  }

  /**
   * Translation helper.
   * @param string $what
   * @param array $args
   * @return string
   */
  private function t($what, $args = array()) {
    return _t($what, $args, __CLASS__);
  }

  private function load_options() {
    $this->options = Options::get_group('keywords');
  }

}

?>
