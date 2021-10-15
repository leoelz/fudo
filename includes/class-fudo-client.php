<?php
class Fudo_Client
{
	private $access_token = null;
	private $use_api = false;
	private $use_staging = false;

	public function __construct($use_api = false, $use_staging = false){
		$this->use_api = $use_api;
		$this->use_staging = $use_staging;
	}

	private function fetch($uri, $body = null){
		$cache_size = 2048;
		$host = $this->use_staging ? "api-staging.fu.do" : "api.fu.do";
		$url = $this->use_api ? "/v1alpha1" : "";
		if ($fp = fsockopen("ssl://".$host,443, $errno, $errstr)) {
			$head = ($body===null?"GET":"POST")." ".$url.$uri." HTTP/1.1\r\n";
			$head .= "Host: ".$host."\r\n";
			$head .= "Accept: application/json\r\n";
			if ($body !== null) {
				$head .= "Content-Length: " . strlen($body) . "\r\n";
				$head .= "Content-Type: application/json\r\n";
			}
			$head .= "Cache-Control: no-cache\r\n";
			if($this->access_token !== null){
				$head .= "Authorization: Bearer ".$this->access_token."\r\n";
			}
			fwrite($fp,$head."\r\n".$body);
			if($body!==null)
				fwrite($fp,$body);
			$content_length=0;
			$chunked=false;
			$line = fgets($fp);
			if(substr($line,0,12)=="HTTP/1.1 200") {
				while (!feof($fp)) {
					$line = fgets($fp);
					if ($line == "\r\n") {
						if (!$chunked){
							$response = "";
							while($content_length > 0) {
								if($cache_size < $content_length){
									$length = $cache_size;
								} else {
									$length = $content_length;
								}
								$line = fread($fp, $length);
								$response .= $line;
								$content_length -= strlen($line);
							}
						} else {
							$response = "";
							while ($length = hexdec(trim(fgets($fp)))) {
								$response .= fread($fp, $length);
							}
						}
						break;
					}
					elseif (substr($line, 0, 15) === "Content-Length:")
						$content_length = substr($line, 15);
					elseif (substr($line, 0, 26) === "Transfer-Encoding: chunked")
						$chunked = true;
				}
				fclose($fp);
				return $response;
			}
		}
	}

	private function get_access_token(){
		$plugin_integration = new Fudo_Integration();
		$fudo_client_id = $plugin_integration->get_option( 'fudo_client_id' );
		$fudo_client_secret = $plugin_integration->get_option( 'fudo_client_secret' );
		$fudo_login = $plugin_integration->get_option( 'fudo_login' );
		$fudo_password = $plugin_integration->get_option( 'fudo_password' );


		$body = $this->use_api
				? json_encode(["clientId"=>$fudo_client_id,"clientSecret"=>$fudo_client_secret])
				: json_encode(["login"=>$fudo_login,"password"=>$fudo_password]);

		$response = $this->fetch($this->use_api ? "/auth" : "/authenticate", $body);

		$response = json_decode($response);
		if (property_exists($response, 'token'))
			return $this->access_token = $response->token;
	}

	public function get_products(){
		if($this->access_token === null)
			$this->get_access_token();
		return $this->fetch("/products?a=-1");
	}

	public function get_categories(){
		if($this->access_token === null)
			$this->get_access_token();
		return $this->fetch("/product_categories");
	}
}
