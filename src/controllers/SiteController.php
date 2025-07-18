<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        // Отключаем Bootstrap, jQuery, yii.js для всех действий контроллера
        \Yii::$app->assetManager->bundles['yii\web\JqueryAsset'] = false;
        \Yii::$app->assetManager->bundles['yii\bootstrap\BootstrapAsset'] = false;
        \Yii::$app->assetManager->bundles['yii\bootstrap5\BootstrapAsset'] = false;
        \Yii::$app->assetManager->bundles['yii\bootstrap5\BootstrapPluginAsset'] = false;
        \Yii::$app->assetManager->bundles['yii\web\YiiAsset'] = false; // отключаем yii.js и css-файлы yii

        if ($action->id !== 'error' && isset($_GET['r'])) {
            throw new \yii\web\NotFoundHttpException('Page not found.');
        }

        $path = \Yii::$app->request->pathInfo;

        if (
            $action->id !== 'error' &&
            $action->id !== 'captcha' &&
            strpos($path, 'site/') === 0
        ) {
            $newUrl = substr($path, strlen('site/'));
            return \Yii::$app->response->redirect('/' . $newUrl, 301)->send();
        }

        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
    
    public function actionDbTest()
    {
        try {
            Yii::$app->db->createCommand('SELECT 1')->queryScalar();
            return '✅ Соединение с базой установлено!';
        } catch (\yii\db\Exception $e) {
            return '❌ Ошибка соединения: ' . $e->getMessage();
        }
    }

}
