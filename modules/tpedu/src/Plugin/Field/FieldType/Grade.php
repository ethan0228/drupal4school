<?php

namespace Drupal\tpedu\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'tpedu_classes' field type.
 *
 * @FieldType(
 *   id = "tpedu_grade",
 *   label = "年級",
 *   module ="tpedu",
 *   description = "年級選單",
 *   category = "臺北市校園",
 *   default_widget = "grade_default",
 *   default_formatter = "grade_default",
 * )
 */
class Grade extends FieldItemBase
{
    public static function schema(FieldStorageDefinitionInterface $field)
    {
        return array(
          'columns' => array(
            'grade' => array(
                'type' => 'varchar_ascii',
                'length' => 50,
                'not null' => false,
            ),
          ),
        );
    }

    public function isEmpty()
    {
        return empty($this->get('grade')->value);
    }

    public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition)
    {
        $properties['grade'] = DataDefinition::create('string')->setLabel('年級');

        return $properties;
    }

    public function fieldSettingsForm(array $form, FormStateInterface $form_state)
    {
        $element = array();
        $element['extra_info'] = array(
            '#markup' => '<p>此欄位可以單獨使用或結合班級、教師欄位使用！結合班級欄位時，可用於選取不同年級的班級；結合教師欄位時，可用於選取不同年級的導師！</p>',
        );

        return $element;
    }
}
