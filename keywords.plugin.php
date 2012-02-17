<?php

/**
 * Keywords
 * Keywords plugin adds Meta Keywords and Description for each post.
 * Inspired by MetaSEO plugin by Habari Community - http://habariproject.org
 * 
 * @package Keywords
 * @version 1.4
 * @author Petr Stuchlik - http://stuchl4n3k.net
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0 (unless otherwise stated)
 * @link http://stuchl4n3k.net/project-habari-keywords
 */
class Keywords extends Plugin {

    /**
     * @var $post Post object that is currently being rendered
     */
    private $post;

    /**
     * We only want to use this to obtain reference on current Theme object.
     *
     * @access public
     * @return string
     */
    public function filter_post_content_out($content, $post) {
        $this->post = $post;
        return $content;
    }

    /**
     * function filter_final_output
     *
     * this filter is called before the display of any page, so it is used 
     * to make any final changes to the output before it is sent to the browser
     *
     * @param $buffer string the page being sent to the browser
     * @return  string the modified page
     */
    public function filter_final_output($buffer) {
        $keywords = $this->get_keywords();
        if (strlen($keywords)) {
            $template_keywords = $this->extract_keywords($buffer);
            if (strlen($template_keywords)) {
                $buffer = str_replace($template_keywords, $keywords, $buffer);
            }
        }

        $description = $this->get_description();
        if (strlen($description)) {
            $template_description = $this->extract_description($buffer);
            if (strlen($template_description)) {
                $buffer = str_replace($template_description, $description, $buffer);
            }
        }

        return $buffer;
    }

    /**
     * Add additional controls to the publish page tab
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
     * Modify a post before it is updated
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
     * Return the string list of keywords for the post
     *
     * @access private
     * @return string
     */
    private function get_keywords() {
        $out = '';
        $keywords = '';

        $matched_rule = URL::get_matched_rule();
        if (is_object($matched_rule)) {
            $rule = $matched_rule->name;
            switch ($rule) {
                case 'display_entry':
                case 'display_page':
                    if (isset($this->post)) {
                        if (strlen($this->post->info->keywords)) {
                            $keywords = $this->post->info->keywords;
                        } else if (count($this->post->tags) > 0) {
                            $keywords = implode(',', (array) $this->post->tags);
                        }
                    }
                    break;
                case 'display_entries_by_tag':
                    $keywords = Controller::get_var('tag');
                    break;
                default:
            }
        }
        $keywords = htmlspecialchars(strip_tags($keywords), ENT_COMPAT, 'UTF-8');
        if (strlen($keywords)) {
            $out = "<meta name=\"keywords\" content=\"" . $keywords . "\">";
        }
        return $out;
    }

    /**
     * Return the description for the post
     *
     * @access private
     * @return string
     */
    private function get_description() {
        $out = '';
        $description = '';

        $matched_rule = URL::get_matched_rule();
        if (is_object($matched_rule)) {
            $rule = $matched_rule->name;
            switch ($rule) {
                case 'display_entry':
                case 'display_page':
                    if (isset($this->post)) {
                        if (strlen($this->post->info->description)) {
                            $description = $this->post->info->description;
                        }
                    }
                    break;
                default:
            }
        }
        $description = htmlspecialchars(strip_tags($description), ENT_COMPAT, 'UTF-8');
        if (strlen($description)) {
            $out = "<meta name=\"description\" content=\"" . $description . "\">";
        }
        return $out;
    }

    private function extract_keywords($content) {
        $patterns = array();
        $tags = preg_match('/(<meta name="keywords" content="[^"]*">)/i', $content, $patterns);
        $res = isset($patterns[1]) ? $patterns[1] : NULL;
        return $res;
    }

    private function extract_description($content) {
        $patterns = array();
        $tags = preg_match('/(<meta name="description" content="[^"]*">)/i', $content, $patterns);
        $res = isset($patterns[1]) ? $patterns[1] : NULL;
        return $res;
    }

}

?>
