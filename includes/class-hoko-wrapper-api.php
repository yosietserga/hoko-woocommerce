<?php
/**
 * File contain Wrapper for a Hoko APIs Client Library for PHP
 *
 * @since 1.0.0
 *
 * @package HOKO
 * @subpackage HOKO/includes
 */

defined( 'ABSPATH' ) || exit;


class WC_HOKO_Necoyoad_Hoko_Api {
	private $api_base_uri = 'https://hoko.com.co/api';

	private $subscription_key;
	private $username;
	private $password;
	private $token;
	private $client;

	public function __construct(String $username, String $password) {
		$this->username = $username;
		$this->password = $password;

		$this->client = new GuzzleHttp\Client();
	}

	public function getHeaders($withToken = true) {

		$header = [
	        'Accept' => 'application/json',
		];
		
		if ($withToken) {
			if (!isset($this->token->access_token)) $this->getAccessToken();
			$authorization = "Bearer {$this->token->access_token}";
			$header['authorization'] = $authorization;
		}

		

		return $header;
	}

	public function get($endpoint, $params) {
		return $this->request('GET', $this->api_base_uri . $endpoint, $params);
	}

	public function post($endpoint, $params) {
		return $this->request('POST', $this->api_base_uri . $endpoint, $params);
	}

	public function request($type, $endpoint, $params) {

		if (!isset($params['headers'])) {
			$withToken = isset($params['withToken']) && $params['withToken'] === false ? false : true;


			if ($withToken && (!isset($this->token->access_token) || !$this->token->access_token)) $this->getAccessToken();

			$params['headers'] = $this->getHeaders( $withToken );
			unset($params['withToken']);
		}

		try {
			$response = $this->client->request($type, $endpoint, $params);
				
			return (int)$response->getStatusCode() === 200 ? json_decode($response->getBody()) : null;
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function getAccessToken() {
		$response = $this->post("/login", [
			'form_params' => [
		        'username' => $this->username, 
		        'password' => $this->password
		    ],
		    'withToken' => false,
		]);

		$this->token = $response;

		return $response->access_token;
	}

	public function refreshToken() {
		if ($this->token) return $this->token->refresh_token;
		return $this->getAccessToken();
	}

	public function getProducts(array $params = [])
	{
		if (isset($params['id']) && !empty((int)$params['id'])) {
			$ep = "/product/{$params['id']}";

			return $this->get($ep, ["withToken"=>false]);
		} else {
			$ep = "/products";

			if (isset($params['page']) && !empty((int)$params['page'])) {
				$query['page'] = (int)$params['page'];
			} else {
				$query['page'] = 1;
			}

			return $this->get($ep, [
				'query' => $query,
				"withToken" => false
			]);
		}
	}

	public function generateChecksum(int $id) {
		return hash('sha256', serialize( $this->getProducts( ["id"=>$id] ) ));
	}

	public function getCategories(array $params = []) {
		if (isset($params['id']) && !empty((int)$params['id'])) {
			$ep = "/category/{$params['id']}";

			return $this->get($ep, [
				"withToken" => false
			]);
		} else {
			$ep = "/categories";
			if (isset($params['page']) && !empty((int)$params['page'])) {
				$query['page'] = (int)$params['page'];
			}
			
			return $this->get($ep, [
				'query' => $query,
				"withToken" => false
			]);
		}
	}

	public function getOrders() {		
		if (isset($params['id']) && !empty((int)$params['id'])) {
			$ep = "/orders/{$params['id']}";

			return $this->get($ep, ["withToken"=>true]);
		} else {
			$ep = "/orders";

			if (isset($params['page']) && !empty((int)$params['page'])) {
				$query['page'] = (int)$params['page'];
			} else {
				$query['page'] = 1;
			}

			return $this->get($ep, [
				'query' => $query,
				"withToken" => true
			]);
		}
	}

	public function createOrder() {}
	public function updateOrderStatus() {}
}