<?php

class ResponseEmitter
{
    public function emit($response)
    {
        $this->emitHeaders($response);
        $this->emitBody($response);
    }

    private function emitHeaders($response)
    {
        $status_code = $response->getStatusCode();
        $reason_phrase = $response->getReasonPhrase();
        header("HTTP/1.1 {$status_code} {$reason_phrase}", true, $status_code);

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("{$name}: {$value}", false);
            }
        }
    }

    private function emitBody($response)
    {
        $body = $response->getBody();

        echo $body;
    }
}
