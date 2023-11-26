<?php
namespace modules\scratchpad\console\controllers;

use Craft;
use Illuminate\Support\Collection;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

// ex terminal command:
// docker-compose exec web ./craft scratchpad/ex1-content-conversion/remove-superscript

class Ex1ContentConversionController extends Controller
{
    public function actionRemoveSuperscript()
    {
        $fields = new Collection([
            'description',
            'pageContent.details' // Matrix block syntax field handle,
        ]);

        $entries = \craft\elements\Entry::find()
            ->section('pages')
            ->collect();

        $totalEntries = count($entries);
        $entriesProcessed = 0;

        $this->stdout('Conversion started for ' . $totalEntries . ' entries.' . PHP_EOL, Console::FG_GREEN);

        $entries->each(function($entry) use ($fields, &$entriesProcessed, $totalEntries) {
            $fields->each(function($field) use ($entry) {
                $blockField = null;

                // determine if regular field or matrix
                if (str_contains($field, '.')) {
                    $split = explode('.', $field);
                    $field = $split[0];
                    $blockField = $split[1];
                }

                // regular field replace
                if (isset($entry->{$field}) && $blockField == null) {
                    $entry->{$field} = $this->fixCopy($entry->{$field});
                    Craft::$app->elements->saveElement($entry);

                // matrix block field replace
                } else if (isset($entry->{$field}) && $blockField) {

                    foreach ($entry->{$field} as $block) {
                        if (isset($block->{$blockField})) {
                            $block->setFieldValue($blockField, $this->fixCopy($block->{$blockField}));
                            Craft::$app->elements->saveElement($block);
                        }
                    }
                }

            });

            $entriesProcessed++;
            $this->stdout($entriesProcessed . ' of ' . $totalEntries . ' entries processed.' . PHP_EOL, Console::FG_GREEN);
        });

        $this->stdout($totalEntries. ' entries processed!' . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }

    protected function fixCopy($content)
    {
        // strip superscript content
        $regex = '/<sup.*?<\/sup>/i';
        $content = preg_replace($regex, '', $content);

        // strip other tags
        $content = strip_tags($content, '<p>');

        return $content;
    }
}
