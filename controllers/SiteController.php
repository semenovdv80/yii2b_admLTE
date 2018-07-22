<?php

namespace app\controllers;

use app\models\SignupForm;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
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
                'class' => AccessControl::className(),
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
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
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
     * Register new user
     *
     * @return string|Response
     */
    public function actionSignup()
    {
        $model = new SignupForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {

                // Send the email:
                $mail = Yii::$app->mailer
                    ->compose('account_active', ['code' => $user->auth_key])
                    ->setFrom('from@domain.com')
                    ->setTo($user->email)
                    ->setSubject('Activate account')
                    ->send();

                if ($mail) {
                    return $this->render('waiting', [
                        'message' => 'Check your email for letter with activation code']);
                } else {
                    $this->goHome();
                }
                /*
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
                */
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Active user account
     *
     * @return Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionActive()
    {
        $authKey = Yii::$app->request->get('code');
        if (!$authKey) {
            Yii::$app->session->setFlash('error', "Active code not found!");
            return $this->goHome();
        }

        $user = User::findByAuthKey($authKey);
        if (!$user) {
            Yii::$app->session->setFlash('error', "User not found!");
            return $this->goHome();
        }

        $user->status = User::STATUS_ACTIVE;
        $user->update();

        if (Yii::$app->getUser()->login($user)) {
        } else {
            Yii::$app->session->setFlash('error', "Can't enter!");
        }
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
}
