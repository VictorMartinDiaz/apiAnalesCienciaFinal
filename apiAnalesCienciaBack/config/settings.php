<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Settings
$settings = [];

// Path settings
$settings['root'] = dirname(__DIR__);
$settings['temp'] = $settings['root'] . '/tmp';
$settings['public'] = $settings['root'] . '/public';

// Error Handling Middleware settings
$settings['error_handler_middleware'] = [
    // Should be set to false in production
    'display_error_details' => false,
    // Parameter is passed to the default ErrorHandler
    // View in rendered output by enabling the "displayErrorDetails" setting.
    // For the console and unit tests it should be disable too
    'log_errors' => false,
    // Display error details in error log
    'log_error_details' => true,
];

$settings['router'] = [
    // Should be set only in production
    'cache_file' => '',
];

// Application settings
$settings['app'] = [
    'secret' => $_ENV['JWT_SECRET'],
];

// Logger settings
$settings['logger'] = [
    'name' => 'app',
    'path' => $settings['root'] . '/logs',
    'filename' => 'app.log',
    'level' => \Monolog\Logger::ERROR,
    'file_permission' => 0775,
];
$settings['error_handler_middleware']['display_error_details'] = true;
$settings['error_handler_middleware']['log_errors'] = true;
$settings['logger']['level'] = \Monolog\Logger::DEBUG;

// JWT
$settings['jwt'] = [

    // The issuer name
    'issuer' => 'tdw-upm',

    // OAuth2: client-id
    'client-id' => 'upm-tdw-aciencia',

    // Max lifetime in seconds
    'lifetime' => 14400,

    // The private key
    'private_key' => '-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAu0oad+YHzWlQm1f0vRsLkMRvNULpkRJKts/Sttb6txqwQdnw
0jIX2RJPXgzNyZmf8BJHEzGcht6gWmC1Ki/Mi4VBpiudKh3oah44w8USmdc2Mrrj
h7f7ESEvzmihnYTMDKUup1ktHerrEvLd2m2yHQjn27t8hgMuReYvRkJGLndiNlEg
gLPF7mF76nVlN/c1qN3B7pLQCTTZHTrM3lAUQexZYPaVucNdlPkEbbnrHwiSA6gM
rPvT6+Ln2zWE6DE3z0W9sjgbGPKlnpNmO/vlap1xT16rWQOvR5X+F5ocQzQwWgEo
TEQDmcHu15GT4maPsBhvXToL27kMKXjeGJ2V9wIDAQABAoIBAQCTxbM+mNvdIdQ8
zVhIANfOQH8yOfpJwXH77dvm8ZZd9IvPWWMepfGVD1JZ9aZFA5Zi+DjmFwXWkD9b
L+ShRZeRGfIjZ1QZEAH6AKBvLsYvZdPYkQbHZc2NxW6P4JRr0YSiEY7O8Zice2dA
yylql5SqPgWapMJqhoXzFtyEBfST4bneMszwUX47CCn8AJKqvm/lQ3JiVbhdKgv+
khCeui/TNm/4HypDKjklh0KVsq5xov/5VWGMuU7g6BYUoq6opblRDj9xa1Ls1T7P
Mz9Zey8qKIbRW4LE1iswCPUp+htMqIT7fFnTOH4BimvnhuRRpK02AWoDIqZ0TTnx
umEBpS/pAoGBAOp+Fy0kzA88LwXSTpNQj050S55Y58CLrTeNW6BvcNgxGIo2xNg8
CDhLvyy5mR5zAj44bhVWqkQHybnU6EZhH6o8P1EP10Z2XqQ3v/W+Sk6VIr+dO3sP
c7o29GhHBdcu0PJ/szcjLZT3B0u/fZgBNSGbNVJR+/1GVjMQqBkALOyVAoGBAMx3
rgGU/tN9/8KgrnpbkMiWFGurSu7C5KfP0NcRWknd1UED47G05nBOdPgfHj/H8eb+
am9VbuCr/wtvVCyD2UIC+W5YDaVW55OSQQChIPa4DaiAuNmjFSr5GC4ixAnf2I1b
eob5KZh0atVOHep63s6fmFd5q9j4xQurXfFZs0lbAoGATl6LwmOs616S3KA38JYY
/wBxEV/nPHuyDYHp4Im+LhLif7bkPNx7Zs0x/HGfEgUf98mGSQ1o5EmyCrB0XKkz
GwL9qkrgCMWgxcN4HVpWnULMlTuoWG2GoPKi5oLuGcekv5ccP047erDAuHksMXQd
3LhxrqyFylUKlBB6Dbj4Sq0CgYAiCbJf9QvO7WTMY69oEyIxIjrYCbX5tVwXS5M6
mlrrfRBpOFqJVNIf1A/I1nVUrNZqW+QgEJrasAdPQgNDPdfHE3OumN38rlDy0iAc
GLbCG7W6XWNoZ6u4catC0urLsgX80kO9gHEaPhci11RHmpjI0Oytc0XWYuN6o9aJ
vSMJjQKBgHcliUYCH+1oNaG0FDL+xK47yamLNlKJAn/PIoDpaKtzdyGfSgC5UiKK
JXorF8n8g7gLS8332R+GrZZpHaEhi4kRaDfyWw0HIMOUceUg6FZSE1TknsCnNJrF
tWB3+osfQWdzydRY4geZlRovsupLOnVWutbaH4Xkmv59COgno1yW
-----END RSA PRIVATE KEY-----',

    'public_key' => '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAu0oad+YHzWlQm1f0vRsL
kMRvNULpkRJKts/Sttb6txqwQdnw0jIX2RJPXgzNyZmf8BJHEzGcht6gWmC1Ki/M
i4VBpiudKh3oah44w8USmdc2Mrrjh7f7ESEvzmihnYTMDKUup1ktHerrEvLd2m2y
HQjn27t8hgMuReYvRkJGLndiNlEggLPF7mF76nVlN/c1qN3B7pLQCTTZHTrM3lAU
QexZYPaVucNdlPkEbbnrHwiSA6gMrPvT6+Ln2zWE6DE3z0W9sjgbGPKlnpNmO/vl
ap1xT16rWQOvR5X+F5ocQzQwWgEoTEQDmcHu15GT4maPsBhvXToL27kMKXjeGJ2V
9wIDAQAB
-----END PUBLIC KEY-----',

];

// Load environment configuration
if (file_exists(__DIR__ . '/../../env.php')) {
    require __DIR__ . '/../../env.php';
} elseif (file_exists(__DIR__ . '/env.php')) {
    require __DIR__ . '/env.php';
}

// Unit-test and integration environment (Travis CI)
if (defined('APP_ENV')) {
    require __DIR__ . '/' . basename(APP_ENV) . '.php';
}

return $settings;
