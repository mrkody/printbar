<?php

namespace Mrkody\Printbar;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Guzzle\Http\Client;
use Exception;

/**
 * Class Printbar
 *
 * @author mrkody kody1994@mail.ru
 *
 * @package Mrkody\Printbar
 */

class Printbar {

	protected $key;
	protected $login;
	protected $type;
	protected $get_url = 'https://printbar.ru/api/v2/get/';
	protected $send_url = 'https://printbar.ru/api/v2/send/';

	public function __construct()
	{
		$this->key = Config::get('printbar.key');
		$this->login = Config::get('printbar.login');
		$this->type = Config::get('printbar.type');
	}
    
	public function getCollections()
	{
		return $this->call($this->get_url . 'collections', null, []);
	}

	public function getTypes()
	{
		return $this->call($this->get_url . 'types', null, []);
	}

	public function getSizes()
	{
		return $this->call($this->get_url . 'sizes', null, []);
	}

	public function getProducts($prod_type = false, $collection = false, $on_page = 20, $page = 1)
	{
		$query = [
			'on_page' => $on_page,
			'page' => $page,
			'men_sizes' => 1,
		];
		if($prod_type) $query['prod_type'] = $prod_type;
		if($collection) $query['collection'] = $collection;

		return $this->call($this->get_url . 'products', null, $query);
	}

	public function getProduct($id_product)
	{
		$query = [
			'id_product' => $id_product,
		];
		return $this->call($this->get_url . 'product', null, $query);
	}

	public function getBalance()
	{
		return $this->call($this->get_url . 'balance', null, []);
	}

	public function getPrices()
	{
		return $this->call($this->get_url . 'prices', null, []);
	}

	public function getShops()
	{
		return $this->call($this->get_url . 'shops', null, []);
	}

	public function getOrders($ids, $id_shop = false)
	{
		$query = [
			'ids' => $ids,
		];
		if($id_shop) $query['id_shop'] = $id_shop;

		return $this->call($this->get_url . 'orders', null, $query);
	}

	public function sendOrder(
		$id_shop, 
		$client_name, 
		$client_tel, 
		$clien_email, 
		$client_city, 
		$client_adress, 
		array $products,
		$sended = false,
		$params = []
	) {
		$query = [
			'id_shop' => $id_shop, 
			'client_name' => $client_name, 
			'client_tel' => $client_tel, 
			'clien_email' => $clien_email, 
			'client_city' => $client_city, 
			'client_adress' => $client_adress, 
			'products' => $products,
			'sended' => $sended,
			'params' => $params,
		];
		return $this->call($this->send_url . 'order', null, $query);
	}

	public function sendOrderToProduction($id)
	{
		$query = [
			'id' => $id,
		];
		
		return $this->call($this->send_url . 'ordertoproduction', null, $query);
	}

	protected function call($url, $body = null, array $query = [])
    {
        try {
        	$query = array_merge($query, ['key' => $this->key, 'login' => $this->login, 'type' => $this->type]);

            $client  = new Client();
            $request = $client->createRequest('get', $url, null, $body, ['query' => $query]);

            $response = $request->send();
            if ($response->isSuccessful()) {
                if (!$response->getBody()->getContentLength()) {
                    return (object)['result' => null, 'url' => $request->getUrl()];
                }
                $result = (object)['result' => json_decode($response->getBody(true)), 'url' => $request->getUrl()];

                return $result;
            }

            throw new Exception($response->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

}
