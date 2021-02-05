<?php

namespace import;


use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\i18n\PhpMessageSource;
use yii\web\Application;
use yii\web\UrlRule;

class Module extends \yii\base\Module implements BootstrapInterface
{
    public $controllerNamespace = __NAMESPACE__ . '\controllers';

    public $defaultRoute = 'import';

    public const EVENT_INIT = 'init';

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app): void
    {
        /** @var $app Application */
        $app->getUrlManager()->addRules([
            [
                'class'   => UrlRule::class,
                'route'   => $this->id . '/import/<action>',
                'pattern' => $this->id . '/<action:[\w\-]+>',
            ],
        ], false);
    }

    public function init(): void
    {
        if (!isset(Yii::$app->i18n->translations['import'])) {
            Yii::$app->i18n->translations['import'] = [
                'class'            => PhpMessageSource::class,
                'forceTranslation' => true,
                'basePath'         => '@vendor/kuainiu/import/src/messages',
            ];
        }
        $event = new Event();
        $this->trigger(self::EVENT_INIT, $event);
    }
}
