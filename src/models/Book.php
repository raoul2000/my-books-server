<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use \thamtech\uuid\helpers\UuidHelper;
use app\migrations\TableName;

/**
 * This is the model class for table "books".
 *
 * @property string $id
 * @property string $title
 * @property string $subtitle
 * @property string|null $author
 * @property string|null $isbn
 * @property boolean $is_traveling
 * @property integer $ping_count
 * @property integer|null $created_at
 * @property integer|null $updated_at
 */
class Book extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return TableName::BOOK;
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
            [['title'], 'required'],
            [['isbn'], 'string', 'max' => 15],
            [['is_traveling'], 'boolean'],
            [['ping_count'], 'integer', 'min' => 0],
            [['title', 'subtitle', 'author'], 'trim'],
            [['title', 'subtitle', 'author'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'title' => 'Title',
            'subtitle' => 'Sub Title',
            'author' => 'Author',
            'isbn' => 'ISBN',
            'is_traveling' => 'Is Traveling',
            'ping_count' => 'Ping Count',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->id = UuidHelper::uuid();
        }
        return true;
    }
    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        BookTicket::deleteAll(['book_id' => $this->id]);
        BookPing::deleteAll(['book_id' => $this->id]);
        UserBook::deleteAll(['book_id' => $this->id]);

        return true;
    }
    /**
     * Gets query for [[BookPings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPings()
    {
        return $this->hasMany(BookPing::class, ['book_id' => 'id']);
    }

    /**
     * Gets query for [[BookReviews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReviews()
    {
        return $this->hasMany(BookPing::class, ['book_id' => 'id']);
    }

    /**
     * Gets query for [[UserBooks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserBooks()
    {
        return $this->hasMany(UserBook::class, ['book_id' => 'id']);
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'book_id'])
            ->via('userBooks');
    }
}
