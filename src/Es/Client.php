<?php
namespace App\Es;

final class Client {
	private string $base;
	private ?string $user;
	private ?string $pass;

	public function __construct(string $base, ?string $user=null, ?string $pass=null) {
		$this->base = rtrim($base,'/');
		$this->user = $user;
		$this->pass = $pass;
	}
	private function req(string $method, string $path, ?array $body=null): array {
		$ch = curl_init($this->base.$path);
		$headers = ['Content-Type: application/json'];
		curl_setopt_array($ch, [
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => $headers,
		]);
		if ($this->user) curl_setopt($ch, CURLOPT_USERPWD, $this->user.':'.$this->pass);
		if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
		$res = curl_exec($ch);
		$err = curl_error($ch);
		$code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		curl_close($ch);
		return [$code, $res, $err];
	}
	public function reindex(string $source, string $dest): array {
		return $this->req('POST', '/_reindex', ['source'=>['index'=>$source], 'dest'=>['index'=>$dest]]);
	}
}
