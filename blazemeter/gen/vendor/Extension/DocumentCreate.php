<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace blazemeter\Common\Mandango\Extension;

use Mandango\Mondator\Extension;

/**
 * DocumentArrayAccess extension.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class DocumentCreate extends Extension {
  protected function setup() {
  }

  /**
   * {@inheritdoc}
   */
  protected function doClassProcess() {
      $this->processTemplate($this->definitions['document_base'], file_get_contents(__DIR__ . '/templates/DocumentCreate.php.twig'));
  }
}
