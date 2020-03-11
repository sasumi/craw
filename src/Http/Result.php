<?php

namespace Craw\Http;

use Craw\Context;
use function Craw\dump;

class Result {
	/** @var string */
	public $url;

	/** @var number */
	public $http_code;

	/** @var string|null */
	public $header;

	/** @var string|null */
	public $body;

	/** @var \Craw\Http\Cookie[] */
	public $cookies;

	/** @var float */
	public $total_time;

	/** @var \Craw\Context */
	public $context;

	/**
	 * @param string $url
	 * @param string $raw_string
	 * @param resource $ch
	 * @param Context|null $context
	 */
	public function __construct($url, $raw_string, $ch, $context=null){
		$this->url = $url;
		$curl_info = curl_getinfo($ch);
		$this->total_time = round($curl_info['total_time_us']/1000000, 6);
		$this->http_code = $curl_info['http_code'];
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$this->header = substr($raw_string, 0, $header_size);
		$this->body = substr($raw_string, $header_size);
		$this->cookies = Cookie::parseCookieStr($this->header);
		$this->context = $context;
	}

	/**
	 * 设定上下文环境
	 * @param $context
	 */
	public function setContext($context){
		$this->context = $context;
	}

	/**
	 * 流程继续判定
	 * @param callable $judge_callback 判定函数，传入参数：(result,context)，若返回true，则继续执行当前context
	 * @param callable|null $interrupt_callback 内容判定失败回调，传入参数(result,context)，若回调返回context，则继续返回，否则程序退出
	 * @return \Craw\Context
	 */
	public function continueWhile(callable $judge_callback, callable $interrupt_callback = null){
		if(call_user_func($judge_callback, $this, $this->context) === true){
			return $this->context;
		}
		if($interrupt_callback){
			$ret = call_user_func($interrupt_callback, $this, $this->context);
			if($ret instanceof Context){
				return $ret;
			}
		}
		exit(0);
	}

	/**
	 * 判定HTTP状态码是否为成功（200）
	 * @return bool
	 */
	public function isSuccess(){
		return $this->http_code == 200;
	}

	/**
	 * 获取失败信息
	 * @return string|null
	 */
	public function getResultMessage(){
		if($this->isSuccess()){
			return 'success, content length:'.strlen($this->body);
		}else{
			return 'http code error('.$this->http_code.').';
		}
	}

	public function __toString(){
		return $this->body ?: '';
	}

	/**
	 * 以JSON格式解码
	 * @param bool $as_assoc_array
	 * @return array|mixed
	 */
	public function decodeAsJSON($as_assoc_array = false){
		return $this->body ? json_decode($this->body, $as_assoc_array) : [];
	}

	/**
	 * 以JSONP格式解码
	 */
	public function decodeAsJSONP(){

	}
}