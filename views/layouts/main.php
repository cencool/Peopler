<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\SiteAsset;
use yii\widgets\Breadcrumbs;
use app\widgets\Alert;


SiteAsset::register($this);
$this->beginPage();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        @media screen and (min-width:800px) {
            #collapse-button {
                display: none;
            }
        }
    </style>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>
    <header>
        <nav class='navbar navbar-default navbar-fixed-top '>
            <div class='container'>
                <div class='navbar-header'>
                    <a href=<?= Url::to(['site/index']) ?> class='navbar-brand'><img src='/icicle.png' width='25' height='25'></a>
                    <button id='collapse-button' type="button" class='btn btn-primary navbar-btn' data-toggle="collapse" data-target="#myNavbar">
                        <span class="glyphicon glyphicon-menu-hamburger"></span>
                    </button>
                </div>
                <div class='collapse navbar-collapse' id='myNavbar'>
                    <ul class='nav navbar-nav'>
                        <li>
                            <?= Html::a(Yii::t('app', 'List'), ['person/index']) ?>
                        </li>
                        <li>
                            <?= Html::a(Yii::t('app','Search'), ['person/search']) ?>
                        </li>
                        <li>
                            <?= Yii::$app->request->getRemoteIp() ?> 
                             <!-- <?= 'Ahoj'?>  -->
                        </li>
                    </ul>
                    <ul class='nav navbar-nav navbar-right'>
                        <?php if (Yii::$app->user->isGuest) { ?>
                            <li>
                                <?= Html::a(Yii::t('app', 'Login'), ['site/login']) ?>
                            </li>
                        <?php } else { ?>
                            <li>
                                <?= Html::a(Yii::t('app', 'Logout') . ': ' . Yii::$app->user->id, ['site/logout']) ?>
                            </li>
                        <?php } ?>
                        <li class='dropdown'>
                            <a class='dropdown-toggle' data-toggle='dropdown' href='#'>
                                <?= Yii::t('app', 'Language') . ' ' ?><span class='caret'></span></a>
                            <ul class='dropdown-menu dropdown-menu-right'>
                                <li><a href=<?= Url::current(['lng' => 'sk']) ?>>SK</a></li>
                                <li><a href=<?= Url::current(['lng' => 'en']) ?>>EN</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <div class='container'>
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            'homeLink' => false,
        ]) ?>
        <?= Alert::widget() ?>

        <div class='content'>
            <?= $content ?>
        </div>
    </div>

    <footer>
        <p style='text-align:center;'><b>&copy; 2021 Cencul </b></p>
    </footer>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
