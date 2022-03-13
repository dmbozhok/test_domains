<?php
/**
 * @var $this \yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

use app\models\Domain;
use yii\grid\GridView;

?>
<div class="mb-3">
    <a class="btn btn-success" href="/domain/add">Добавить</a>
</div>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => [
        'class' => 'table table-hover'
    ],
    'pager' => [
        'maxButtonCount' => 5,
        'class' => \yii\bootstrap4\LinkPager::className(),
        'options' => ['class' => 'pagination'],
    ],
    'summary' => '<div class="summary-tbl"><span class="all">Всего: </span><span class="total">{totalCount}</span></div>',
    'columns' => [
        [
            'header' => '№',
            'format' => 'html',
            'value' => function ($model) {
                /**
                 * @var $model Domain
                 */
                return $model->id;
            },
        ],
        [
            'header' => 'Дата',
            'format' => 'html',
            'value' => function ($model) {
                /**
                 * @var $model Domain
                 */
                return date('d.m.Y H:i:s', $model->time);
            },
            'headerOptions' => ['class' => 'text-center align-middle'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'header' => 'Название',
            'format' => 'html',
            'value' => function ($model) {
                /**
                 * @var $model Domain
                 */
                return $model->name . ($model->nameIdn != $model->name ? '<br> ' . $model->nameIdn : '');
            },
        ],
        [
            'header' => 'Статус',
            'format' => 'raw',
            'value' => function ($model) {
                /**
                 * @var $model Domain
                 */
                $result = Domain::$statuses[$model->status] ?? $model->status;
                if ($model->status == Domain::STATUS_SENT) {
                    $result .= '<button class="btn btn-info ml-2 update-status" data-href="/domain/update-status" data-id="' . $model->id . '">Обновить</button>';
                }
                return $result;
            },
            'contentOptions' => ['class' => 'text-left pl-4'],
            'headerOptions' => ['class' => 'pl-4'],
        ],
    ],
]); ?>
