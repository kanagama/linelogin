# Line Login

Line Login拡張

## 要件
* PHP 7.1+
* ext-mbstring

## 使い方

1.2 どちらかで必要なパラメータを設定して実行するだけ

### 1.環境変数を設定する
```
export LINE_REDIRECT_URL="xxxxxxx"
export LINE_CLIENT_ID="xxxxxxx"
export LINE_CLIENT_SECRET="xxxxxxx"
```

```php
LineLoginClient::get()->authRedirect();
```

### 2. set()で設定
```php
$options = [
    'line_redirect_url'  => 'xxxxxx',
    'line_client_id'     => 'xxxxxx',
    'line_client_secret' => 'xxxxxx',
];

LineLoginClient::get()->set($options)->authRedirect();
```

## レスポンス
