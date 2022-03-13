<?php
/**
 * @var $this \yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

use app\models\DnsChange;
use yii\grid\GridView;

?>

<div class="mb-3">
    <a class="btn btn-success" href="/dns/update">Создать запрос</a>
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
                 * @var $model DnsChange
                 */
                return $model->id;
            },
        ],
        [
            'header' => 'Дата',
            'format' => 'html',
            'value' => function ($model) {
                /**
                 * @var $model DnsChange
                 */
                return date('d.m.Y H:i:s', $model->time);
            },
            'headerOptions' => ['class' => 'text-center align-middle'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'header' => 'Название',
            'format' => 'html',
            'value' => function ($modelChange) {
                /**
                 * @var $modelChange DnsChange
                 */
                $model = $modelChange->domain;
                return $model->name . ($model->nameIdn != $model->name ? '<br> ' . $model->nameIdn : '');
            },
        ],
        [
            'header' => 'NS сервера',
            'format' => 'html',
            'value' => function ($modelChange) {
                /**
                 * @var $modelChange DnsChange
                 */
                $result = '';
                if ($modelChange->ns1) {
                    $result .= $modelChange->ns1 . '<br>';
                }
                if ($modelChange->ns2) {
                    $result .= $modelChange->ns2 . '<br>';
                }
                if ($modelChange->ns3) {
                    $result .= $modelChange->ns3 . '<br>';
                }
                if ($modelChange->ns4) {
                    $result .= $modelChange->ns4 . '<br>';
                }
                return $result;
            },
        ],
        [
            'header' => 'Статус',
            'format' => 'raw',
            'value' => function ($model) {
                /**
                 * @var $model DnsChange
                 */
                $result = DnsChange::$statuses[$model->status] ?? $model->status;
                if ($model->status == DnsChange::STATUS_SENT) {
                    $result .= '<button class="btn btn-info ml-2 update-status" data-href="/dns/update-status" data-id="' . $model->id . '">Обновить</button>';
                }
                return $result;
            },
            'contentOptions' => ['class' => 'text-left pl-4'],
            'headerOptions' => ['class' => 'pl-4'],
        ],
    ],
]); ?>