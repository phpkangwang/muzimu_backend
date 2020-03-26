<?php

namespace common\services;
/**
 * 作者：军师
 * 邮箱：68079320@qq.com
 * 个人博客：http://www.webiji.com
 */
class Rsa
{
	private $privateKey='';//私钥（用于用户加密）
	private $publicKey='';//公钥（用于服务端数据解密）

	public function __construct(){
		$this->privateKey = openssl_pkey_get_private(file_get_contents('secret_key/php_private.pem'));//私钥，用于加密
		$this->publicKey = openssl_pkey_get_public(file_get_contents('secret_key/php_public.pem'));//公钥，用于解密
	}
	
	
	
	/**
	 * 私钥加密
	 * @param 原始数据 $data
	 * @return 密文结果 string
	 */
	public function encryptByPrivateKey($data) {
		openssl_private_encrypt($data,$encrypted,$this->privateKey,OPENSSL_PKCS1_PADDING);//私钥加密
		$encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
		return $encrypted;
	}
	
	/**
	 * 私钥解密
	 * @param 密文数据 $data
	 * @return 原文数据结果 string
	 */
	public function decryptByPrivateKey($data){
		$data = base64_decode($data);
		openssl_private_decrypt($data,$encrypted,$this->privateKey,OPENSSL_PKCS1_PADDING);//私钥解密
		return $encrypted;
	}
	
	/**
	 * 私钥签名
	 * @param unknown $data
	 */
	public function signByPrivateKey($data){
		openssl_sign($data, $signature, $this->privateKey);
		$encrypted = base64_encode($signature);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
		return $encrypted;
	}
	
	
	/**
	 * 公钥加密
	 * @param 原文数据 $data
	 * @return 加密结果 string
	 */
	public function encryptByPublicKey($data) {
		openssl_public_encrypt($data,$decrypted,$this->publicKey,OPENSSL_PKCS1_PADDING);//公钥加密
		return base64_encode($decrypted);
	}
	
	/**
	 * 公钥解密
	 * @param 密文数据 $data
	 * @return 原文结果 string
	 */
	public function decryptByPublicKey($data) {
		$data = base64_decode($data);
		openssl_public_decrypt($data,$decrypted,$this->publicKey,OPENSSL_PKCS1_PADDING);//公钥解密
		return $decrypted;
	}
	
	/**
	 * 公钥验签
	 * @param unknown $data
	 * @param unknown $sign
	 */
	public function verifyByPublicKey($data,$sign){
		$sign = base64_decode($sign);
		return openssl_verify($data, $sign, $this->publicKey);
	}
	
	public function __destruct(){
		openssl_free_key($this->privateKey);
		openssl_free_key($this->publicKey);
	}
}
//示例代码
//$rsa = new \Hass\Compass\Models\service\RSA1();
//header("Content-type: text/html; charset=utf-8");
//$str = '我要加密这段文字。';
//echo '原文:'.$str.'</br>';
//$crypt = $rsa->encryptByPrivateKey($str);
//echo '私钥加密密文:'.$crypt.'</br>';
//$now = $rsa->decryptByPublicKey($crypt);
//echo '公钥解密原文:'.$now.'</br>';
//echo '------------'.'</br>';
//$str = '我要加密这段文字。';
//echo '原文:'.$str.'</br>';
//$crypt = $rsa->encryptByPublicKey($str);
//echo '公钥加密密文:'.$crypt.'</br>';
//$now = $rsa->decryptByPrivateKey($crypt);
//echo '私钥解密原文:'.$now.'</br>';
//echo '------------'.'</br>';
//$str = '我要签名这段文字。';
//echo '原文:'.$str.'</br>';
//$crypt = $rsa->signByPrivateKey($str);
//echo '签名密文:'.$crypt.'</br>';
//if($rsa->verifyByPublicKey($str,$crypt)){
//    echo 'true';
//} else {
//    echo 'false';
//}













