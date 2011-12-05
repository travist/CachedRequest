<?php

require_once 'CachedResponse.php';

class HTTP_CachedRequest extends HTTP_Request2 {

  // By default, this simply implements a static cache.  Other classes can
  // derive from this class to implement more persistent caching like File,
  // API, or MemCache.
  protected $static_cache = array();

  // The constructor.
  function __construct($url = null, $method = self::METHOD_GET, array $config = array()) {

    // Set default cache timeout to 1hr.
    $config['cache_timeout'] = isset($config['cache_timeout']) ? $config['cache_timeout'] : 3600;

    // Add the configuration for this class to the configuration.
    $this->config = array_merge($this->config, array(
      'cache_name' => $config['cache_name'],
      'cache_timeout' => $config['cache_timeout']
    ));

    // Call the parent constructor.
    parent::__construct($url, $method, $config);
  }

  /**
   * Sets the cache name.
   *
   * Derived classes can implement this hook when the cache name is set and before
   * certain checks are made.
   */
  public function set_cache_name($cache_name) {
  }

  /**
   * Return if we should cache.
   */
  public function should_cache() {
    return ($this->method == HTTP_Request2::METHOD_GET);
  }

  /**
   * Return if this cache is valid.
   *
   * @return type
   */
  public function cache_valid() {
    return isset($this->static_cache[$this->config['cache_name']]);
  }

  /**
   * Cache the response.
   */
  public function cache_response($body) {
    $this->static_cache[$this->config['cache_name']] = $body;
  }

  /**
   * Returns the cache.
   */
  public function get_cache() {
    return $this->static_cache[$this->config['cache_name']];
  }

  // Override the send function to only send if the cache exists and is valid.
  public function send() {

    // Make sure we have a cache_name.
    if (!$this->config['cache_name']) {

      // The cache name can be a HASH of the URL.
      $this->config['cache_name'] = md5($this->url->getURL());
    }

    // Set the cache name.
    $this->set_cache_name($this->config['cache_name']);

    // Only send if we shouldn't cache or if the cache is invalid.
    $response = null;
    if (!$this->should_cache() || !$this->cache_valid()) {
      $response = parent::send();
    }

    // Return our cached response.
    return new HTTP_CachedResponse($this, $response);
  }
}
?>
