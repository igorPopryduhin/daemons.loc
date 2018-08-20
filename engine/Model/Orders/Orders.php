<?php

namespace Engine\Model\Orders;

use Engine\DI\DI;
use Engine\Wxrrd\Wxrrd;
use SafeMySQL;
use Engine\Model;

class Orders extends Model
{
    /**
     * @var SafeMySQL
     */
    protected $db;
    /**
     * @var Wxrrd
     */
    public $wxrrd;


    public function __construct (DI $di)
    {
        parent::__construct($di);
    }

    /**
     * Список заказов <hr>
     */
    public function tradeLists()
    {
        $data = $this->wxrrd->CallAPI('weiba.wxrrd.trade.lists', $this->wxrrd->restBuild());

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
     * Детальная информация о заказе <hr>
     * @param string $order_sn
     * @return mixed
     */
    public function tradeDetails(string $order_sn)
    {
        $this->wxrrd->setField('order_sn', $order_sn);
        return $this->wxrrd->CallAPI('weiba.wxrrd.trade.details', $this->wxrrd->restBuild());
    }


    /**
     * Добавление детальной информации о заказе в базу данных
     * @param array $orderDetails
     */
    private function ordersToDataBase($orderDetails = [])
    {
        $id 			 = (int)$orderDetails['id'];			// Идентификатор записи
        $order_sn        = $orderDetails['order_sn'];         // Серийный номер
        $nickname 		 = $orderDetails['nickname'];			// Имя покупателя
        $order_type 	 = $orderDetails['order_type'];	    // Тип ордера
        $amount			 = $orderDetails['amount'];		    // Общая сумма покупки
        $goods_amount	 = $orderDetails['goods_amount'];		// Общая сумма покупки
        $is_virtual		 = $orderDetails['is_virtual'];		// Виртуальность продукта
        $payment_name	 = $orderDetails['payment_name'];		// Наименование платежной системмы
        $status_msg		 = $orderDetails['status_msg'];       // Статус сообщение
        $status          = $orderDetails['status'];			// Статус
        $original_status = $orderDetails['status'];			// Оригинальный статус
        $created_at      = $orderDetails['created_at'];
        $updated_at      = $orderDetails['updated_at'];
        $pay_at			 = $orderDetails['pay_at'];
        $payment_code	 = $orderDetails['payment_code'];
        $payment_sn      = $orderDetails['payment_sn'];
        $trade_sn		 = $orderDetails['trade_sn'];

        $this->db->query("INSERT IGNORE INTO `order` (`order_id`, `nickname`, `order_sn`, `order_type`, `amount`, `goods_amount`, `is_virtual`, `payment_name`, 
                                                            `status_msg`, `status`, `original_status`, `created_at`, `updated_at`, `pay_at`, `payment_code`, `payment_sn`,
                                                             `trade_sn`) VALUES (
							?i,?s,?s,?i,?s,?s,?i,?s,?s,?i,?i,?s,?s,?s,?s,?s,?s)",

            // ЗНАЧЕНИЯ ПОЛЕЙ
            $id,				// Идентификатор записи в текуще базе
            $nickname,			// Ник пользователя создавший транзакцию
            $order_sn,			// Номер заказа
            $order_type,		// Тип заказа
            $amount,			// Сумма
            $goods_amount,		// Сумма
            $is_virtual,		// Виртуальность продукта
            $payment_name,		// Наименование платежной системмы
            $status_msg,		// Статус сообщение
            $status,			// Статус транзакции
            $original_status,	// Статус транзакции
            $created_at,		// Время создания заказа
            $updated_at,		// Время последненго изменения статуса заказа
            $pay_at,			//
            $payment_code,		//
            $payment_sn,		// Серийный номер оплаты
            $trade_sn			//
        );

    }
}