<?php
namespace modules\scratchpad\console\controllers;

use Craft;
use craft\fields\Matrix;
use craft\models\EntryType;
use craft\fields\Url;
use Illuminate\Support\Collection;
use presseddigital\linkit\fields\LinkitField;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use craft\db\Query;

// ex terminal command:
// docker-compose exec web ./craft scratchpad/ex3-field-replace/link-it

class Ex3FieldReplaceController extends Controller
{

    public $baseLinkItConfig = [
        'name' => 'TEMP NAME',
        'handle' => 'tempHandle',
        'required' => true,
        'types' => [
            \presseddigital\linkit\models\Url::class => ['enabled' => 1],
            \presseddigital\linkit\models\Entry::class => ['enabled' => 1],
        ]
    ];

    public function actionLinkIt()
    {
        $this->stdout('Process started' . PHP_EOL, Console::FG_GREEN);

        // get all URL fields on entries to be replaced
        $urlFields = Craft::$app->fields->getFieldsByType(Url::class);

        foreach($urlFields as $urlField) {

            // get all layout ids that use this field
            $layoutRecords = (new Query())
                ->select(['layoutId', 'sortOrder'])
                ->from('fieldlayoutfields')
                ->where(['fieldId' => $urlField->id])
                ->collect();

            $linkItConfig = $this->baseLinkItConfig;
            $linkItConfig['groupId'] = 1;
            $linkItConfig['handle'] .= $urlField->id;
            $linkItField = Craft::$app->fields->getFieldByHandle($linkItConfig['handle']);
            if (!$linkItField) {
                $linkItField = new LinkitField($linkItConfig);
                Craft::$app->fields->saveField($linkItField);
            }

            $layoutRecords->each(function($layoutRecord) use ($linkItField) {
                $layout = Craft::$app->fields->getLayoutById($layoutRecord['layoutId']);
                $tab = $layout->tabs[0];

                $tab->setElements([
                    ... $tab->elements,
                    new \craft\fieldlayoutelements\CustomField($linkItField),
                ]);

                Craft::$app->fields->saveLayout($layout);

            });

            // get the entries to have content replaced
            $entries = \craft\elements\Entry::find()
                ->section('pages')
                ->collect();

            $entries->each(function($entry) use ($urlField, $linkItField) {
                // move content from old field to new field
                dump($entry->{$urlField->handle});

                $entry->setFieldValue($linkItField->handle, [
                    'type' => \presseddigital\linkit\models\Url::class,
                    'value' => $entry->{$urlField->handle}
                ]);
                Craft::$app->elements->saveElement($entry);
            });
        }

        // delete old fields and rename new fields
        foreach($urlFields as $urlField) {
            Craft::$app->fields->deleteField($urlField);

            $linkItHandle = $this->baseLinkItConfig['handle'] . $urlField->id;
            $linkItField = Craft::$app->fields->getFieldByHandle($linkItHandle);
            $linkItField->name = $urlField->name;
            $linkItField->handle = $urlField->handle;
            Craft::$app->fields->saveField($linkItField);
        }

        $this->stdout('Process completed' . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }
}
