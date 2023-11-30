<?php
namespace modules\scratchpad\console\controllers;

use Craft;
use craft\fields\Dropdown;
use craft\models\EntryType;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

// ex terminal command:
// docker-compose exec web ./craft scratchpad/ex2-layout-field/new-fields --field=pageContent

class Ex2LayoutFieldController extends Controller
{
    public $field = ''; // the matrix field to append the new field to

    public $backgroundFieldConfig = [
        'name' => 'Background Color',
        'handle' => 'backgroundColor',
        'instructions' => 'This color will cover the full background of the block.',
        'required' => false,
        'options' => [
            [
                'label' => 'Red',
                'value' => 'red',
                'default' => '',
            ], [
                'label' => 'Green',
                'value' => 'green',
                'default' => '',
            ], [
                'label' => 'Blue',
                'value' => 'blue',
                'default' => '',
            ],
        ],
    ];

    public function options($actionId): array
    {
        $options = parent::options($actionId);

        // For each `actionId`, add options to the console command
        if ($actionId === 'new-fields') {
            $options[] = 'field';
        }

        return $options;
    }

    public function actionNewFields()
    {
        if (!$this->field) {
            $this->stderr('You must provide a --field option. This is the matrix field that needs to have the new fields appended to its blocks.' . PHP_EOL, Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout('Process started' . PHP_EOL, Console::FG_GREEN);

        $matrix = Craft::$app->fields->getFieldByHandle($this->field);

        foreach($matrix->blockTypes as $blockType) {
            $fieldLayout = $blockType->fieldLayout;
            $tab = $fieldLayout->tabs[0];

            $field = new \craft\fields\Dropdown($this->backgroundFieldConfig);

            $tab->setElements([
                ... $tab->elements,
                new \craft\fieldlayoutelements\CustomField($field)
            ]);

            $this->stdout('Field added to ' . $blockType->handle . PHP_EOL, Console::FG_GREEN);
        }

        Craft::$app->fields->saveField($matrix);

        $this->stdout('Process completed' . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }


}
