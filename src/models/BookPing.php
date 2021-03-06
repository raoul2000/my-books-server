<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\migrations\TableName;

/**
 * This is the model class for table "{{%book_ping}}".
 *
 * @property int $id
 * @property string $book_id
 * @property boolean $is_boarding
 * @property string $email
 * @property integer $rate
 * @property string $location_name
 * @property string $user_ip
 * @property string $text
 * @property integer|null $created_at
 * @property integer|null $updated_at
 * 
 * @property Book $book
 */
class BookPing extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return TableName::BOOK_PING;
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
            [['book_id' ], 'required'],
            [['is_boarding'], 'boolean'],
            [['book_id'], 'string', 'max' => 40],
            [['book_id'], 'exist', 'skipOnError' => true, 'targetClass' => Book::class, 'targetAttribute' => ['book_id' => 'id']],
            [['user_ip'], 'string', 'max' => 50],
            [['text'], 'string'],
            [['rate'], 'integer'],            
            [['location_name', 'email'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'book_id' => 'Book ID',
            'is_boarding' => 'Boarding',
            'email' => "Email",
            'rate' => 'Rate',
            'text' => 'Text',
            'location_name' => 'Location',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'user_ip' => 'User IP'
        ];
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
}
