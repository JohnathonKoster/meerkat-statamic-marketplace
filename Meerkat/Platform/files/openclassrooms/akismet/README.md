Akismet
-------
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/dbe229b7-2d94-4b89-ae1b-94acb80ee91d/mini.png)](https://insight.sensiolabs.com/projects/dbe229b7-2d94-4b89-ae1b-94acb80ee91d)
[![Build Status](https://travis-ci.org/OpenClassrooms/Akismet.svg)](https://travis-ci.org/OpenClassrooms/Akismet)
[![Coverage Status](https://coveralls.io/repos/OpenClassrooms/Akismet/badge.svg?branch=master)](https://coveralls.io/r/OpenClassrooms/Akismet?branch=master)

This is a PHP5 library that provides [Akismet Spam Protection service](https://akismet.com/) functionality in your application.

## Installation
The easiest way to install Akismet Library is via [composer](http://getcomposer.org/).

Create the following `composer.json` file and run the `php composer.phar install` command to install it.

```json
{
    "require": {
        "openclassrooms/akismet": "*"
    }
}
```
```php
<?php
require 'vendor/autoload.php';

use OpenClassrooms\Akismet\Services\AkismetService;

//do things
```

## Basic Usage
The `AkismetServiceImpl` class needs to set a client to communicate with Akismet Spam Protection service. This library provides a client based on GuzzleHttp\Client, though you can implement your own client. Then, you can set the client using the ```AkismetServiceImpl::setClient()``` method.

### The Models
#### Comment
This Library defines a "Comment" interface which must be passed as an argument into the different AkismetService methods.
```php
use OpenClassrooms\Akismet\Models\Comment;

$comment = new CommentImpl();
```
#### Comment Builder
This library provides a Builder to create the Comment: 
```php
use OpenClassrooms\Akismet\Models\CommentBuilder;

$comment = $commentBuilder->create()
                          ->withUserIp(CommentStub::USER_IP)
                          ->withUserAgent(CommentStub::USER_AGENT)
                          ->withReferrer(CommentStub::REFERRER)
                          ->withPermalink(CommentStub::PERMALINK)
                          ->withAuthorName(CommentStub::AUTHOR_NAME)
                          ->withAuthorEmail(CommentStub::AUTHOR_EMAIL)
                          ->withContent(CommentStub::CONTENT)
                          ->build();
```
#### Service
```php
Use OpenClassrooms\Akismet\Services\Impl\AkismetServiceImpl;

$akismetService = new AkismetServiceImpl();

// commentCheck method needs only one parameter, which has to be the Comment object
if ($akismetService->commentCheck($comment)) {
 // store the comment and mark it as spam (in case of a misdiagnosis).
} else {
 // store the comment normally
}
```
to submit misdiagnosed spam and ham, which improves the system for everybody.
```php
$akismetService->submitSpam($comment);
```
And
```php
$akismetService->submitHam($comment);
```

## Beyond this Library
If you plan to use Akismet Library in a Symfony2 project, check out the [AkismetBundle](https://github.com/OpenClassrooms/AkismetBundle). The bundle provides an easy configuration option for this library.
