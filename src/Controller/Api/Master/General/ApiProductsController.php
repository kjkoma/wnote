<?php
/**
 * Copyright (c) Japan Computer Services, Inc.
 *
 * Licensed under The MIT License
 *
 * @author    Japan Computer Services, Inc
 * @copyright Copyright (c) Japan Computer Services, Inc. (http://www.japacom.co.jp)
 * @since     1.0.0
 * @version   1.0.0
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 *
 * -- Histories --
 * 2017.12.31 R&D 新規作成
 */
namespace App\Controller\Api\Master\General;

use App\Controller\Api\ApiController;
use Exception;

/**
 * Products API Controller
 *
 */
class ApiProductsController extends ApiController
{
    /**
     * 本クラスの初期化処理を行う
     *  
     * - - -
     */
    public function initialize()
    {
        parent::initialize();
        $this->_loadComponent('ModelProducts');
        $this->_loadComponent('ModelClassifications');
    }

    /**
     * 指定された製品IDの製品情報を取得する
     *
     */
    public function product()
    {
        $data = $this->validateParameter('product_id', ['post']);
        if (!$data) return;

        // 製品を取得
        $product = $this->ModelProducts->get($data['product_id']);
        if (!$product) {
            $this->setError('指定されたデータがありません。', 'NOT_FOUND_PRODUCT', $data, 404);
            return;
        }

        // 分類名称を取得・付加（ビューのselect2選択BOX用）
        $classification = $this->ModelClassifications->get($product['classification_id']);
        if (!$classification) {
            $this->setError('指定された製品データに関連する分類情報がありません。', 'NOT_FOUND_CLASSIFICATION', $data, 404);
            return;
        }
        $product['classification_text'] = $classification['kname'];

        // レスポンスメッセージの作成
        $this->setResponse(true, 'your request is success', [
            'product' => $product
        ]);
    }

    /**
     * 送信された製品データを登録する
     *
     */
    public function add()
    {
        $data = $this->validateParameter('product', ['post']);
        if (!$data) return;

        $product = $data['product'];
        $product['domain_id'] = $this->AppUser->current();

        // トランザクション開始
        $this->ModelProducts->begin();

        try {
            // 分類を保存
            $newProduct = $this->ModelProducts->add($product);
            $this->AppError->result($newProduct);

            // エラー判定
            if ($this->AppError->has()) {
                // ロールバック
                $this->ModelProducts->rollback();
                $this->setResponseError('your request is failure.', $this->AppError->errors());
                return;
            }

            // コミット
            $this->ModelProducts->commit();

        } catch(Exception $e) {
            // ロールバック
            $this->ModelProducts->rollback();
            $this->setError('保存時に予期せぬエラーが発生しました。管理者へお問い合わせください。', 'UNEXPECTED_EXCEPTION', $e);
            return;
        }

        // レスポンスメッセージの作成
        $this->setResponse(true, 'your request is success', ['product' => $newProduct['data']]);
    }

    /**
     * 送信された製品データを更新する
     *
     */
    public function edit()
    {
        $data = $this->validateParameter('product', ['post']);
        if (!$data) return;

        $product  = $data['product'];

        // トランザクション開始
        $this->ModelProducts->begin();

        try {
            // 分類を保存
            $updateProduct = $this->ModelProducts->save($product);
            $this->AppError->result($updateProduct);

            // エラー判定
            if ($this->AppError->has()) {
                // ロールバック
                $this->ModelProducts->rollback();
                $this->setResponseError('your request is failure.', $this->AppError->errors());
                return;
            }

            // コミット
            $this->ModelProducts->commit();

        } catch(Exception $e) {
            // ロールバック
            $this->ModelProducts->rollback();
            $this->setError('保存時に予期せぬエラーが発生しました。管理者へお問い合わせください。', 'UNEXPECTED_EXCEPTION', $e);
            return;
        }

        // レスポンスメッセージの作成
        $this->setResponse(true, 'your request is success', ['product' => $updateProduct['data']]);
    }

    /**
     * 指定された製品IDの製品データを削除する
     *
     */
    public function delete()
    {
        $data = $this->validateParameter('id', ['post']);
        if (!$data) return;

        $productId = $data['id'];

        // トランザクション開始
        $this->ModelProducts->begin();

        try {
            // 製品を削除(TableのDependencyを利用して依存データを削除)
            $deleteProduct = $this->ModelProducts->delete($productId);
            $this->AppError->result($deleteProduct);

            // エラー判定
            if ($this->AppError->has()) {
                // ロールバック
                $this->ModelProducts->rollback();
                $this->setResponseError('your request is failure.', $this->AppError->errors());
                return;
            }

            // コミット
            $this->ModelProducts->commit();

        } catch(Exception $e) {
            // ロールバック
            $this->ModelProducts->rollback();
            $this->setError('保存時に予期せぬエラーが発生しました。管理者へお問い合わせください。', 'UNEXPECTED_EXCEPTION', $e);
            return;
        }

        // レスポンスメッセージの作成
        $this->setResponse(true, 'your request is succeed', ['product' => $deleteProduct['data']]);
    }

}
