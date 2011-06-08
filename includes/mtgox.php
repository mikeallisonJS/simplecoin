<?php
/**
 * Mt Gox Trade API Client
 * 
 * History:
 * 
 *		12-Apr-11
 *			First version of the client
 * 
 * @author Russell Smith <russell.smith@ukd1.co.uk>
 * @copyright UKD1 Limited 2011
 * @license licence.txt ISC license
 * @see https://www.mtgox.com/support/tradeAPI
 * @see https://github.com/ukd1/Mt-Gox-Trade-API-PHP-Client
 */
 
 
/**
 * Order class for Mt Gox Orders
 *
 * @author Russell Smith <russell.smith@ukd1.co.uk>
 * @copyright UKD1 Limited 2011
 * @license licence.txt ISC license
 * @see https://www.mtgox.com/support/tradeAPI
 * @see https://github.com/ukd1/Mt-Gox-Trade-API-PHP-Client
 */
class mtgox_order extends mtgox_base
{
	private $data;

	/**
	 *
	 *
	 * @param  $user username for Mt Gox
	 * @param  $pass password for Mt Gox
	 * @param null $data array of data about the order
	 */
	public function __construct ($user, $pass, $data = null)
	{
		$this->user = $user;
		$this->pass = $pass;
		$this->data = $data;
	}
	
	public function __get ($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}

	/**
	 * Is this a sell order?
	 * @return bool
	 */
	public function is_sell_order ()
	{
		return $this->data['type'] === self::SELL_ORDER;
	}

	/**
	 * Is this a buy order?
	 * @return bool
	 */
	public function is_buy_order ()
	{
		return $this->data['type'] === self::BUY_ORDER;
	}

	/**
	 * Cancel the order over the API
	 * @return bool true on success
	 */
	public function cancel ()
	{
		return is_array($this->_post('cancelOrder.php', array('oid' => $this->data['oid'], 'type' => $this->data['type'])));
	}
}

/**
 * Mt Gox Trade API implementation
 *
 * @author Russell Smith <russell.smith@ukd1.co.uk>
 * @copyright UKD1 Limited 2011
 * @license licence.txt ISC license
 * @see https://www.mtgox.com/support/tradeAPI
 * @see https://github.com/ukd1/Mt-Gox-Trade-API-PHP-Client
 */
class mtgox extends mtgox_base
{
	/**
	 * @param string $user username
	 * @param string $pass password
	 */
	public function __construct ($user, $pass)
	{
		$this->user = $user;
		$this->pass = $pass;
	}

	/**
	 * Get your current balance
	 *
	 * @return array An array of your current USD / BTC balance
	 */
	public function balance ()
	{
		return $this->_post('getFunds.php');

	}

	/**
	 * Request a transfer of BTC
	 *
	 * @param string $address BTC address to transfer to
	 * @param float $amount amount of BTC to transfer
	 * @return array|false
	 */
	public function widthdraw ($address, $amount)
	{
		return $this->_post('withdraw.php', array('bitcoin_address_to_send_to' => $address, 'amount' => $amount));
	}

	/**
	 * Return an array of your current orders
	 *
	 * @param int $oid optionally just return a single order
	 * @return array
	 */
	public function orders ()
	{
		$response = $this->_post('getOrders.php', array('amount' => '#', 'price' => '#'));

		$orders = array();
		foreach ($response['orders'] as $_order)
		{
			$orders[] = new mtgox_order($this->user, $this->pass, $_order);
		}

		return $orders;
	}

	/**
	 * Create a buy order for amount @ price
	 *
	 * @param int $amount amount of BTC to buy
	 * @param float $price price to buy BTC at
	 * @return array|bool
	 */
	public function buy ($amount, $price)
	{
		return $this->_post('buyBTC.php', array('amount' => $amount, 'price' => $price));
	}

	/**
	 * Create a sell order for amount @ price
	 *
	 * @param int $amount amount of BTC to sell
	 * @param float $price price to sell BTC at
	 * @return array|bool
	 */
	public function sell ($amount, $price)
	{
		return $this->_post('sellBTC.php', array('amount' => $amount, 'price' => $price));	
	}

