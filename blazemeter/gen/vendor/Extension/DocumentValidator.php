<?php


namespace blazemeter\Common\Mandango\Extension;


use Mandango\Mondator\Definition\Method;
use Mandango\Mondator\Definition\Property;
use Mandango\Mondator\Extension;

/**
 * DocumentValidation extension.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class DocumentValidator extends Extension {


  /**
   * {@inheritdoc}
   */
  protected function doClassProcess() {
    $validation = array(
      'constraints' => array(),
      'validators'  => array(),
      'defaults'    => array(),
      'mandatory'   => array()
    );


    foreach (['fields', 'referencesOne', 'referencesMany', 'embeddedsOne', 'embeddedsMany'] as $ftype) {
      foreach ($this->configClass[$ftype] as $name => $field) {
        if (empty($field['inherited']) && isset($field['validators']) && $field['validators']) {
          $validation['validators'][$name] = $field['validators'];
        }
        if (!empty($field['type'])) {
          $validation['validators'][$name][] = ['FieldType' => [$field['type']]];
        }
        if (!empty($field['mandatory'])) {
          $validation['validators'][$name][] = ['NotNull' => []];
        }
        if (isset($field['default'])) {
          $validation['defaults'][$name] = $field['default'];
        }
      }
    }


//    $validationStr = Dumper::exportArray($validation, 12);
//    \$validation = $validationStr;

    $method = new Method('public', 'validate', '$metadata', <<<EOF

    foreach (self::\$_validators as \$field => \$validators) {
      if (!empty(\$validators)) {
        \$field_value = \$this->get(\$field);
        foreach (\$validators as \$validator => \$params) {
          if (!class_exists(\$validator)) {
            \$validator_class = 'blazemeter\Common\Validators\\\' . \$validator;
          } else {
            \$validator_class = \$validator;
          }

          if (class_exists(\$validator_class)) {
            if (!call_user_func_array(\$validator_class . '::isValid',[\$field_value ,\$params])) {
              return false;
            }
          }
        }
      }
    }

        return true;
EOF

    );
    $method->setDocComment(<<<EOF
    /**
     * Maps the validation.
     *
     */
EOF

    );

    $this->definitions['document_base']->addMethod($method);

    foreach ([
               'constraints',
               'validators',
               'defaults',
               'mandatory'
             ] as $setting) {

      $property = new Property('protected', '_' . $setting, $validation[$setting]);
      $property->setStatic(true);
      $this->definitions['document_base']->addProperty($property);
    }
  }


}
