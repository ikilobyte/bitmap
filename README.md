# bitmap
简易版bitmap

## 说明
* 学习交流，不能用于生产环境，生产环境请使用redis

## 使用
```php
<?php

// 会在当前data目录下持久化数据
$bitmap = new Bitmap('./data');

// setbit
$bitmap -> set('key',10,1);
$bitmap -> set('key',12,1);
$bitmap -> set('key',16,0);

// getbit
$value = $bitmap -> get('key',10);
var_dump($value); // 1
