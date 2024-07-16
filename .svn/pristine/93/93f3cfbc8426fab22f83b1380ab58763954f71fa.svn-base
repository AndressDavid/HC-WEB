<?php

class ApiHelper
{
    private $headers;

    public function __construct($headers = [])
    {
        $this->headers = $headers;
    }

    public function get($tcUrl, $taParametros = [])
    {
        $lcUrl = $tcUrl;

        if (!empty($taParametros)) {
            $lcUrl .= '?' . http_build_query($taParametros);
        }

        try {
            $curl = curl_init($lcUrl);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => $this->headers,
            ]);

            $laResponse = curl_exec($curl);
            $laResponseData = json_decode($laResponse, true);
            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpStatus !== 200) {
                return [];
            }

            return $laResponseData;
        } catch (\Exception $e) {
            echo 'Error de Comunicación ' . $e->getMessage();
        }
    }

    public function post($tcUrl, $taParametros = [], $taData = [])
    {
        try {
            $lcUrl = $tcUrl;

            if (!empty($taParametros)) {
                $lcUrl .= '?' . http_build_query($taParametros);
            }

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $lcUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => $this->headers,
                CURLOPT_POSTFIELDS => $taData
            ]);

            $laResponse = curl_exec($curl);
            $laResponseData = json_decode($laResponse, true);
            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpStatus !== 200) {
                return [];
            }

            return $laResponseData;
        } catch (\Exception $e) {
            echo 'Error de Comunicación ' . $e->getMessage();
        }
    }
}
