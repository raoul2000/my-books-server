<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use Da\QrCode\QrCode;
use yii\helpers\Url;
use app\migrations\TableName;

/**
 * This is the model class for table "book_ticket".
 *
 * @property string $id
 * @property int $user_id
 * @property string $book_id
 * @property integer|null $created_at
 * @property integer|null $updated_at
 * @property DateTime|null $departure_at
 * @property string|null $from
 * @property Book $book
 * @property User $user
 */
class BookTicket extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return TableName::BOOK_TICKET;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [[
            'class' => TimestampBehavior::class
        ]];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'book_id'], 'required'],
            [['user_id'], 'integer'],
            [['id'], 'safe', 'when' => function ($model) {
                return Yii::$app->user->can('administrate');
            }],
            [['from'], 'safe'],
            [
                ['departure_at'], 'datetime', 'format' => Yii::$app->formatter->datetimeFormat,   // 'php:Y-m-d H:i:s'
                'message' => 'Format de date invalide : YYYY-MM-JJ hh:mm:ss'
            ],  
            [['book_id'], 'string', 'max' => 40],
            [['book_id'], 'exist', 'skipOnError' => true, 'targetClass' => Book::class, 'targetAttribute' => ['book_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'book_id' => 'Book ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'departure_at' => 'Departure At',
            'from' => 'From',
        ];
    }
    public function afterDelete()
    {
        if (file_exists($this->getQrCodeFilePath())) {
            unlink($this->getQrCodeFilePath());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert && !isset($this->id)) {
            // TODO: handle edge case
            $maxTries = 5;
            for ($i = 0; $i < $maxTries; $i++) {
                $id = $this->generateId();
                if (BookTicket::findOne($id) === null) {
                    $this->id = $id;
                    break;
                } // TODO: id collision should be reported (log or elsewhere)
            }
            if (!isset($this->id)) {
                
                throw new \Exception("failed to generate Id");
            }
        }
        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->createQrCode();
        }
    }
    /**
     * @return string - the absolute file path of the QRCode
     */
    public function getQrCodeFilePath()
    {
        return Yii::getAlias('@qrcodePath/' . $this->id . '.png');
    }
    /**
     * @return string - URL of the QRCode
     */
    public function getQrCodeUrl()
    {
        return Url::to(['/account/book-qr-code', 'id' => $this->id] , true);
    }

    /**
     * Create and save the QR code image
     * The QR code content is the URL of the ping form for this book
     */
    private function createQrCode()
    {
        // see also parameter 'bookPingUrl' in src\config\params.php
        $pingReviewUrl = Url::to(['/book-ping', 'id' => $this->id], true);

        $qrCode = (new QrCode($pingReviewUrl))
            ->setSize(150)
            ->setMargin(5);
        $qrCode->writeFile($this->getQrCodeFilePath());
    }
    /**
     * Gets query for [[Book]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBook()
    {
        return $this->hasOne(Book::class, ['id' => 'book_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function generateId()
    {
        srand((float) microtime() * 1000000);
        $char = [
            'A', 'B', 'C', 'D', 'E',
            'F', 'G', 'H', 'J', 'K',
            'L', 'M', 'N', 'P', 'Q',
            'R', 'S', 'T', 'U', 'V',
            'X', 'Y', 'Z',
            '2', '3', '4', '5', '6',
            '7', '8', '9'
        ];
        $maxVal = count($char) - 1;
        $idLength = 6;
        $id = '';
        for ($i = 1; $i <= $idLength; $i++) {
            $id .= $char[rand(0, $maxVal)];
            if (strlen($id) === intdiv($idLength, 2)) {
                $id .= '-';
            }
        }
        return $id;
    }
    public function extraFields()
    {
        return ['book'];
    }
    /**
     * Hides sensitive fields so they are not exposed to REST API
     */
    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information
        unset($fields['user_id']);

        $fields['qrcode_url'] = function ($model) {
            return $model->getQrCodeUrl();
        };
        $fields['departure_at'] = function ($model) {
            $date = new \DateTime($model->departure_at, new \DateTimeZone('UTC'));
            return $date->format(\DateTimeInterface::ISO8601);
        };
        $fields['checkpoint_url'] = function ($model) {
            return Yii::$app->params['checkpointUrl'];
        };
        return $fields;
    }
}
