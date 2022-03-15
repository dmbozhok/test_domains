<?php
/**
 * @var $this \yii\web\View
 * @var $model \app\models\DnsChange
 * @var $result bool|null
 */

use yii\bootstrap4\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'dns-update-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
    'method' => 'post',
]);
?>
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
        <div class="col-12">
            <?= $form->field($model, 'domain_id')->dropdownList(\app\models\Domain::listActiveDomains(), [
                'prompt' => 'Выберите домен', 'class' => 'form-control',
            ])->label('Домен') ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'ns1')->textInput(['class' => 'form-control ns-control'])->label('NS 1') ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'ns2')->textInput(['class' => 'form-control ns-control'])->label('NS 2') ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'ns3')->textInput(['class' => 'form-control ns-control'])->label('NS 3') ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'ns4')->textInput(['class' => 'form-control ns-control'])->label('NS 4') ?>
        </div>
    </div>
    <button class="btn btn-success" id="update-domain-ns">Отправить</button>
<?php ActiveForm::end(); ?>