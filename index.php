<?php

/**
 * The usage is the same as HTTP_Request2 except that it will implement
 * caching for the METHOD_GET routines.
 */
$request = new FileCachedRequest('http://mysite.com/rest/resource/1234');
$result = $request->send()->getBody();
?>
