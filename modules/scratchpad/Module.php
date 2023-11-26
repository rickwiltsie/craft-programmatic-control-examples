<?php

namespace modules\scratchpad;

use Craft;
use yii\base\Module as BaseModule;

/**
 * scratchpad module
 *
 * @method static Module getInstance()
 */
class Module extends BaseModule
{
    public function init(): void
    {
        Craft::setAlias('@modules/scratchpad', __DIR__);

        // Set the controllerNamespace based on whether this is a console or web request
        if (Craft::$app->request->isConsoleRequest) {
            $this->controllerNamespace = 'modules\\scratchpad\\console\\controllers';
        }
    }
}
