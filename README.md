# yii2-clamav
yii2-clamav is a Yii2 component to clamd / clamscan that allows you to scan files and directories using ClamAV.
it's a fork of https://github.com/vanagnostos/php-clamav adapted for Yii2

## Installation
```Shell
composer require cacko/yii2-clamav
```

## Configuration

#### via unix socker
```PHP
<?php
    'components' => [
    .....
        'clamav' => [
            'class' => 'Cacko\ClamAv\Scanner',
            'driver' => 'clamd_local',
            'socket' => '/var/run/clamav/clamd.sock'
        ],
```
#### using executable
```PHP
<?php
    'components' => [
    .....
        'clamav' => [
            'class' => 'Cacko\ClamAv\Scanner',
            'driver' => 'clamscan',
            'executable' => '/usr/local/bin/clamdscan'
        ],
```
#### using local tcp socket
```PHP
<?php
    'components' => [
    .....
        'clamav' => [
            'class' => 'Cacko\ClamAv\Scanner',
            'driver' => 'clamd_local',
            'host' => '127.0.0.1',
            'port' => 3310
        ],
```
#### using remote tcp socket
```PHP
<?php
    'components' => [
    .....
        'clamav' => [
            'class' => 'Cacko\ClamAv\Scanner',
            'driver' => 'clamd_remote',
            'host' => '192.168.0.10',
            'port' => 3310
        ],
```
#### dummy driver - does nothing
```PHP
<?php
    'components' => [
    .....
        'clamav' => [
            'class' => 'Cacko\ClamAv\Scanner',
            'driver' => 'dummy',
        ],
```

## Usage

#### Scan file
```PHP
<?php

$result = Yii::$app->clamav->scan('my_file.txt');

```

#### Scan text
```PHP
<?php

$result = Yii::$app->clamav->scanBuffer(file_get_contents('my_file.txt'));

```

#### Scan file as object
```PHP
<?php

$result = Yii::$app->clamav->scanResource(new SplFileObject('my_file.txt'), 'rb');

```