	/**
	 * Return an array of ticker data
	 *
	 * @return array
	 */
	public function ticker ()
	{
		$ticker = $this->_get('data/ticker.php');
		
		return $ticker['ticker'];
	}

	/**
	 * Return an array of market depth data
	 * @return array
	 */
	public function depth ()
	{
		return $this->_get('data/getDepth.php');
	}

	/**
	 * Return an array of asks / bids
	 * @return array
	 */
	public function trades ()
	{
		return $this->_get('data/getTrades.php');
	}
	
}


/**
 * Abstract class which the main / order class extend
 *
 * @author Russell Smith <russell.smith@ukd1.co.uk>
 * @copyright UKD1 Limited 2011
 * @license licence.txt ISC license
 * @see https://www.mtgox.com/support/tradeAPI
 * @see https://github.com/ukd1/Mt-Gox-Trade-API-PHP-Client
 */
abstract class mtgox_base
{
	/**
	 * @var username to use for authentication against the API
	 */
	protected $user;

	/**
	 * @var password to use for authentication against the API
	 */
	protected $pass;

	/**
	 * Current Mt Gox fee in percent (unused)
	 */
	const MTGOX_FEE = 0.0065;

	/**
	 * Mt Gox endpoint for the API
	 */
	const ENDPOINT = 'https://www.mtgox.com/code/';

	/**
	 * A timeout to control how long to wait for the API to respond in seconds
	 */
	const TIMEOUT = 3;

	/**
	 * User agent string which is sent which all requests
	 */
	const USERAGENT = 'UKD1 MTGOX Client';

	/**
	 * Sell Order type
	 */
	const SELL_ORDER = 1;

	/**
	 * Buy order type
	 */
	const BUY_ORDER = 2;

	/**
	 * Order status ACTIVE
	 */
	const STATUS_ACTIVE = 1;

	/**
	 * Order status INACTIVE (insufficent funds)
	 */
	const STATUS_INSUFFICENT_FUNDS = 2;

	/**
	 * Do a HTTP POST to the specified URI
	 *
	 * @param string $uri uri to post to (appended to the endpoint)
	 * @param array $data array of post fields to pass
	 * @return bool|array
	 */
	protected function _post ($uri, $data = array())
	{
		$data['name'] = $this->user;
		$data['pass'] = $this->pass;
	
		$r = $this->_http('POST', $uri, $data);
		
		if ($r['http_code'] === 200)
		{
			return json_decode($r['result'], true);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Perform an HTTP GET
	 *
	 * @param  $uri URI to get, appended to the endpoint url
	 * @param array $data get parameters
	 * @return bool|array
	 */
	protected function _get ($uri, $data = array())
	{
		$r = $this->_http('GET', $uri, $data);
		
		if ($r['http_code'] === 200)
		{
			return json_decode($r['result'], true);
		}
		else
		{
			return false;
		}
	}

	/**
	 * perform a HTTP request
	 *
	 * @param string $method HTTP method to use, currently supports GET|POST
	 * @param string $uri URI to append to the endppint
	 * @param array $data single dimensional key / value pairs of data to pass
	 * @return array
	 */
	protected function _http ($method, $uri, $data)
	{
		$url = self::ENDPOINT . $uri;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);	
			

		switch ($method)
		{
			case 'POST':
				$post_fields = array();
				foreach ($data as $k=>$v) {
					array_push($post_fields, "$k=$v");
				}
				$post_fields = implode('&', $post_fields);
				
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
				break;
				
			case 'GET':
			default:
				$get_fields = array();
				foreach ($data as $k=>$v) {
					array_push($get_fields, "$k=$v");
				}
				$url .= '?' . implode('&', $get_fields);
			
				curl_setopt($ch, CURLOPT_URL, $url);
		}

		$result = curl_exec ($ch);

		$tmp = curl_getinfo($ch);
		$tmp['result'] = $result;
		curl_close ($ch);

		return $tmp;
	}
	
}
