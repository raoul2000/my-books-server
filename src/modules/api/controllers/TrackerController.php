<?php

namespace app\modules\api\controllers;

use Yii;
use app\models\Book;
use app\models\BookPing;
use yii\rest\Controller;
use yii\httpclient\Client;
use yii\web\NotFoundHttpException;

class TrackerController extends Controller
{
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
        ];
    }

    /**
     * Returns the list of books belonging to the current user
     * 
     * @param string $id - the id of the book to track
     */
    public function actionIndex($id)
    {
        $book = Book::find()
            ->where(['id' => $id])
            ->one();

        if (!$book) {
            throw new NotFoundHttpException('book not found');
        }

        $track = BookPing::find()
            ->where(['book_id' => $id])
            ->asArray()
            ->all();

        return [
            'book' => $book,
            'track' => $track
        ];
    }
}