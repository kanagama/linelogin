<?php
namespace Kanagama\LineLogin;

use GuzzleHttp\Client;
use BadFunctionCallException;

final class LineLoginClient
{
    const LINE_STATE_SESSION_NAME = 'line_state_session';
    const LINE_TOKEN_SESSION_NAME = 'line_token_session';

    const LINE_AUTHORIZE_URL = 'https://access.line.me/oauth2/v2.1/authorize';
    const LINE_OAUTH_URL     = 'https://api.line.me/oauth2/v2.1/token';
    const LINE_VERIFY_URL    = 'https://api.line.me/oauth2/v2.1/verify';
    const LINE_REVOKE_URL    = 'https://api.line.me/oauth2/v2.1/revoke';
    const LINE_PROFILE_URL   = 'https://api.line.me/v2/profile';

    const HEADER_CONTENT_TYPE = [
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
    ];

    const DEFAULT_SCOPE = 'profile%20openid';

    private static $singleton = null;

    private $line_redirect_url  = null;
    private $line_client_id     = null;
    private $line_client_secret = null;

    private $guzzle = null;

    /**
     * 設定
     */
    public function __construct()
    {
        if (is_null($this->guzzle)) {
            $this->guzzle = new \GuzzleHttp\Client;
        }

        $this->setenv();
    }

    /**
     * 環境変数の値を設定する
     */
    private function setenv()
    {
        if (is_null($this->line_redirect_url) && getenv('LINE_REDIRECT_URL')) {
            $this->line_redirect_url = getenv('LINE_REDIRECT_URL');
        }
        if (is_null($this->line_client_id) && getenv('LINE_CLIENT_ID')) {
            $this->line_client_id = getenv('LINE_CLIENT_ID');
        }
        if (is_null($this->line_client_secret) && getenv('LINE_CLIENT_SECRET')) {
            $this->line_client_secret = getenv('LINE_CLIENT_SECRET');
        }
    }

    /**
     * シングルトン
     */
    public static function get()
    {
        if (is_null(self::$singleton)) {
            self::$singleton = new self();
        }
        return self::$singleton;
    }

    /**
     * 設定
     */
    public function set(array $options)
    {
        if (!empty($options['line_redirect_url'])) {
            $this->line_redirect_url = $options['line_redirect_url'];
        }
        if (!empty($options['line_client_id'])) {
            $this->line_client_id = $options['line_client_id'];
        }
        if (!empty($options['line_client_secret'])) {
            $this->line_client_secret = $options['line_client_secret'];
        }

        return $this;
    }

    /**
     * クロスサイトリクエストフォージェリ (opens new window)防止用の固有な英数字の文字列。
     * ログインセッションごとにウェブアプリでランダムに生成してください。
     * なお、URLエンコードされた文字列は使用できません。
     *
     * @return string
     */
    private function createState(): string
    {
        return substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, rand(10, 12));
    }

    /**
     * 必須チェック
     */
    private function checkMandatory()
    {
        switch (true) {
            case is_null($this->line_redirect_url):
            case is_null($this->line_client_id):
            case is_null($this->line_client_secret):
                throw new BadFunctionCallException;
        }
    }

    /**
     * ユーザーに認証と認可を要求するページへリダイレクト
     */
    public function redirect()
    {
        $this->checkMandatory();

        // 必須パラメータセット
        $gets = [
            'response_type' => 'code',
            'client_id'     => $this->line_client_id,
            'redirect_uri'  => $this->line_redirect_url,
            'state'         => $this->createState(),
            'scope'         => self::DEFAULT_SCOPE,
        ];

        // エンコードされたURLでは正常動作しないのでエンコード無しを生成
        $query = [];
        foreach ($gets as $key => $value) {
            $query[] = $key.'='.$value;
        }

        header('Location: '.self::LINE_AUTHORIZE_URL.'?'.implode('&', $query));
        exit;
    }
}