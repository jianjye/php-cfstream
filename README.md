# PHP - Cloudflare Stream

PHP CFStream is a PHP TUS client that makes it easy to send video files to Cloudflare Stream. 

- Simple interface for:
  - Creation of resource endpoints
  - Upload videos
  - Get status of uploads
  - Deletion of videos
  - Whitelist access
  - Get embed codes
- Implemented in pure PHP and CURL with the help of GuzzleHttp Client
- Tightly integrated with Cloudflare Stream
- Does not support:
  - Resume uploads

Inspired by the PHP Tus Client

1. composer require jjsee/php-cfstream
2. $cfstream = new CFStream($email, $zone, $key)

1. composer require jjsee/php-cfstream
2. Edit .env
3. php artisan vendor:publish
4. $cfstream = new CFStreamLaravel();