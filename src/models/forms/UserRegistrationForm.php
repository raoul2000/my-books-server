<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\User;
use app\models\UserToken;
use app\components\PasswordValidator;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user This property is read-only.
 *
 */
class UserRegistrationForm extends Model
{
    private $user_id;
    private $account_activation_token;
    public $username;
    public $email;
    public $password;
    public $password_confirm;
    public $verifyCode;
    public $status;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'password', 'password_confirm', 'email'], 'required', 
                'message' => 'veuillez saisir une valeur'],
            ['username', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'username',
                'message' => 'ce nom est déjà utilisé'],
            ['email', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email',
                'message' => 'cette adresse email est déjà enregistrée'],
            ['email', 'email',
                'message' => 'adresse email invalide'],
            ['password', PasswordValidator::class],
            ['password_confirm', 'compare', 'compareAttribute' => 'password',
                'message' => 'mot de passe différent'],
            ['verifyCode', 'captcha', 'when' => function($model) {
                return Yii::$app->user->can('administrate') === false && Yii::$app->params['enableVerifyCodeOnCreateAccount'];
            }],
            ['status', 'required', 'when' => function($model) {
                return Yii::$app->user->can('administrate');
            }]
        ];
    }
    public function attributeLabels()
    {
        return [
            'username' => 'Pseudo',
            'password' => 'Mot de passe',
            'password_confirm' => 'Mot de passe (confirmation)',
            'email' => 'Adresse Email',
            'verifyCode' => 'Code de Vérification',
        ];
    }
    public function getUserId()
    {
        return $this->user_id;
    }

    public function getAccountActivationToken()
    {
        return $this->account_activation_token;
    }

    /**
     * Register a user using the provided data.
     * @return bool registration failure or success
     */
    public function register()
    {
        $success = false;
        if ($this->validate()) {

            $user = new User();
            
            $user->setScenario(User::SCENARIO_REGISTER);

            $user->username     = $this->username;
            $user->email        = $this->email;
            $user->new_password = $this->password;

            if(Yii::$app->user->can('administrate')){
                $user->status   = $this->status;
            } else {
                // default status may be overridden by initUserToken method
                $user->status    = User::STATUS_ACTIVE;
            }

            if($user->save()) {
                $this->user_id = $user->id; // expose to caller
                if(Yii::$app->user->can('administrate') === false) {
                    $success = $this->initUserToken($user);
                    if(!$success) {
                        $user->delete();
                    }
                } else {
                    $success = true;
                }
            } else {
                $this->addErrors($user->getErrors());
            }
        }

        if(!$success) {
            $this->account_activation_token = '';
            $this->password = '';
            $this->password_confirm = '';
        }
        return $success;
    }

    private function initUserToken($user) 
    {
        if (Yii::$app->params['enableAccountActivation']) {
            // account must be activated : create activation token and 
            // update 'status' attribute
            $user->status = User::STATUS_INACTIVE;
            $userToken    = UserToken::generate($user->id, UserToken::TYPE_EMAIL_ACTIVATE);
            $this->account_activation_token = $userToken->token;
            $success = $user->update(true, ['status']) === 1;
        } else {
            // no account activation step : create API Key now
            UserToken::generate($this->user_id, UserToken::TYPE_API_KEY);
            $success = true;
        }     
        return $success;   
    }
}
