<?php

class Request
{
    private $uri;
    private $method;
    private $headers;
    private $body;
    private $queryParams;
    private $cookieParams;
    private $serverParams;
    private $files;
    private $parsedBody;
    private $attributes;

    public function __construct(
        string $method = 'GET',
        $uri = null,
        array $headers = [],
        $body = null,
        array $queryParams = [],
        array $cookieParams = [],
        array $serverParams = [],
        array $files = [],
        array $parsedBody = [],
        array $attributes = []
    ) {
        $this->method = $method;
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;
        $this->queryParams = $queryParams;
        $this->cookieParams = $cookieParams;
        $this->serverParams = $serverParams;
        $this->files = $files;
        $this->parsedBody = $parsedBody;
        $this->attributes = $attributes;
    }

    public function getProtocolVersion()
    {
        return '1.1';
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeader($name)
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function getHeader($name)
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    public function getHeaderLine($name)
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value)
    {
        $new = clone $this;
        $new->headers[strtolower($name)] = (array)$value;
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

    public function getRequestTarget()
    {
        return $this->uri->__toString();
    }

    public function withRequestTarget($requestTarget)
    {
        $new = clone $this;
        $new->uri = $this->uri->withPath($requestTarget);
        return $new;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function withMethod($method)
    {
        $new = clone $this;
        $new->method = $method;
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

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    public function getServerParams()
    {
        return $this->serverParams;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
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
