<?php
// no cache
header('Pragma: no-cache');
// HTTP/1.1
header('Cache-Control: no-cache, must-revalidate');
// date in the past
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
// define XML content type
header('Content-type: text/xml');
// print XML header
print '<?xml version="1.0"?>';
// prepare demo progress value
$progress = (mktime() % 50) * 2;
?>
<DOCUMENT><PROGRESS><?php print $progress ?></PROGRESS></DOCUMENT>