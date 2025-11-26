<?php
// proxy.php - simple testing proxy (for practice only)

// basic allowlist: only permit certain hosts endpoints to avoid abuse
$allowed = [
  'httpbin.org',
  'example.com',
  'jsonplaceholder.typicode.com',
  'httpstat.us'
];

if (empty($_GET['url'])) {
    http_response_code(400);
    echo "Missing url param";
    exit;
}

$target = $_GET['url'];
$parts = parse_url($target);
if (!$parts || !isset($parts['host']) || !in_array($parts['host'], $allowed)) {
    http_response_code(403);
    echo "Target not allowed for testing.";
    exit;
}

$ch = curl_init($target);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_USERAGENT => 'Mozilla/5.0 (Test-Proxy)',
  CURLOPT_HTTPHEADER => [
    'Accept: */*',
    'Accept-Language: en-US,en;q=0.9'
  ],
  CURLOPT_TIMEOUT => 15,
  CURLOPT_ENCODING => '', // accept gzip/deflate
]);

$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'text/html';
curl_close($ch);

// if upstream didn't return HTML, pass it through raw
header("Content-Type: {$contentType}");
http_response_code($code);

// OPTIONAL: inject small JS redirect for HTML so you can practice "proxy -> redirect"
if (stripos($contentType, 'text/html') !== false && !empty($_GET['inject_redirect'])) {
    // inject before </body>
    $redirect = htmlspecialchars($_GET['inject_redirect'], ENT_QUOTES, 'UTF-8');
    $inject = "<script>setTimeout(()=>{window.location.href='{$redirect}'}, 100);</script>";
    $body = preg_replace('/<\/body>/i', $inject . '</body>', $body, 1) ?? $body . $inject;
}

echo $body;
