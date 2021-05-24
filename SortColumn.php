<?php

/**
 * @copyright Copyright (c) 2018 Ivan Orlov
 * @license   https://github.com/demisang/yii2-sort/blob/master/LICENSE
 * @link      https://github.com/demisang/yii2-sort#readme
 * @author    Ivan Orlov <gnasimed@gmail.com>
 */

namespace demi\sort;

use Closure;
use yii\db\ActiveRecord;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * GridView Column for change sort of model
 *
 * @package demi\sort
 */
class SortColumn extends ActionColumn
{
    /** @inheritdoc */
    public $template = '{up} {down} {pin}';

    /** @var string Name of action for handle sort changing */
    public $action = 'change-sort';

    /** @inheritdoc */
    public $buttonOptions = ['class' => 'btn btn-default btn-sm'];

    /** @inheritDoc */
    public $header = '';

    /** @inheritDoc */
    public $disableButtons = [];

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        $this->contentOptions['style'] = 'text-align: right;white-space: nowrap;' . (isset($this->contentOptions['style']) ? ' ' . $this->contentOptions['style'] : '');

        $this->visibleButtons = [
            'pin' => function ($model, $key, $index) {
                return $model->canPin();
            }
        ];

        $this->disableButtons = [
            'up' => function ($model, $key) {
                return $model->canSort(SortBehavior::DIR_UP);
            },
            'down' => function ($model, $key) {
                return $model->canSort(SortBehavior::DIR_DOWN);
            },
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('up', 'arrow-up');
        $this->initDefaultButton('down', 'arrow-down');
        $this->initDefaultButton('pin', function ($model, $key) {
            return $model->isSorted() ? 'remove' : 'pushpin';
        });
    }

    /**
     * @inheritdoc
     */
    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                $title = ucfirst($name);

                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0'
                ], $additionalOptions, $this->buttonOptions);

                if (isset($this->disableButtons[$name])) {
                    $options['disabled'] = $this->disableButtons[$name] instanceof Closure
                        ? call_user_func($this->disableButtons[$name], $model, $key)
                        : $this->disableButtons[$name];
                }

                if ($iconName instanceof Closure) {
                    $iconName = call_user_func($iconName, $model, $key);
                }

                $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-$iconName"]);
                return Html::a($icon, $url, $options);
            };
        }
    }

    /**
     * @inheritdoc
     */
    public function createUrl($buttonName, $model, $key, $index)
    {
        if ($this->urlCreator instanceof Closure) {
            return call_user_func($this->urlCreator, $this->action, $model, $key, $index);
        } else {
            $params = is_array($key) ? $key : ['id' => (string)$key];

            // set sort direction param
            if ($buttonName == 'up') {
                $params['direction'] = SortBehavior::DIR_UP;
            } elseif ($buttonName == 'down') {
                $params['direction'] = SortBehavior::DIR_DOWN;
            }

            $params[0] = $this->controller ? $this->controller . '/' . $this->action : $this->action;

            return Url::toRoute($params);
        }
    }
}
