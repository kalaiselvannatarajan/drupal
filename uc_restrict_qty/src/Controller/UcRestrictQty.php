<?php

namespace Drupal\uc_restrict_qty\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\examples\Utility\DescriptionTemplateTrait;

/**
 * Controller routines for page example routes.
 */
class UcRestrictQty extends ControllerBase {

  use DescriptionTemplateTrait;

  /**
   * {@inheritdoc}
   */
  protected function getModuleName() {
    return 'uc_restrict_qty';
  }

  /**
   * Constructs a simple page.
   *
   * The router _controller callback, maps the path
   * 'examples/page-example/simple' to this method.
   *
   * _controller callbacks return a renderable array for the content area of the
   * page. The theme system will later render and surround the content with the
   * appropriate blocks, navigation, and styling.
   */
  public function titlehelp() {
    return [
      '#markup' => '<p>' . $this->t('Simple page: The quick brown fox jumps over the lazy dog.') . '</p>',
    ];
  }


}


