<?php

namespace app\modules\shorten\helpers;

final class UriHelper
{
	const URL_NETWORK_PATH_REF = 1;		// "//" <authority> <path> [<query>] [<fragment>] , move relative to current scheme
	const URL_ABSOLUTE_PATH_REF = 2;	// "/" <path> [<query>] [<fragment>] , move relative to current authority (userinfo+host+port)
	const URL_RELATIVE_PATH_REF = 3;	// <path> [<query>] [<fragment>] , move relative to current path

	private static $defaultPorts = [
		'http'  => 80,
		'https' => 443,
		'ftp' => 21,
		'gopher' => 70,
		'nntp' => 119,
		'news' => 119,
		'telnet' => 23,
		'tn3270' => 23,
		'imap' => 143,
		'pop' => 110,
		'ldap' => 389,
	];

	private static $charUnreserved = 'a-zA-Z0-9_\-\.~';
	private static $charSubDelims = '!\$&\'\(\)\*\+,;=';
	private static $replaceQuery = ['=' => '%3D', '&' => '%26'];

	public static function isLocalUrl($url, $baseHost)
	{
		if ("" !== $baseHost) {
			// Found base host in new URI
			if (false !== strpos($url, $baseHost)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Validate url for next actions
	 *
	 * @param string $url
	 * @param boolean $punycodes
	 * @param boolean $withIp
	 * @param string $defaultScheme
	 * @return bool|array If the url is empty or incorrect type.
	 */
	public static function isValidUrl($url, $punycodes = true, $withIp = false, $defaultScheme = 'http')
	{
		if ("" === $url or !is_string($url)) {
			return false;
		}
/*
		if (1 === preg_match('/[^a-z0-9а-яё.\-\/@:?&=#%_]/iu', $url)) {
			return false;
		}
*/
		$regexIp4 =
			// IP address exclusion
			// private & local networks
			'(?!10(?:\.\d{1,3}){3})' .
			'(?!127(?:\.\d{1,3}){3})' .
			'(?!169\.254(?:\.\d{1,3}){2})' .
			'(?!192\.168(?:\.\d{1,3}){2})' .
			'(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})' .
			// IP address dotted notation octets
			// excludes loopback network 0.0.0.0
			// excludes reserved space >= 224.0.0.0
			// excludes network & broacast addresses
			// (first & last IP address of each class)
			'(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])' .
			'(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}' .
			'(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))';

		// IPv6 RegEx - http://stackoverflow.com/a/17871737/273668
		$regexIp6 = '\[(' .
			'([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|' .         // 1:2:3:4:5:6:7:8
			'([0-9a-fA-F]{1,4}:){1,7}:|' .                        // 1::                              1:2:3:4:5:6:7::
			'([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|' .        // 1::8             1:2:3:4:5:6::8  1:2:3:4:5:6::8
			'([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|' . // 1::7:8           1:2:3:4:5::7:8  1:2:3:4:5::8
			'([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|' . // 1::6:7:8         1:2:3:4::6:7:8  1:2:3:4::8
			'([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|' . // 1::5:6:7:8       1:2:3::5:6:7:8  1:2:3::8
			'([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|' . // 1::4:5:6:7:8     1:2::4:5:6:7:8  1:2::8
			'[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|' .      // 1::3:4:5:6:7:8   1::3:4:5:6:7:8  1::8
			':((:[0-9a-fA-F]{1,4}){1,7}|:)|' .                    // ::2:3:4:5:6:7:8  ::2:3:4:5:6:7:8 ::8       ::
			'fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|' .    // fe80::7:8%eth0   fe80::7:8%1     (link-local IPv6 addresses with zone index)
			'::(ffff(:0{1,4}){0,1}:){0,1}' .
			'((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]).){3,3}' .
			'(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|' .         // ::255.255.255.255   ::ffff:255.255.255.255  ::ffff:0:255.255.255.255  (IPv4-mapped IPv6 addresses and IPv4-translated addresses)
			'([0-9a-fA-F]{1,4}:){1,4}:' .
			'((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]).){3,3}' .
			'(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])' .          // 2001:db8:3:4::192.0.2.33  64:ff9b::192.0.2.33 (IPv4-Embedded IPv6 Address)
			')\]';

		$myPattern = '~^' .			// begin string
			'(?:(https?)://)?' .		// scheme [1]
			'(?:(\S+(?::\S*)?)@)?' .	// (auth login:(password)) [2]
			'(?:' .
			( $punycodes === false ?
				'(' .	// host [3]
				'(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?){0,62}[a-z\x{00a1}-\x{ffff}0-9]{1,63})' .
				'(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?){0,62}[a-z\x{00a1}-\x{ffff}0-9]{1,63})*' .
				'(?:\.([a-z\x{00a1}-\x{ffff}]{2,63}))' .		// TLD [4]
				')'
				:
				// IDN support
				'(' .	// host [3]
				'(?:xn--[a-z0-9\-]{1,59}|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?){0,62}[a-z\x{00a1}-\x{ffff}0-9]{1,63}))' .
				'(?:\.(?:xn--[a-z0-9\-]{1,59}|(?:[a-z\x{00a1}-\x{ffff}0-9]+-?){0,62}[a-z\x{00a1}-\x{ffff}0-9]{1,63}))*' .
				'(?:\.(?:xn--[a-z0-9\-]{1,59}|([a-z\x{00a1}-\x{ffff}]{2,63}\.?)))' .		// TLD [4]
				')'
			) .
			( $withIp === false ? '' :
				'|(' . $regexIp4 .
				')|(' . $regexIp6 .
				')' ) .
			')' .
			'(?::(\d{2,5}))?' .		// (port) [5]
			'(?:([/?#]{1}[^\s]*))?' .		// (path, fragment, query) [6]
			'$~iuS';				// end string

		if (1 !== preg_match($myPattern, $url, $matches)) {
			return false;
		}

		// RFC 3986 2.2-2.3
		// US-ASCII
		// reserved    = gen-delims / sub-delims
		// gen-delims  = ":" / "/" / "?" / "#" / "[" / "]" / "@"
		// sub-delims  = "!" / "$" / "&" / "'" / "(" / ")" / "*" / "+" / "," / ";" / "="
		// unreserved  = ALPHA (%41-%5A and %61-%7A) / DIGIT (%30-%39) / "-" (%2D) / "." (%2E) / "_" (%5F) / "~" (%7E)
		// parse before percent encode!

		// [userinfo] + host + [port] = authority
		// userinfo    = *( unreserved / pct-encoded / sub-delims / ":" )
		// host        = IP-literal / IPv4address / registered name
		// reg-name    = *( unreserved / pct-encoded / sub-delims )

		// Non-ASCII characters must first be encoded according to UTF-8 [STD63], and then
		// each octet of the corresponding UTF-8 sequence must be percent-encoded to be represented as URI characters.
		// When a non-ASCII registered
   		// name represents an internationalized domain name intended for
		// resolution via the DNS, the name must be transformed to the IDNA
   		// encoding [RFC3490] prior to name lookup


		// path-absolute   ; begins with "/" but not "//"
      	// path-absolute = "/" [ segment-nz *( "/" segment ) ]

      	// segment       = *pchar
      	// segment-nz    = 1*pchar
		// pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"

		// query       = *( pchar / "/" / "?" )
		// is indicated by the first ("?") and terminated by  ("#") or by the end of the URI.
		// fragment    = *( pchar / "/" / "?" )

		// URI-reference = URI / relative-ref
		// URI = scheme ":" hier-part [ "?" query ] [ "#" fragment ]
		// absolute-URI  = scheme ":" hier-part [ "?" query ]
		// relative-ref = relative-part [ "?" query ] [ "#" fragment ]

		// hier-part = "//" authority path
		// relative-part = ["/"] path

		// TODO If a URI does not contain an authority component, then the path cannot begin
		// with two slash characters ("//").

		$urlParts = [
			'scheme' => ($matches[1] !== '' ? $matches[1] : $defaultScheme),
			'host' => $matches[3],
			'port' => $matches[5],
		];

		if ("" !== $matches[2]) {
			// todo auth not contain @
			$auth = explode(':', $matches[2]);
			if (empty($auth)) {
				$urlParts['login'] = $matches[2];
			} else {
				$urlParts['login'] = $auth[0];
				$urlParts['password'] = $auth[1];
			}
		}
		$tail = $matches[6];
		if ("" !== $tail) {
			$forQuery = explode('?', $tail, 2);
			if ($forQuery[1]) {
				$urlParts['path'] = $forQuery[0];
				$tail = $urlParts['query'] = $forQuery[1];
			} else {
				if (strncmp($tail, '?', 1) === 0) {
					$urlParts['path'] = '';
					$tail = $urlParts['query'] = $forQuery[0];
				} else {
					$urlParts['path'] = $forQuery[0];
					$urlParts['query'] = '';
				}
			}
			$forFragment = explode('#', $tail, 2);
			if ($forFragment[1]) {
				if (!empty($urlParts['query'])) {
					$urlParts['query'] = $forFragment[0];
					$urlParts['fragment'] = $forFragment[1];
				} else {
					$urlParts['path'] = $forFragment[0];
					$urlParts['fragment'] = $forFragment[1];
				}
			} else {
				$urlParts['fragment'] = '';
			}
		}

		/*
		// Host must contain a point and TLD domain length must be 2 characters and over
		// see http://www.iana.org/domains/root/db
		if (false === strrpos($uri->getHost(), '.') ||
			strlen(utf8_decode(substr($uri->getHost(), 0, strpos($uri->getHost(), '.')))) < 2 ) {
			return false;
		}

		// Host must not be of such a length
		// Faster analogous??? 	mb_strlen() - ??
		$len = strlen(utf8_decode($uri->getHost()));
		if ($len <= 3 || 200 <= $len) {
			return false;
		}
		*/

		return $urlParts;
	}

	public static function buildUrl($urlParts)
	{
		// todo
		$new = $urlParts['scheme']."://".$urlParts['host'];
		if (!empty($urlParts['port'])) $new = $new.":".$urlParts['port'];
		$new = $new.$urlParts['path'];
		if (!empty($urlParts['query'])) $new = $new."?".$urlParts['query'];
		if (!empty($urlParts['fragment'])) $new = $new."#".$urlParts['fragment'];
		
		return $new;
	}
	
	/*
	 * Convert relative links, images scr and form actions to absolute
	 *
	 * @param ElementFinder $page
	 * @param string $affectedUrl
	 */
	/*	public static function convertUrlsToAbsolute(ElementFinder $page, $affectedUrl) {

			$affected = new Uri($affectedUrl);

			$srcElements = $page->element('//*[@src] | //*[@href] | //form[@action]');
			$baseUrl = $page->value('//base/@href')->getFirst();

			foreach ($srcElements as $element) {
				$attributeName = 'href';

				if ($element->hasAttribute('action') === true and $element->tagName === 'form') {
					$attributeName = 'action';
				} else if ($element->hasAttribute('src') === true) {
					$attributeName = 'src';
				}

				$relative = $element->getAttribute($attributeName);

				# don`t change javascript in href
				if (preg_match('!^\s*javascript\s*:\s*!', $relative)) {
					continue;
				}

				if (parse_url($relative) === false) {
					continue;
				}

				if (!empty($baseUrl) and !preg_match('!^(/|http)!i', $relative)) {
					$relative = Uri::resolve(new Uri($baseUrl), $relative);
				}

				$url = Uri::resolve($affected, (string) $relative);
				$element->setAttribute($attributeName, (string) $url);
			}
	*/

	/**
	 * Resolves the request URI portion for the currently requested URL.
	 * This refers to the portion that is after the [[hostInfo]] part. It includes the [[queryString]] part if any.
	 * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
	 * @return string|boolean the request URI portion for the currently requested URL.
	 * Note that the URI returned is URL-encoded.
	 * @throws \HttpHeaderException if the request URI cannot be determined due to unusual server configuration
	 */
	public static function resolveRequestUri()
	{
		if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
			$requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif (isset($_SERVER['REQUEST_URI'])) {
			$requestUri = $_SERVER['REQUEST_URI'];
			if ($requestUri !== '' && $requestUri[0] !== '/') {
				$requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
			}
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
			$requestUri = $_SERVER['ORIG_PATH_INFO'];
			if (!empty($_SERVER['QUERY_STRING'])) {
				$requestUri .= '?' . $_SERVER['QUERY_STRING'];
			}
		} else {
			throw new \HttpHeaderException('Unable to determine the request URI.');
		}
		return $requestUri;
	}


/**
	 * Returns a value indicating whether a URL is relative.
	 * A relative URL does not have host info part.
	 *
	 * method can't correctly determine relative path reference because
	 * that may be url beginning with host
	 *
	 * @param string $url the URL to be checked
	 * @return boolean whether the URL is relative
	 */
	public static function isRelative($url)
	{
		if (1 === preg_match('#(?:^mailto:)|(?:^tel:).*$#i', $url)) {
			return false;
		}
		if (strpos(substr($url, 0, 8), '://') !== false) return false;

		// A relative reference that begins with two slash characters is termed
		// a network-path reference; such references are rarely used
		// Такой тип ссылок применяется не часто, смысл заключается в переходе по указанной ссылке
		// с применением текущей схемы.
		if (strncmp($url, '//', 2) === 0) return self::URL_NETWORK_PATH_REF;

		if (strncmp($url, '/', 1) === 0) return self::URL_ABSOLUTE_PATH_REF;
		return self::URL_RELATIVE_PATH_REF;
	}

	public static function getAbsoluteFromRelAbs($relLink, $baseHref)
	{
		return rtrim($baseHref, '/') . $relLink;
	}
	public static function getAbsoluteFromRelNet($relLink, $scheme)
	{
		return $scheme . ':' . $relLink;
	}




	public static function getHost($url)
	{
		$url = ltrim($url);

		// Remove protocol from $url
		$start_pos = strpos($url, '://') + 3;
		if (false !== $start_pos && 8 <= $start_pos) {
			$remains = substr($url, $start_pos);

			// Remove page and directory references
			if(stristr($remains, "/")) {
				return substr($remains, 0, strpos($remains, "/"));
			}
			return $remains;
		}
		return '';
	}

	/**
	 *	Not mine function
	 * @param $url string
	 * @return boolean
	 */
	public static function isUrlExist($url = "") {

		if ("" === $url or !is_string($url)) {
			throw new \InvalidArgumentException('Url must be not empty and string.');
			//return false;
		}

		$url = @parse_url($url);
		if ($url === false) {
			return false;
		}

		$url = array_map('trim', $url);
		$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
		$path = (isset($url['path'])) ? $url['path'] : '';

		if ($path == '') {
			$path = '/';
		}

		$path .= ( isset ( $url['query'] ) ) ? "?$url[query]" : '';

		if ( isset ( $url['host'] ) AND $url['host'] != gethostbyname ( $url['host'] ) ) {
			if ( PHP_VERSION >= 5 ) {
				$headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
			}
			else {
				$fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);
				if (!$fp) {
					return false;
				}
				fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
				$headers = fread ( $fp, 128 );
				fclose ( $fp );
			}
			$headers = ( is_array ( $headers ) ) ? implode ( "\n", $headers ) : $headers;
			return ( bool ) preg_match ( '#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers );
		}
		return false;
	}
}