<?php

namespace WebCore;

class Message
{
    protected $protocol_version = '1.1';
    protected $headers = [];
    protected $header_names = [];
    protected $body;

    public function getProtocolVersion()
    {
        return $this->protocol_version;
    }

    public function withProtocolVersion($version)
    {
        $this->validateProtocolVersion($version);

        $clone = clone $this;
        $clone->protocol_version = $version;

        return $clone;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeader($name)
    {
        return $this->getOriginalHeaderName($name) ? true : false;
    }

    public function getHeader($name)
    {
        if (!$this->hasHeader($name)) return [];

        return $this->headers[$this->getOriginalHeaderName($name)];
    }

    public function getHeaderLine($name)
    {
        $value = $this->getHeader($name);

        return implode(',', $value);
    }

    public function withHeader($name, $value)
    {
        $clone = clone $this;
        $clone->doSetHeader($name, $value);

        return $clone;
    }

    public function withAddedHeader($name, $value)
    {
        $clone = clone $this;
        $clone->doAddHeader($name, $value);

        return $clone;
    }

    public function withoutHeader($name)
    {
        $clone = clone $this;
        $clone->doRemoveHeader($name);

        return $clone;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function withBody($body)
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    protected function initHeaders(array $headers = [])
    {
        $this->headers = [];
        $this->header_names = [];

        foreach ($headers as $name => $value) {
            $this->doSetHeader($name, $value);
        }
    }

    protected function doSetHeader($name, $value)
    {
        $name = $this->filterHeaderName($name);

        // if already exists, remove existing header values
        if ($this->hasHeader($name)) {
            unset($this->headers[$this->getOriginalHeaderName($name)]);
        }

        // set header with original name
        $this->headers[$name] = $this->filterHeaderValue($value);
        // link normalized header name and the original header name
        $this->header_names[$this->normalizeHeaderName($name)] = $name;
    }

    protected function doAddHeader($name, $value)
    {
        $name = $this->filterHeaderName($name);

        if (!$this->hasHeader($name)) return $this->withHeader($name, $value);

        // fetch original name from the existing header
        $original_name = $this->getOriginalHeaderName($name);

        $this->headers[$original_name] = array_merge($this->headers[$original_name], $this->filterHeaderValue($value));
    }

    protected function doRemoveHeader($name)
    {
        // if header exists, remove header and the mapping for it's name
        if ($this->hasHeader($name)) {
            unset($this->headers[$this->getOriginalHeaderName($name)]);
            unset($this->header_names[$this->normalizeHeaderName($name)]);
        }
    }

    private function normalizeHeaderName($name)
    {
        return strtolower($name);
    }

    private function getOriginalHeaderName($name)
    {
        $normalized_name = $this->normalizeHeaderName($name);

        if (isset($this->header_names[$normalized_name])) {
            return $this->header_names[$normalized_name];
        }

        return '';
    }

    private function validateProtocolVersion($version)
    {
        $valid_protocol_versions = ['1.0', '1.1', '2'];

        if (!in_array($version, $valid_protocol_versions)) {
            throw new \InvalidArgumentException('Invalid HTTP version');
        }
    }

    private function filterHeaderName($name)
    {
        if (!is_string($name)) throw new \InvalidArgumentException('Invalid header name type');

        if (!preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name)) {
            throw new \InvalidArgumentException('Provided header name is not valid');
        }

        return $name;
    }

    private function filterHeaderValue($value)
    {
        if (!is_array($value)) $value = [$value];

        foreach ($value as $a_value) {
            if (!is_string($a_value) && !is_numeric($a_value)) {
                throw new \InvalidArgumentException('Invalid header value type; must be a string or number');
            }

            // Look for:
            // \n not preceded by \r, OR
            // \r not followed by \n, OR
            // \r\n not followed by space or horizontal tab; these are all CRLF attacks
            if (preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $a_value)) {
                throw new \InvalidArgumentException('Provided header value is not valid');
            }

            // Non-visible, non-whitespace characters
            // 9 === horizontal tab
            // 10 === line feed
            // 13 === carriage return
            // 32-126, 128-254 === visible
            // 127 === DEL (disallowed)
            // 255 === null byte (disallowed)
            if (preg_match('/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/', $a_value)) {
                throw new \InvalidArgumentException('Provided header value is not valid');
            }
        }

        return $value;
    }
}
