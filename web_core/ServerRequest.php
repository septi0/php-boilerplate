<?php

namespace WebCore;

class ServerRequest
{
    private $method;
    private $uri;
    private $headers;
    private $body;
    private $protocolVersion;
    private $serverParams;
    private $queryParams;
    private $cookieParams;
    private $uploadedFiles;
    private $parsedBody;
    private $attributes;

    public function __construct(
        $method,
        $uri,
        $headers = [],
        $body = null,
        $protocolVersion = '1.1',
        $serverParams = [],
        $queryParams = [],
        $cookieParams = [],
        $uploadedFiles = [],
        $parsedBody = null,
        $attributes = []
    ) {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;
        $this->protocolVersion = $protocolVersion;
        $this->serverParams = $serverParams;
        $this->queryParams = $queryParams;
        $this->cookieParams = $cookieParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->parsedBody = $parsedBody;
        $this->attributes = $attributes;
    }

    /** RequestInterface Methods **/

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method)
    {
        $new = clone $this;
        $new->method = strtoupper($method);
        return $new;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function withUri($uri, $preserveHost = false)
    {
        $new = clone $this;
        $new->uri = $uri;
        return $new;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }

    public function getHeader($name)
    {
        return $this->headers[$name] ?? [];
    }

    public function withHeader($name, $value)
    {
        $new = clone $this;
        $new->headers[$name] = (array) $value;
        return $new;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function withBody($body)
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version)
    {
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    /** ServerRequestInterface Methods **/

    public function getServerParams()
    {
        return $this->serverParams;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function withQueryParams($queryParams)
    {
        $new = clone $this;
        $new->queryParams = $queryParams;
        return $new;
    }

    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    public function withCookieParams($cookies)
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles($uploadedFiles)
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data)
    {
        if (!is_array($data) && !is_object($data) && $data !== null) {
            throw new \InvalidArgumentException('Parsed body must be array, object, or null');
        }

        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute($name, $value)
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    public function withoutAttribute($name)
    {
        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }
}
