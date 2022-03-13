<?php
/**
 * @var $this \yii\web\View
 * @var $model \app\models\Domain
 * @var $result array
 */

use yii\bootstrap4\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'domain-add-form',
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
    'method' => 'post',
]); ?>
<?php if ($result) { ?>
    <?php if ($result['success'] === false) { ?>
        <?= $form->errorSummary($model); ?>
    <?php } elseif ($result['sent'] === false) { ?>
        <div class="alert alert-danger">Не удалось отправить запрос или запрос не прошел успешно</div>
    <?php } elseif ($result['sent'] === true) { ?>
        <div class="alert alert-success">Запрос на регистрацию отправлен</div>
    <?php } ?>
<?php } ?>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'name',[])->textInput([
                'class' => 'form-control'
            ])->label('Домен') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'emails',[])->textInput([
                'class' => 'form-control'
            ])->label('Email') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'phones',[])->textInput([
                'class' => 'form-control'
            ])->label('Телефон') ?>
        </div>
    </div>
    <button class="btn btn-success" id="add-domain">Добавить</button>
<?php ActiveForm::end(); ?>
