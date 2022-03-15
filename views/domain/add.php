<?php
/**
 * @var $this \yii\web\View
 * @var $model \app\models\Domain
 * @var $result bool|null
 */

use yii\bootstrap4\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'domain-add-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
    'method' => 'post',
]); ?>
<?php if (isset($result)) { ?>
    <?php if ($result === false) { ?>
        <div class="alert alert-danger">Не удалось отправить запрос или запрос не прошел успешно</div>
    <?php } elseif ($result === true) { ?>
        <div class="alert alert-success">Запрос на регистрацию отправлен</div>
    <?php } ?>
<?php } ?>
<?php if (is_array($model->errors) && count($model->errors)) { ?>
    <?= $form->errorSummary($model); ?>
<?php } ?>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'name',[])->textInput([
                'class' => 'form-control'
            ])->label('Домен') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'client_id')->dropdownList(\app\models\Client::listActiveClients(), [
                'prompt' => 'Выберите анкету клиента', 'class' => 'form-control',
            ])->label('Клиент') ?>
        </div>
    </div>
    <button class="btn btn-success" id="add-domain">Добавить</button>
<?php ActiveForm::end(); ?>
