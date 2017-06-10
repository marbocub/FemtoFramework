<?php

class FemtoFramework {
    /*
     * private変数
     *
     * array $action_map    : アクションマップの連想配列
     *       ['method']     : HTTPメソッド名（例："GET","PUT"）
     *       ['pattern']    : パス名（例：/hello/{id}, {id}は変数キー名'id'を表す）
     *       ['function']   : コールバックする無名関数（例：function(){echo "Hello";}）
     *
     * array $args          : 変数マップの連想配列
     */
    private $action_map = array();  // 初期値は空配列
    private $args = array();        // 初期値は空配列

    // パターン中の変数部分を表すマッチング文字列
    const VARIABLE_REGEX = "\{([a-zA-Z0-9_]*?)\}";
    const QUOTED_VARIABLE_REGEX = "\\\{([a-zA-Z0-9_]*?)\\\}";


    /*
     * public methods
     * 
     * void get(string $pattern, callback $function);
     * void put(string $pattern, callback $function);
     * void add(string $method, string $pattern, callback $function);
     * : アクションマップへ登録する
     *
     * string arg(string $key);
     * : パス中の変数を取得する
     *
     * void run(void);
     * : パスとアクションマップをマッチさせて一致したらコールバックする
     */
    function get($pattern, $function) {
        self::add("GET", $pattern, $function);
    }

    function put($pattern, $function) {
        self::add("PUT", $pattern, $function);
    }

    function add($method, $pattern, $function) {
        $this->action_map[] = array(
            "method"    => strtoupper($method),
            "pattern"   => $pattern,
            "function"  => $function,
        );
    }

    function arg($key) {
        return empty($this->args[$key]) ? "" : $this->args[$key];
    }

    function run() {
        // URL解析したパス名
        $path = self::path();

        // アクションマップを1セットずつチェック
        foreach ($this->action_map as $map) {

            // HTTPメソッドが一致するか？
            if ($_SERVER['REQUEST_METHOD'] != $map['method'])
                continue;

            // パス階層数が一致するか？
            if (count(explode("/", $path)) != count(explode("/", $map['pattern'])))
                continue;

            // パスがパターンとマッチするか？
            if (!preg_match(self::pathpattern($map['pattern']), $path, $values)) 
                continue;
            array_shift($values);   // 値配列を得る

            /*
             * ここまで来たらパターンと一致している！
             */

            // パターンからキー配列（変数名の配列）を得る
            preg_match_all("~".self::VARIABLE_REGEX."~", $map['pattern'], $varnames);
            $keys = $varnames[1];   // キー配列を得る

            // キー配列と値配列から変数マップを生成
            $this->args = array_combine($keys, $values);

            // アクションをコールバック
            $map['function']();

            // 最初にマッチした所で打ち止め
            return;
        }

        // どのパターンとも一致しなかったとき
        echo "Femto Framework ERROR<br>";
        echo "マッチするパターンがありません。";
    }


    /*
     * private methods
     *
     * string path(void)    : HTTPリクエストから相対パス名を得る
     * 
     * string pathpattern(string $pattern)
     *                      : 変数値を得るためのパスマッチング文字列を生成する
     */
    private function path() {
        // サーバ変数から相対パス名を得る
        if (!empty($_SERVER['PATH_INFO'])) {
            $path = $_SERVER['PATH_INFO'];
        } else {
            $path = preg_replace("~^".dirname($_SERVER['PHP_SELF'])."~", "", $_SERVER['REQUEST_URI']);
            $path = preg_replace("~\?".$_SERVER['QUERY_STRING']."$~", "", $path);
        }

        // 重複するパス区切り文字を無視
        $path = preg_replace("~(/+)~", "/", $path);

        // パス名は'/'から開始させる
        return "/" . ltrim($path, "/");
    }

    private function pathpattern($pattern) {
        $d = "~";   // デミリタ
        $quote = preg_quote($pattern, $d);
        $temp1 = preg_replace($d.self::QUOTED_VARIABLE_REGEX.$d, "(.*?)", $quote);
        $pathpattern = preg_replace($d."^(.*)$".$d, $d."^$1$".$d, $temp1);
        return $pathpattern;
    }
}
?>