<?php

declare(strict_types=1);

namespace App\Client;

use Exception;
use App\Error\HttpError;

class RestClient extends HttpClient
{
    const OPTION_JSON_DECODE = 'json_decode';
    const OPTION_JSON_ASSOC = 'json_assoc';

    public function __construct()
    {
        parent::__construct();
        $this->name = 'rest';
    }

    public function request($method, $url, $options = [])
    {
        $response = parent::request($method, $url, $options);

        $json_decode = $options[self::OPTION_JSON_DECODE] ?? true;
        $json_assoc = $options[self::OPTION_JSON_ASSOC] ?? false;

        if ($json_decode && $response->content && $response->status != 204) {
            $response->entity = json_decode($response->content, $json_assoc);
        } else {
            $response->entity = null;
        }

        if ($response->status < 100) {
            throw new HttpError('SERVICE_UNAVAILABLE', 503, new \Exception($response->error));
        }

        switch ($response->status) {

            case 200:
            case 201:
            case 202:

                if (json_last_error() != JSON_ERROR_NONE) {

                    $e = new Exception('JSON: ' . json_last_error_msg() . '(' . json_last_error() . ')');
                    throw new HttpError('INVALID_SERVICE_RESPONSE', 502, $e);
                }

                break;

            case 204:
                break;

            default:

                $message = $response->content;

                if ($response->entity) {

                    foreach (['message', 'Message', 'description', 'status'] as $name) {

                        if (!$json_assoc ? isset($response->entity->{$name}) : isset($response->entity[$name])) {
                            $message = !$json_assoc ? $response->entity->{$name} : $response->entity[$name];
                            break;
                        }
                    }
                }

                throw new HttpError($message ?? 'SERVICE_ERROR', $response->status);
        }

        return $response;
    }
}
