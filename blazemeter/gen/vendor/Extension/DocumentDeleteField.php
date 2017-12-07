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
class DocumentDeleteField extends Extension {
  protected function setup() {
  }

  /**
   * {@inheritdoc}
   */
  protected function doClassProcess() {

    if (isset($this->configClass['deletedField']) && $this->configClass['deletedField']) {
      $this->processTemplate($this->definitions['document_base'], file_get_contents(__DIR__ . '/templates/DocumentDeleteField.php.twig'));
      $this->processTemplate($this->definitions['query_base'], file_get_contents(__DIR__ . '/templates/DocumentQueryDeleteField.php.twig'));
    }
  }
}
