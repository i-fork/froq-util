<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-util
 */
declare(strict_types=1);

use froq\util\Util;

/**
 * Build a query string.
 *
 * @param  array  $data
 * @param  string $ignored_keys
 * @param  bool   $remove_tags
 * @return string
 * @since  4.0
 */
function build_query_string(array $data, string $ignored_keys = '', bool $remove_tags = false): string
{
    return Util::buildQueryString($data, $ignored_keys, $remove_tags);
}

/**
 * Parse a query string.
 *
 * @param  string $query
 * @param  string $ignored_keys
 * @param  bool   $remove_tags
 * @return array
 * @since  4.0
 */
function parse_query_string(string $query, string $ignored_keys = '', bool $remove_tags = false): array
{
    return Util::parseQueryString($query, $ignored_keys, $remove_tags);
}

/**
 * Build a cookie.
 *
 * @param  string      $name
 * @param  string|null $value
 * @param  array|null  $options
 * @return string|null
 * @since  4.0
 */
function build_cookie(string $name, string|null $value, array $options = null): string|null
{
    if ($name == '') {
        trigger_error('No cookie name given');
        return null;
    }

    $cookie = ['name' => $name, 'value' => $value] + array_replace(
        array_pad_keys([], ['expires', 'path', 'domain', 'secure', 'httponly', 'samesite']),
        array_map_keys($options ?? [], 'strtolower')
    );

    extract($cookie);

    $ret = rawurlencode($name) . '=';

    if ($value === '' || $value === null || $expires < 0) {
        $ret .= sprintf('n/a; Expires=%s; Max-Age=0', gmdate('D, d M Y H:i:s \G\M\T', 0));
    } else {
        $ret .= rawurlencode($value);

        // Must be given in-seconds format.
        if ($expires !== null) {
            $ret .= sprintf('; Expires=%s; Max-Age=%d', gmdate('D, d M Y H:i:s \G\M\T', time() + $expires),
                $expires);
        }
    }

    $path     && $ret .= '; Path=' . $path;
    $domain   && $ret .= '; Domain=' . $domain;
    $secure   && $ret .= '; Secure';
    $httponly && $ret .= '; HttpOnly';
    $samesite && $ret .= '; SameSite=' . $samesite;

    return $ret;
}

/**
 * Parse a cookie (from a header line).
 *
 * @param  string $cookie
 * @return array
 * @since  4.0
 */
function parse_cookie(string $cookie): array
{
    $ret = [];

    foreach (split(';', $cookie) as $i => $component) {
        $component = trim($component);
        if ($component) {
            [$name, $value] = split('=', $component, 2);
            if ($i == 0) {
                $ret['name']  = isset($name)  ? rawurldecode($name)  : $name;
                $ret['value'] = isset($value) ? rawurldecode($value) : $value;
                continue;
            }

            $name = strtolower($name ?? '');
            if ($name) {
                switch ($name) {
                    case 'secure':   $value = true;               break;
                    case 'httponly': $value = true;               break;
                    case 'samesite': $value = strtolower($value); break;
                }
                $ret[$name] = $value;
            }
        }
    }

    return $ret;
}

/**
 * Build a URL.
 *
 * @param  array $data
 * @return string|null
 * @since  4.0
 */
function build_url(array $data): string|null
{
    if (!$data) {
        return null;
    }

    $url = '';

    // Syntax: https://tools.ietf.org/html/rfc3986#section-3
    if (isset($data['scheme'])) {
        $url .= $data['scheme'] . '://';
    }

    if (isset($data['authority'])) {
        $url .= $data['authority'];
    } else {
        $authority = null;
        isset($data['user']) && $authority = $data['user'];
        isset($data['pass']) && $authority .= ':'. $data['pass'];

        // Separate.
        if ($authority !== null) {
            $authority .= '@';
        }

        isset($data['host']) && $authority .= $data['host'];
        isset($data['port']) && $authority .= ':'. $data['port'];

        $url .= $authority;
    }

    if (isset($data['queryParams'])) {
        $data['query'] = http_build_query($data['queryParams']);
    }

    isset($data['path']) && $url .= $data['path'];
    isset($data['query']) && $url .= '?'. $data['query'];
    isset($data['fragment']) && $url .= '#'. $data['fragment'];

    return $url;
}
