<?php

namespace koperdog\yii2nsblog\frontend;

class Module extends \yii\base\Module
{
    const MODULE_NAME = "nsblog";
    
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'koperdog\yii2nsblog\frontend\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        $this->registerTranslations();
    }
    
    private function registerTranslations()
    {
        if (!isset(\Yii::$app->i18n->translations[self::MODULE_NAME . '*'])) {
            
            \Yii::$app->i18n->translations[self::MODULE_NAME . '*'] = [
                'class'    => \yii\i18n\PhpMessageSource::class,
                'basePath' => dirname(__DIR__) . '/messages',
                'fileMap'  => [
                    self::MODULE_NAME . "/error" => "error.php", 
                ],
            ];
        }
    }
}
