<?php
/**
 * @var $this \yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

use app\models\Client;
use yii\grid\GridView;

?>

    <div class="mb-3">
        <a class="btn btn-success" href="/client/add">Добавить анкету</a>
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
                 * @var $model \app\models\Client
                 */
                return $model->id;
            },
        ],
        [
            'header' => 'Имя',
            'format' => 'html',
            'value' => function ($model) {
                /**
                 * @var $model Client
                 */
                return $model->nameLocal . '<br>' . $model->birthday;
            },
            'headerOptions' => ['class' => 'text-center align-middle'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'header' => 'Данные',
            'format' => 'html',
            'value' => function ($model) {
                /**
                 * @var $model Client
                 */
                return 'Email: ' . $model->emails . '<br>' . 'Телефон: ' . $model->phones . '<br>';
            },
        ],
        [
            'header' => 'Статус',
            'format' => 'raw',
            'value' => function ($model) {
                /**
                 * @var $model Client
                 */
                $result = Client::$statuses[$model->status] ?? $model->status;
                if ($model->status == Client::STATUS_SENT) {
                    $result .= '<button class="btn btn-info ml-2 update-status" data-href="/client/update-status" data-id="' . $model->id . '">Обновить</button>';
                }
                return $result;
            },
            'contentOptions' => ['class' => 'text-left pl-4'],
            'headerOptions' => ['class' => 'pl-4'],
        ],
    ],
]); ?>