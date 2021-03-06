<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for the Twitter bootstrap css files.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AdminLtePluginsWysiHtml5Asset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte/plugins/bootstrap-wysihtml5';

    public $js = [
        'bootstrap3-wysihtml5.all.min.js'
    ];

    public $css = [
        'bootstrap3-wysihtml5.min.css'
    ];
}
