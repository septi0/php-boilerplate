<?php

class Uri
{
    private $scheme = '';
    private $host = '';
    private $port = null;
    private $path = '';
    private $query = '';
    private $fragment = '';
    private $userInfo = '';

    public function __construct($server = [])
    {
        $this->scheme = $this->detectScheme($server);
        $this->host = $server['HTTP_HOST'] ?? ($server['SERVER_NAME'] ?? '');
        $this->port = $this->detectPort($server);
        $this->path = $server['REQUEST_URI'] ?? '/';
        $this->query = parse_url($this->path, PHP_URL_QUERY) ?? '';
        $this->path = parse_url($this->path, PHP_URL_PATH) ?? '/';
    }

    private function detectScheme($server)
    {
        if (!empty($server['HTTPS']) && $server['HTTPS'] !== 'off') {
            return 'https';
        }
        return 'http';
    }

    private function detectPort($server)
    {
        $port = $server['SERVER_PORT'] ?? null;
        if ($port === '80' && $this->scheme === 'http') {
            return null;
        }
        if ($port === '443' && $this->scheme === 'https') {
            return null;
        }
        return $port !== null ? (int) $port : null;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getAuthority()
    {
        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }
        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }

    public function getUserInfo()
    {
        return $this->userInfo;
    }

    public function withUserInfo($user, $password = '')
    {
        $clone = clone $this;
        $clone->userInfo = $password ? "$user:$password" : $user;
        return $clone;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function withHost(string $host)
    {
        $clone = clone $this;
        $clone->host = $host;
        return $clone;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function withPort($port)
    {
        $clone = clone $this;
        $clone->port = $port;
        return $clone;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function withPath(string $path)
    {
        $clone = clone $this;
        $clone->path = $path;
        return $clone;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function withQuery($query)
    {
        $clone = clone $this;
        $clone->query = ltrim($query, '?');
        return $clone;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function withFragment($fragment)
    {
        $clone = clone $this;
        $clone->fragment = ltrim($fragment, '#');
        return $clone;
    }

    public function __toString()
    {
        $uri = $this->scheme . '://';

        if ($this->userInfo !== '') {
            $uri .= $this->userInfo . '@';
        }

        $uri .= $this->host;

        if ($this->port !== null) {
            $uri .= ':' . $this->port;
        }

        $uri .= $this->path;

        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }
}
