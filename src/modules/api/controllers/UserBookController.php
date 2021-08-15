<?php

namespace app\modules\api\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;
use app\models\Book;
use app\models\UserBook;
use yii\data\ActiveDataProvider;

class UserBookController extends Controller
{
    use \app\modules\api\controllers\ControllerBehaviorTrait;

    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
        ];
    }

    /**
     * Returns the list of books belonging to the current user
     */
    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' =>  UserBook::find()
                ->with('book')
                ->where(['user_id' => Yii::$app->user->getId()])
        ]);
    }

    /**
     * Create a book and link it to the current user
     */
    public function actionCreate()
    {
        $book = new Book();

        $params = Yii::$app->getRequest()->getBodyParams();
        $book->load($params['book'],'');
        if ($book->load($params['book'],'') && $book->save()) {
            $book->refresh();           // update timestamp attributes

            $userBook = new UserBook();
            $userBook->load($params['userBook'],'');
            $userBook->setAttributes([
                'user_id' => Yii::$app->user->getId(),
                'book_id' => $book->id
            ]);

            if ($userBook->save()) {
                $userBook->refresh();   // update timestamp attributes

                $response = Yii::$app->getResponse();
                $response->setStatusCode(201);

                return [
                    'book'     => $book,
                    'userBook' => $userBook
                ];
            } else {
                $book->delete();    // rollback : delete book
                throw new ServerErrorHttpException('Failed to create user book');
            }
        } else {
            throw new ServerErrorHttpException('Failed to create book.');
        }
    }

    /**
     * Return UserBook and if the expand=book query parameter is set,
     * the related Book record
     */
    public function actionView($id)
    {
        $userBook = UserBook::find()
            ->with('book')
            ->where(['user_id' => Yii::$app->user->getId()])
            ->andWhere(['book_id' => $id])
            ->one();

        if (!$userBook) {
            throw new NotFoundHttpException("Object not found");
        }

        return $userBook;
    }

    public function actionUpdate($id)
    {
        $userBook = UserBook::find()
            ->where(['user_id' => Yii::$app->user->getId()])
            ->andWhere(['book_id' => $id])
            ->with('book')
            ->one();

        if (!$userBook) {
            throw new NotFoundHttpException("Object not found");
        }

        // FIXME: book id (and all other primary keys) should be protected from user updates
        // TODO: allow user to update userBook model

        $book = $userBook->book;

        $params = Yii::$app->getRequest()->getBodyParams();
        if (isset($params['book'])) {
            $book->load($params['book'], '');
            if ($book->validate()) {
                $book->update();
            } else {
                throw new ServerErrorHttpException('Failed to update book.');
            }
        }

        if (isset($params['userBook'])) {
            $userBook->load($params['userBook'], '');
            if ($userBook->validate()) {
                $userBook->update();
            } else {
                throw new ServerErrorHttpException('Failed to update user-book.');
            }
        }
        $response = Yii::$app->getResponse();
        $response->setStatusCode(201);

    }

    /**
     * Deletes the UserBook and the related Book records for a given book id
     * and the current user.
     */
    public function actionDelete($id)
    {
        $userBook = UserBook::find()
            ->where(['user_id' => Yii::$app->user->getId()])
            ->andWhere(['book_id' => $id])
            ->with('book')
            ->one();

        if ($userBook) {
            $userBook->delete();
            $userBook->book->delete();
        } else {
            throw new NotFoundHttpException("Object not found");
        }
    }
}
