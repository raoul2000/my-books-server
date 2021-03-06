<?php

namespace app\modules\api\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;
use app\models\Book;
use app\models\UserBook;
use yii\data\ActiveDataProvider;
use app\components\helpers\ValidationErrorHelper;

class UserBookController extends Controller
{
    use \app\modules\api\controllers\ControllerBehaviorTrait;

    protected function verbs()
    {
        return [
            'index'  => ['GET', 'HEAD', 'OPTIONS'],
            'view'   => ['GET', 'HEAD', 'OPTIONS'],
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
            'pagination' => false,
            'query'      => UserBook::find()
                ->with('book')
                ->where([
                    'user_id' => Yii::$app->user->getId()
                ])
                ->orderBy('updated_at DESC')
        ]);
    }

    /**
     * Create a book and link it to the current user
     */
    public function actionCreate()
    {
        $book = new Book();
        $params = Yii::$app->getRequest()->getBodyParams();

        if ($book->load($params['book'], '') && $book->save()) {
            $userBook = new UserBook();
            $userBook->load($params['userBook'], '');
            $userBook->setAttributes([
                'user_id' => Yii::$app->user->getId(),
                'book_id' => $book->id
            ]);

            if ($userBook->save()) {
                $response = Yii::$app->getResponse();
                $response->setStatusCode(201);

                return $userBook;
            } else {
                $book->delete();    // rollback : delete book
                throw new ServerErrorHttpException('Failed to create user book : '
                    . ValidationErrorHelper::mergeErrorMessages($userBook->getErrors()));
            }
        } else {
            throw new ServerErrorHttpException('Failed to create book : '
                . ValidationErrorHelper::mergeErrorMessages($book->getErrors()));
        }
    }

    /**
     * Return UserBook and if the expand=book query parameter is set,
     * the related Book record
     */
    public function actionView($id)
    {
        return $this->findUserbookModel($id);;
    }

    public function actionUpdate($id)
    {
        $userBook = $this->findUserbookModel($id);

        // FIXME: book id (and all other primary keys) should be protected from user updates

        $book = $userBook->book;
        $params = Yii::$app->getRequest()->getBodyParams();
        $updateFn = [];
        if (isset($params['book'])) {
            if ($book->is_traveling === 1) {
                throw new ServerErrorHttpException("can't update a traveling book");
            }
            $book->load($params['book'], '');
            if ($book->validate()) {
                $updateFn[] = function () use ($book) {
                    $book->update();
                };
            } else {
                throw new ServerErrorHttpException('Failed to update book :'
                    . ValidationErrorHelper::mergeErrorMessages($book->getErrors()));
            }
        }

        if (isset($params['userBook'])) {
            $userBook->load($params['userBook'], '');
            if ($userBook->validate()) {
                $updateFn[] = function () use ($userBook) {
                    $userBook->update();
                };
            } else {
                throw new ServerErrorHttpException('Failed to update user book : '
                    . ValidationErrorHelper::mergeErrorMessages($userBook->getErrors()));
            }
        }
        // apply updates
        foreach ($updateFn as $update) {
            $update();
        }
        // success response
        $response = Yii::$app->getResponse();
        $response->setStatusCode(201);
        return $userBook;
    }

    /**
     * Deletes the UserBook and the related Book records for a given book id
     * and the current user.
     */
    public function actionDelete($id)
    {
        $userBook = $this->findUserbookModel($id);
        // RULE: userbook refering to a traveling book cannot be deleted
        if ($userBook->book->is_traveling === 1) {
            throw new ServerErrorHttpException("Can't delete a traveling book.");
        } else {
            $userBook->delete();
            $userBook->book->delete();
        }
    }

    /**
     * Find UserBook by Id and for the current user or throws if not found
     */
    private function findUserbookModel($id)
    {
        $userBook = UserBook::find()
            ->where([
                'user_id' => Yii::$app->user->getId(),
                'book_id' => $id
            ])
            ->with('book')
            ->one();

        if (!$userBook) {
            throw new NotFoundHttpException("Object not found");
        }
        return $userBook;
    }
}
