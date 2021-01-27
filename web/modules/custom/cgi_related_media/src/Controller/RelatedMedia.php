<?php

namespace Drupal\cgi_related_media\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class RelatedMedia extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function mediaPage() {
    //Get the latest CGI media.
    $get_media_announcement[] = $this->getNodeTaggedTerms('Media announcement');
    $get_media_announcement[] = $this->getNodeTaggedTerms('Brochure');
    $get_media_announcement[] = $this->getNodeTaggedTerms('White paper');

    $get_media_nids = $this->getMediaNids($get_media_announcement);
    $ordered_media_list = array_merge($get_media_announcement, $get_media_nids);
    foreach ($ordered_media_list as $key => $nid) {
        $node = \Drupal::entityManager()->getStorage('node')->load($nid);
        $view_builder = \Drupal::entityManager()->getViewBuilder('node');
        if (!empty($node)) {
            $teaser_view_node = $view_builder->view($node, 'teaser');
            $media_nodes[$nid] = $teaser_view_node;
        }
    }
    //Get the base url of the drupal
    $host = \Drupal::request()->getSchemeAndHttpHost();
    //Get the contents from json file.
    $get_blog_content = file_get_contents($host . '/blog_feed.json');
    $blog_data = json_decode($get_blog_content, TRUE);
    return [
        '#theme' => 'related_media_block',
        '#media_teaser_nodes' => $media_nodes,
        '#blog_data' => $blog_data['blogs'],
    ];
  }
 /**
  * Get the latest and single nid of the term.
  *
  * @return nid
  *   The latest nid for the term.
  */
  private function getNodeTaggedTerms($term_name) {
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1)
          ->condition('type', 'cgi_media')
          ->condition('field_cgi_media_category.entity.name', $term_name)
          ->sort('created', 'DESC')
          ->range(0, 1);
    $node_nid = $query->execute();
    $nid = array_shift($node_nid);
    return $nid;
  }
 /**
  * Get the list of nids of the CGI media content type.
  *
  * @return array
  *   The list of nids for the CGI Media.
  */
  private function getMediaNids($nids) {
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1)
          ->condition('type', 'cgi_media')
          ->condition('nid', $nids, 'NOT IN')
          ->sort('created', 'DESC');
    $nids = $query->execute();
    return $nids;
  }
}
