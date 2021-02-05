<?php

namespace import\controllers;

use import\models\ImportCsv;
use import\models\ImportCsvSearch;
use yii\base\UserException;
use yii\web\Controller;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\RangeNotSatisfiableHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class ImportController extends Controller
{

    public function actionIndex(): string
    {
        $searchModel  = new ImportCsvSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionCsv()
    {
        $model       = new ImportCsv();
        $model->type = 0;

        if (Yii::$app->getRequest()->isPost) {
            $session = Yii::$app->session;
            $model->load(Yii::$app->getRequest()->post());
            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->uploadFile()) {
                $session->setFlash('success', '文件上传成功');

                return $this->redirect('index');
            }
        }

        return $this->render('csv', [
            'model' => $model,
        ]);
    }


    /**
     * @param $id
     *
     * @return Response
     * @throws UserException
     * @throws RangeNotSatisfiableHttpException
     */
    public function actionDownload($id): Response
    {
        $model = $this->findModel($id);
        $file  = $model->download();
        if (!is_null($file)) {
            return Yii::$app->getResponse()->sendStreamAsFile(fopen($file, 'rb'), $model->file);
        }
        throw new NotFoundHttpException('页面不存在!');
    }


    public function actionDelete($id): Response
    {
        $session = Yii::$app->session;
        $model   = $this->findModel($id);
        if (!in_array($model->status, [ImportCsv::SUCCESS_STATUS, ImportCsv::FAIL_STATUS], true)) {
            $session->setFlash('error', '状态异常');
        } elseif ($model->delete()) {
            $session->setFlash('success', '删除成功');
        } else {
            $session->setFlash('error', '删除失败');
        }

        return $this->redirect('index');
    }


    public function actionUpdate($id): Response
    {
        $session = Yii::$app->session;
        $model   = $this->findModel($id);
        if ($model->updateMakeTask()) {
            $session->setFlash('success', '修改成功!');
        } else {
            $session->setFlash('error', '修改失败!');
        }

        return $this->redirect('index');
    }


    public function actionClean($id): Response
    {
        $session = Yii::$app->session;
        $model   = $this->findModel($id);

        $model->status       = ImportCsv::CLEAN_STATUS;
        $model->success_line = 0;
        if ($model->save(false)) {
            $session->setFlash('success', '操作成功');
        } else {
            $session->setFlash('error', '操作失败');
        }

        return $this->redirect('index');
    }

    protected function findModel(int $id): ImportCsv
    {
        $model = ImportCsv::findOne($id);
        if ($model) {
            return $model;
        }

        throw new UserException('数据不存在!');
    }

}


