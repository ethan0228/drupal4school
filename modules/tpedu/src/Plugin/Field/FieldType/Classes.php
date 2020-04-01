<?php

namespace Drupal\tpedu\Plugin\Field\FieldType;

use Drupal\Core\Field\ListItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'tpedu_classes' field type.
 *
 * @FieldType(
 *   id = "tpedu_classes",
 *   label = "班級",
 *   description = "班級列表",
 *   category = "臺北市教育人員",
 *   default_widget = "classes_default",
 *   default_formatter = "classes_default"
 * )
 */
class Classes extends ListItemBase {

    public function getPossibleOptions(AccountInterface $account = NULL) {
        if ($account->init == 'tpedu') {
            if ($this->getSetting('filter_by_current_user')) $classes = get_teach_classes($account->uuid);
            if ($this->getSetting('filter_by_subject')) $classes = get_teach_classes($account->uuid);
            if ($this->getSetting('filter_by_grade')) $classes = get_teach_classes($account->uuid);
        }
        else {
            $classes = all_classes();
        }
        foreach ($classes as $c) {
          $values[$c->id] = $c->name;
        }    
        return $values;
    }

    public static function defaultFieldSettings() {
        return [
            'filter_by_grade' => false,
            'filter_by_subject' => false,
            'filter_by_current_user' => false,
            'inline_columns' => 1,
        ] + parent::defaultFieldSettings();
    }

    public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
  
        $form['filter_by_grade'] = array(
            '#type' => 'checkbox',
            '#title' => '依年級欄位過濾班級',
            '#description' => '若勾選，僅顯示指定年級的所有班級。',
            '#default_value' => $this->getSetting('filter_by_grade'),
        );
        $form['filter_by_subject'] = array(
            '#type' => 'checkbox',
            '#title' => '依配課科目過濾班級',
            '#description' => '若勾選，僅顯示指定科目的所有已配課班級。',
            '#default_value' => $this->getSetting('filter_by_subject'),
        );
        $form['filter_by_current_user'] = array(
            '#type' => 'checkbox',
            '#title' => '依使用者過濾班級',
            '#description' => '若勾選，僅顯示目前使用者的任教班級。',
            '#default_value' => $this->getSetting('filter_by_current_user'),
        );
        $form['inline_columns'] = array(
            '#type' => 'number',
            '#title' => '每行顯示數量',
            '#min' => 1,
            '#max' => 12,
            '#description' => '當使用核取框（複選）或選取鈕（單選）時，您可以指定每一行要顯示的欄位數量。',
            '#default_value' => $this->getSetting('inline_columns'),
        );      
        return $form;
    }

}