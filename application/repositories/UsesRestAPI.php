
<?php

trait UsesRestAPI
{
    private static $API = API_URL;

    /**
     * Decode API response.
     */
    private static function decode_response($buffer)
    {
        if ($buffer === false || $buffer === '') {
            return null;
        }

        $decoded = json_decode($buffer);

        if ($decoded === null && is_string($buffer)) {
            $trimmed = trim($buffer);

            if (strpos($trimmed, "}{") !== false) {
                $parts = explode("}{", $trimmed, 2);
                $decoded = json_decode($parts[0] . "}");
            }
        }

        return $decoded;
    }

    /**
     * Execute cURL request with safe timeout and error handling.
     */
    private static function execute_curl(
        $url,
        $method = 'GET',
        $data = null,
        $timeout = 15,
        $json = false
    ) {
        $curl_handle = curl_init();

        if ($curl_handle === false) {
            log_message('error', 'Unable to initialize cURL.');
            return null;
        }

        $options = [
            CURLOPT_URL => self::$API . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => max(5, (int) $timeout),
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => false,
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;

            if ($json) {
                $options[CURLOPT_HTTPHEADER] = [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ];

                $payload = json_encode($data);

                if ($payload === false) {
                    log_message('error', 'API JSON encoding failed: ' . json_last_error_msg());
                    curl_close($curl_handle);
                    return null;
                }

                $options[CURLOPT_POSTFIELDS] = $payload;
            } else {
                $options[CURLOPT_POSTFIELDS] = is_array($data)
                    ? http_build_query($data)
                    : $data;
            }
        }

        if ($method === 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        }

        curl_setopt_array($curl_handle, $options);

        $buffer = curl_exec($curl_handle);

        $curl_error = curl_error($curl_handle);
        $curl_errno = curl_errno($curl_handle);
        $http_code = (int) curl_getinfo(
            $curl_handle,
            CURLINFO_HTTP_CODE
        );

        $elapsed = curl_getinfo(
            $curl_handle,
            CURLINFO_TOTAL_TIME
        );

        curl_close($curl_handle);

        if ($buffer === false) {
            log_message(
                'error',
                'External API cURL failure. Method: ' . $method .
                ', Error: ' . $curl_errno .
                ', Message: ' . $curl_error .
                ', Time: ' . $elapsed . ' seconds'
            );

            return null;
        }

        if ($http_code >= 400 || $http_code === 0) {
            log_message(
                'error',
                'External API HTTP error. Method: ' . $method .
                ', HTTP Code: ' . $http_code .
                ', Time: ' . $elapsed . ' seconds'
            );
        }

        return $buffer;
    }

    /**
     * GET API method.
     */
    private static function get_method($url, $cacheTTL = 0, $timeout = 15)
    {
        $buffer = self::execute_curl(
            $url,
            'GET',
            null,
            $timeout
        );

        return self::decode_response($buffer);
    }

    /**
     * POST API method.
     */
    private static function post_method(
        $url,
        $array,
        $cacheTTL = 0,
        $timeout = 15
    ) {
        $buffer = self::execute_curl(
            $url,
            'POST',
            $array,
            $timeout
        );

        return self::decode_response($buffer);
    }

    /**
     * POST API method with JSON.
     */
    private static function post_method_with_json(
        $url,
        $array,
        $timeout = 15
    ) {
        $buffer = self::execute_curl(
            $url,
            'POST',
            $array,
            $timeout,
            true
        );

        return self::decode_response($buffer);
    }

    /**
     * DELETE API method.
     */
    private static function delete($id)
    {
        $array = [
            'item_id' => $id
        ];

        return self::execute_curl(
            '',
            'DELETE',
            $array,
            15
        );
    }
}
