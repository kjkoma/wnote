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
namespace App\Controller\Component;

/**
 * 製品（Products）へのアクセス用コンポーネント
 *  
 * ※データ取得時は親コンポーネントのtableメソッドを利用し、ドメイン指定でデータ取得すること
 * 
 */
class ModelProductsComponent extends AppModelComponent
{
    /**
     * 本クラスの初期化処理を行う
     *  
     * - - -
     * @param array $config コンフィグ
     * @return void
     */
    public function initialize(array $config)
    {
        $config['modelName'] = 'Products';
        parent::initialize($config);
    }

    /**
     * 指定された分類の製品を取得する
     *  
     * - - -
     * @param integer $classificationId 分類ID
     * @param boolean $toArray true:配列で返す|false:ResultSetで返す（default）
     * @return array 製品一覧
     */
    public function findByClassificationId($classificationId, $toArray = false)
    {
        $query = $this->modelTable->find('sorted')
            ->where(['classification_id' => $classificationId]);

        return ($toArray) ? $query->toArray() : $query->all();
    }

    /**
     * 組織指定でビューのツリーノード表示用のユーザー一覧を取得する
     *  
     * - - -
     * @param integer $classificationId 分類ID
     * @return array 製品一覧
     */
    public function treeNode($classificationId)
    {
        $list = $this->findByClassificationId($classificationId);

        return $this->makeTreeArray($list);
    }

    /**
     * ビューのツリーノード表示形式の配列を作成する（製品ノード用）
     *  
     * - - -
     * @param array $list 製品一覧のResultSetオブジェクト
     * @return array ツリーノード表示形式の配列
     */
    public function makeTreeArray($list) {
        $result = [];
        foreach($list as $item) {
            array_push($result, [
                'id'                => $item['id'],
                'kname'             => $item['kname'],
                'classification_id' => $item['classification_id']
            ]);
        }

        return $result;
    }

    /**
     * 製品を検索する（kname or name）
     *  
     * - - -
     * @param string $search 検索文字列
     * @param string $classificationId 分類ID
     * @return array 製品一覧（select2用id/textペア）
     */
    public function find2List($search, $classificationId = null)
    {
        $query = $this->modelTable->find('valid');

        if (!is_null($search) && $search !== '' && $search != '*') {
            $query->andWhere(function($exp) use ($search) {
                return $exp->or_([
                    'kname like ' => '%' . $search . '%',
                    'name like ' => '%' . $search . '%',
                ]);
            });
        }

        if (!is_null($classificationId) && $classificationId !== '') {
            $query->andWhere([
                'classification_id' => $classificationId
            ]);
        }
        $list = $query->all();

        $products = [];
        foreach($list as $item) {
            array_push($products, [
                'id'   => $item['id'],
                'text' => $item['kname']
            ]);
        }

        return $products;
    }

    /**
     * 指定された製品IDと分類IDのデータが存在するかどうかを検証する
     *  
     * - - -
     * @param integer $productId 製品ID
     * @param integer $classificationId 分類ID
     * @return boolean true:正常/false:異常（指定された製品と分類に関連性がない）
     */
    public function validateProductAndClassification($productId, $classificationId)
    {
        $count = $this->modelTable->find('valid')
            ->where([
                'id' => $productId,
                'classification_id' => $classificationId
            ])
            ->count();

        return ($count == 0) ? false : true;
    }

}
