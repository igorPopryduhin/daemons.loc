<?php

namespace Engine\Model\Product;


use Engine\DI\DI;
use Engine\Model;
use Engine\Session\Session;
use Engine\Wxrrd\Wxrrd;
use SafeMySQL;

class Product extends Model
{
    /**
     * @var SafeMySQL
     */
    protected $db;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var Wxrrd
     */
    public $wxrrd;



    public function __construct (DI $di)
    {
        parent::__construct($di);
    }


    public function goodsLists()
    {
        $data = $this->wxrrd->CallAPI('weiba.wxrrd.goods.lists', $this->wxrrd->restBuild());
        return $data;

        if (is_array($data)){
            foreach ($data as $itemData){
                if (is_array($itemData)) {
                    foreach ($itemData as $item){
                        $order_sn  = $item['order_sn'];
                        // Идём на сервер за детальной информацией
                        $dataOrderDetails = $this->tradeDetails($order_sn);
                        if (is_array($dataOrderDetails)){
                            $this->ordersToDataBase($dataOrderDetails['data']);
                        }
                    }
                }
            }
        }
    }


    /**
     * Синхронизация продуктов
     * @param int $offset
     * @return int
     */
    public function goodsListsSyncDataBase($offset = 0)
    {
        $limit = 10;
        $pages = 0;
        $this->wxrrd->setField('limit',  $limit);
        $this->wxrrd->setField('offset',  $offset);
        $data = $this->wxrrd->CallAPI('weiba.wxrrd.goods.lists', $this->wxrrd->restBuild());

        if (is_array($data)){
            if ($data['errCode'] == 0){
                $pages = round($data['count'] / $limit);

                foreach ($data as $itemData){
                    if (is_array($itemData)) {
                        foreach ($itemData as $item){
                            
                            $id         = (int)$item['id'];
                            $title      = $item['title'];
                            $price      = $item['price'];
                            $img        = $item['img'];
                            $stock      = (int)$item['stock'];
                            $csale      = (int)$item['csale'];
                            $is_sku     = (int)$item['is_sku'];
                            $created_at = $item['created_at'];
                            $goods_sn   = $item['goods_sn'];
                            $link_url   = $item['link_url'];

                            $this->db->query("INSERT INTO `product` (`product_id`, `title`, `price_cny`, `img`, `stock`, `csale`, `is_sku`, `create_at`, `goods_sn`, `link_url`) 
                              VALUES (?i, ?s, ?s, ?s, ?i, ?i, ?i, ?s, ?s, ?s) 
                              ON DUPLICATE 
                              KEY UPDATE `product_id`=?i, `title`=?s, `price_cny`=?s, `img`=?s, `stock`=?i, `csale`=?i, `is_sku`=?i, `create_at`=?s, `goods_sn`=?s, `link_url`=?s",

                                $id, $title, $price, $img, $stock, $csale, $is_sku, $created_at, $goods_sn, $link_url,
                                $id, $title, $price, $img, $stock, $csale, $is_sku, $created_at, $goods_sn, $link_url
                            );
                        }
                    }
                }

            }
        }

        return $pages;
    }



    /**
     * @param null $id
     */
    public function deleteOne($id)
    {
        $this->db->query("DELETE FROM `product` WHERE  `product_id`= ?i", $id);
    }

    /**
     * @param $id
     * @return bool
     */
    public function isKey($id)
    {
        return (boolean)$this->db->getOne("SELECT count(`product_id`) FROM `product` WHERE `product_id` = ?i LIMIT 1", $id);
    }

}