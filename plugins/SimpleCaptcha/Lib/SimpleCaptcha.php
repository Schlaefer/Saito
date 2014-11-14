<?php

class SimpleCaptcha extends Object {
	static public $defaults = array (
			'dummyField' => 'homepage',
			'method' => 'hash',
			'checkSession' => false,
			'checkIp' => false,
			'salt' => '',
			'type' => 'active',
	);

	public static function buildHash($params, $options) {
		$hashValue = date('c', $params['timestamp']).'_';
		$hashValue .= ($options['checkSession']) ? session_id().'_' : '';
		$hashValue .= ($options['checkIp']) ? env('REMOTE_ADDR').'_' : '';
		$hashValue .= $params['result'].'_'.$options['salt'];
		$hashValue = Security::hash($hashValue);
		return $hashValue;
	}
}
?>