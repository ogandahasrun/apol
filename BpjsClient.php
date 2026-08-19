<?php
/**
 * Class BpjsClient
 * Menangani otentikasi, request, dan dekripsi untuk BPJS Apotek Online (PHP Native)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/LZString.php';

class BpjsClient {
    private $consId;
    private $secretKey;
    private $userKey;
    private $baseUrl;

    public function __construct() {
        $this->consId = BPJS_CONS_ID;
        $this->secretKey = BPJS_SECRET_KEY;
        $this->userKey = BPJS_USER_KEY;
        $this->baseUrl = BPJS_BASE_URL;
    }

    /**
     * Membuat Signature BPJS
     */
    private function getSignature() {
        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $this->consId . "&" . $tStamp, $this->secretKey, true);
        $encodedSignature = base64_encode($signature);
        
        return [
            'X-cons-id' => $this->consId,
            'X-timestamp' => $tStamp,
            'X-signature' => $encodedSignature,
            'user_key' => $this->userKey
        ];
    }

    /**
     * Melakukan HTTP Request menggunakan cURL
     */
    public function request($endpoint, $method = 'GET', $data = null, $contentType = 'application/json') {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $headers = $this->getSignature();
        
        $httpHeaders = [
            "X-cons-id: " . $headers['X-cons-id'],
            "X-timestamp: " . $headers['X-timestamp'],
            "X-signature: " . $headers['X-signature'],
            "user_key: " . $headers['user_key'],
            "Accept: application/json",
            "Content-Type: " . $contentType
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'metaData' => ['code' => 500, 'message' => 'cURL Error: ' . $error],
                'response' => null
            ];
        }

        $result = json_decode($response, true);
        
        // Coba dekripsi jika ada atribut response dan bentuknya terenkripsi
        if (isset($result['response']) && is_string($result['response'])) {
            $key = $this->consId . $this->secretKey . $headers['X-timestamp'];
            $result['response'] = $this->decrypt($result['response'], $key);
        }

        return $result;
    }

    /**
     * Dekripsi string response BPJS (AES-256-CBC + LZString)
     */
    private function decrypt($response, $key) {
        $encrypt_method = 'AES-256-CBC';
        $key_hash = hex2bin(hash('sha256', $key));
        $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
        
        $output = openssl_decrypt(base64_decode($response), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);
        
        if ($output !== false) {
            // Lakukan dekompresi LZString
            $decompressed = LZString::decompressFromBase64($output);
            if ($decompressed) {
                $json = json_decode($decompressed, true);
                return $json !== null ? $json : $decompressed;
            }
            // Fallback jika bukan lzstring (atau gagal)
            $json = json_decode($output, true);
            return $json !== null ? $json : $output;
        }
        
        return "Gagal Dekripsi";
    }
}
