<?php
namespace modules\scratchpad\console\controllers;

use Craft;
use craft\fields\Matrix;
use craft\models\MatrixBlockType;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

// ex terminal command:
// docker-compose exec web ./craft scratchpad/seeder/setup

class SeederController extends Controller
{
    public $message = ''; // the message to be said

    public function options($actionId): array
    {
        $options = parent::options($actionId);

        // For each `actionId`, add options to the console command
        if ($actionId === 'say') {
            $options[] = 'message';
        }

        return $options;
    }

    public function actionSetup()
    {
        // create section
        $newSection = new Section([
            'name' => 'Games',
            'handle' => 'games',
            'type' => Section::TYPE_CHANNEL,
            'siteSettings' => [
                new Section_SiteSettings([
                    'siteId' => Craft::$app->sites->getPrimarySite()->id,
                    'enabledByDefault' => true,
                    'hasUrls' => true,
                    'uriFormat' => 'games/{slug}',
                    'template' => 'games/_entry',
                ]),
            ]
        ]);
        Craft::$app->sections->saveSection($newSection);

        // create matrix field
        $newMatrix = Craft::$app->fields->createField([
            'type' => 'craft\fields\Matrix',
            'groupId' => 1,
            'name' => 'Page Content',
            'handle' => 'pageContent',
            'instructions' => '',
            'translationMethod' => 'none',
            'translationKeyFormat' => NULL,
            'settings' => [ ],
        ]);
dump($newMatrix);

        // create and include the block types
        $blockTypes = [];

        $newBlockType = new MatrixBlockType();
        $newBlockType->name = 'Headline';
        $newBlockType->handle = 'headline';
        $blockTypes[] = $newBlockType;

        $newBlockType = new MatrixBlockType();
        $newBlockType->name = 'Box Art';
        $newBlockType->handle = 'boxArt';
        $blockTypes[] = $newBlockType;

        $newBlockType = new MatrixBlockType();
        $newBlockType->name = 'Contents';
        $newBlockType->handle = 'contents';
        $blockTypes[] = $newBlockType;

        $newBlockType = new MatrixBlockType();
        $newBlockType->name = 'Rules';
        $newBlockType->handle = 'rules';
        $blockTypes[] = $newBlockType;


        $newMatrix->setBlockTypes($blockTypes);

        // save matrix field
        $matrixSaved = Craft::$app->fields->saveField($newMatrix);
        dump($matrixSaved);



        /*
        $section = Craft::$app->sections->getSectionByHandle($this->sectionHandle);
        $neoField = Craft::$app->fields->getFieldByHandle($this->neoHandle);
        $neoBlocks = $neoField->blockTypes;

        // clear out the section. Used for development
        if ($this->clearSection) {
            foreach ($section->entryTypes as $entryType) {
                Craft::$app->sections->deleteEntryType($entryType);
            }
        }

        // loop through the neo blocks
        foreach($neoBlocks as $block) {

            // instantiate the new entry type
            $newEntryType = New EntryType();
            $newEntryType->sectionId = $section->id;
            $newEntryType->handle = $block->handle;
            $newEntryType->name = $block->name;

            // make the new field layout
            $newEntryTypeFieldLayout = $newEntryType->getFieldLayout();
            $fieldLayoutTabs = $block->fieldLayout->tabs;
            $newEntryTypeTabs = [];
            foreach($fieldLayoutTabs as $tab) {
                $tab->fields = array_filter($tab->fields, function($field) {
                    if (in_array($field->handle, $this->ignoreFields)) {
                        return false;
                    }
                    return true;
                });
                $tab->layoutId = null;
                $newEntryTypeTabs[] = $tab;
            }
            $newEntryTypeFieldLayout->setTabs($newEntryTypeTabs);
            $newEntryType->setFieldLayout($newEntryTypeFieldLayout);

            // save the entry type
            $saveSuccess = Craft::$app->sections->saveEntryType($newEntryType);

            if ($saveSuccess) {
                $this->stdout($newEntryType->name . ' Entry Type successfully created' . PHP_EOL, Console::FG_GREEN);
            } else {
                $this->stdout($newEntryType->name . ' Entry Type encountered an error during save' . PHP_EOL, Console::FG_RED);
            }
        }
        */

        $this->stdout('Setup' . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }

    public function reset()
    {

    }
}
