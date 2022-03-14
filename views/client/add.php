<?php
/**
 * @var $this \yii\web\View
 * @var $model \app\models\Client
 * @var $result bool|null
 */

use yii\bootstrap4\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'client-add-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
    'method' => 'post',
]);
if (isset($result)) {
    if ($result === true) {
        echo '<div class="alert alert-success">Запрос на регистрацию отправлен</div>';
    } elseif ($result === false) {
        echo '<div class="alert alert-danger">Не удалось отправить запрос или запрос не прошел успешно</div>';
    }
}
if (is_array($model->errors) && count($model->errors)) {
    echo $form->errorSummary($model);
}
?>
    <h2>Анкета для граждан РФ</h2>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'nameLocal',[])->textInput(['class' => 'form-control']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'birthday')->input('date', ['class' => 'form-control']); ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'emails',[])->textInput(['class' => 'form-control']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'phones',[])->textInput(['class' => 'form-control']) ?>
        </div>
        <div class="col-12 mt-3"><h3>Адрес места жительства</h3></div>
        <div class="col-md-4">
            <?= $form->field($model, 'addressLocalIndex',[])->textInput(['class' => 'form-control']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'addressLocalCountry',[])->textInput(['class' => 'form-control']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'addressLocalRegion',[])->textInput(['class' => 'form-control']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'addressLocalCity',[])->textInput(['class' => 'form-control']) ?>
        </div>
        <div class="col-md-8">
            <?= $form->field($model, 'addressLocalStreet',[])->textInput(['class' => 'form-control']) ?>
        </div>
        <div class="col-12 mt-3"><h3>Паспортные данные</h3></div>
        <div class="col-md-2">
            <?= $form->field($model, 'identitySeries',[])->textInput(['class' => 'form-control']) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'identityNumber',[])->textInput(['class' => 'form-control']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'identityIssuer',[])->textInput(['class' => 'form-control']) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'identityIssued',[])->input('date', ['class' => 'form-control']) ?>
        </div>
    </div>
    <button class="btn btn-success" id="add-client">Добавить</button>
<?php ActiveForm::end(); ?>