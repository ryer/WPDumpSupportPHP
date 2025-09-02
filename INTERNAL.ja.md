# WPDumpSupportPHP 内部ドキュメント (日本語)

このドキュメントは、`WPDumpSupportPHP` プログラムの構造と処理の流れについて解説します。

## 概要

このプログラムは、WordPressの `wp-json` APIからエクスポートされたJSON形式のダンプデータをPHPで扱うためのライブラリです。
巨大なJSONファイルを効率的に処理するために、ストリーミングパーサー `JsonMachine` を利用しており、メモリ使用量を抑えつつ、投稿、ページ、ユーザー、メディアなどのオブジェクト間の関連性を解決する機能を提供します。

## 主要なクラスと役割

### `WPDumpSupport\WPDump`

このライブラリの利用者が直接操作する中心的なクラスです。内部に `ReferenceResolver` のインスタンスを保持し、その機能を利用者に対して透過的に提供します。

**主なメソッド:**

*   `__construct(array $params)`: コンストラクタ。`jsonDir` (JSONダンプディレクトリ)、`cacheDir` (キャッシュディレクトリ)、`customDefs` (カスタム定義) を含む連想配列を受け取ります。これらのパラメータは内部でインスタンス化される `ReferenceResolver` に渡されます。
*   `load()`: 内部の `ReferenceResolver` の `load()` メソッドを呼び出し、キャッシュを生成します。
*   `streamPosts()`, `streamPages()` など: `__call` マジックメソッドを介して、内部の `ReferenceResolver` の `stream()` メソッドを呼び出し、解決済みのオブジェクトをジェネレータとして返します。
*   `countPosts()`, `countPages()` など: `__call` マジックメソッドを介して、内部の `ReferenceResolver` の `count()` メソッドを呼び出し、データ件数を返します。

**データタイプの定義と拡張性**

`WPDump` は、カスタム投稿タイプやカスタムタクソノミーの定義を受け付けますが、その解釈と処理は内部の `ReferenceResolver` に委譲されます。

さらに、コンストラクタのパラメータ `customDefs` を利用して、カスタム投稿タイプやカスタムタクソноミーを簡単に追加できます。定義は以下のような形式の連想配列です。

```php
$params = [
    'customDefs' => [
        // カスタム投稿タイプ「書籍」
        'my_books' => [
            'cache' => 'file',          // ファイルキャッシュを利用
            'class' => MyBook::class,   // 対応するPHPクラス
            'file' => 'my_books.json'   // JSONファイル名
        ],
        // カスタムタクソノミー「ジャンル」
        'genre' => [
            'cache' => 'memory',        // インメモリキャッシュを利用
            'class' => Genre::class,
            'file' => 'genres.json'
        ]
    ]
];
```

データ定義の解釈、キャッシュ戦略の選択、および実際のキャッシュ処理は、`ReferenceResolver` が担当します。

### `WPDumpSupport\WPJsonLoader`

`JsonMachine` ライブラリのラッパーとして機能し、JSONファイルをストリーミングで読み込み、対応する `WPObject` のサブクラスのインスタンスを生成する役割を担います。このクラスは `ReferenceResolver` から利用されます。

**主なメソッド:**

*   `streamWpJson($filePath, $className)`: 指定されたJSONファイルをストリーミングで読み込み、指定されたクラス名のオブジェクトを `yield` するジェネレータです。

### `WPDumpSupport\Object\WPObject`

WordPressの各データオブジェクト（投稿、ユーザーなど）の基底となる抽象クラスです。

**主な機能:**

*   `processSource()` メソッドは、JSONから読み込んだ連想配列を内部の `$source` プロパティに保持します。
*   プロパティへのアクセスは `__get` マジックメソッドを介して行われます。未定義のプロパティ (`$post->title` など) にアクセスすると、内部の `$source` 配列から対応する値が動的に返されます。WordPress REST APIの多くのフィールド（例: `title`, `content`）は `['rendered' => '...']` という形式の連想配列になっていますが、`__get` は自動的に `rendered` キーの値を返すため、利用者は `$post->title` のように直感的にアクセスできます。
*   リファレンス解決の際には、`resolveReferences(ReferenceResolver $resolver)` メソッドが `ReferenceResolver` から呼び出されます。`__set` マジックメソッドを利用して `$post->author = $userObject` のようにプロパティが直接オブジェクトに設定されます。一度設定されたプロパティは、以降 `$source` 配列を見ずに直接アクセスされます。
*   JSONから読み込んだ生のデータは、`getSource()` メソッドで取得できます。

### `WPDumpSupport\Object` のサブクラス

`WPDumpSupport\Object\WPPost`, `WPDumpSupport\Object\WPPage`, `WPDumpSupport\Object\WPUser` など、WordPressの各データタイプに対応する具象クラスです。
これらはすべて `WPDumpSupport\Object\WPObject` を継承しており、データタイプ固有の処理を実装します。例えば `WPPost` クラスは `resolveReferences` メソッドをオーバーライドし、`author` (ユーザー)、`featured_media` (メディア)、`categories` (カテゴリ)、`tags` (タグ) といったプロパティに格納されているIDを、対応するオブジェクトに解決します。

### `WPDumpSupport\Resolver\ReferenceResolver`

リファレンス解決に関するすべてのロジックを担当する中心的なクラスです。
`WPDump` クラスは、この `ReferenceResolver` のインスタンスを内部で保持します。

**主なメソッド:**

*   `load()`: データ定義に基づき、`WPJsonLoader` を使ってJSONファイルを読み込み、キャッシュ戦略（`ICacheStrategy`）に従ってキャッシュを生成します。
*   `stream(string $postTypeKey)`: 指定されたデータタイプのオブジェクトをジェネレータとして返します。このとき、内部で `resolve()` メソッドを呼び出してリファレンスを解決します。
*   `count(string $postTypeKey)`: 指定されたデータタイプの件数を返します。
*   `resolve(WPObject $object)`: `WPObject` の `resolveReferences()` メソッドを呼び出します。
*   `resolveProperty(WPObject $self, string $prop, int $id, string $targetKey)`: `WPObject` のサブクラスから呼び出され、個々のプロパティの参照を解決します。

## 処理フロー

1.  **`WPDump` のインスタンス化**:
    利用者は、ダンプファイルが格納されているディレクトリを指定して `WPDump` クラスのインスタンスを作成します。`WPDump` のコンストラクタは、受け取ったパラメータを使って `ReferenceResolver` をインスタンス化します。

2.  **`$wpdump->load()` の呼び出し**:
    投稿やページのデータを読み込む前に、まず `load()` メソッドを呼び出します。
    `WPDump` は内部の `ReferenceResolver` の `load()` メソッドを呼び出します。`ReferenceResolver` はデータ定義に従って、各データタイプのキャッシュ（インメモリまたはファイル）を生成します。

3.  **`$wpdump->streamPosts()` などの利用**:
    `foreach` ループなどで `streamPosts()` や `streamPages()` を呼び出すことで、投稿やページのデータを一つずつジェネレータとして取得できます。
    `WPDump` は内部の `ReferenceResolver` の `stream()` メソッドを呼び出します。返されるオブジェクトは `ReferenceResolver` によってリファレンスが解決されており、例えば `$post->author` はユーザーIDではなく、`WPUser` オブジェクトになっています。

## キャッシュ戦略と巨大ファイルへの対応

### 課題

WordPressのエクスポートデータ、特に投稿、ページ、メディアといったデータは数万〜数十万件に及ぶ可能性があり、JSONファイルも数GBを超えることがあります。これらのデータをすべてメモリ上に展開して処理しようとすると、PHPのメモリ上限に達し、処理が失敗する原因となります。

### 解決策：設定可能なキャッシュ戦略

この問題を解決するため、データセットの特性に応じて利用者がキャッシュ戦略を選択できるアーキテクチャを採用しています。

#### `ICacheStrategy` インターフェース

キャッシュのロジックは `WPDumpSupport\Resolver\ICacheStrategy` インターフェイスによって抽象化されています。このインターフェースは、キャッシュへの保存 (`set`)、取得 (`get`)、リセット (`reset`)、そして件数を取得する (`count`) の4つのメソッドを定義します。

#### 標準のキャッシュ戦略

このライブラリは、2つの標準的なキャッシュ戦略を提供します。

1.  **`WPDumpSupport\Resolver\FileCacheStrategy` (`cache: 'file'`)**:
    *   **概要**: 各データオブジェクトを個別のファイルとしてキャッシュします。データ量が大きい、またはメモリを節約したい場合に最適です。
    *   **動作**: `ReferenceResolver::load()` が呼び出されると、JSONデータはストリーミングで1レコードずつ読み込まれ、PHPの `serialize()` を使ってキャッシュファイルとして保存されます。IDに基づいた階層化ディレクトリ構造（例: `$cacheDir/posts/00/00/01/23/123.cache`）に格納することで、ファイルシステムへの負荷を分散します。
    *   **ユースケース**: 投稿、ページ、メディアなど、レコード数が非常に多くなる可能性のあるデータタイプに適しています。

2.  **`WPDumpSupport\Resolver\MemoryCacheStrategy` (`cache: 'memory'`)**:
    *   **概要**: すべてのデータオブジェクトをメモリ上の配列に保持します。参照解決を高速に行いたい場合に最適です。
    *   **動作**: `ReferenceResolver::load()` が呼び出されると、JSONデータがすべて読み込まれ、`ReferenceResolver` 内部のプロパティにPHPオブジェクトとして格納されます。
    *   **ユースケース**: ユーザー、カテゴリ、タグなど、頻繁に参照されるがレコード数は比較的少ないデータタイプに適しています。

#### 戦略の選択

`ReferenceResolver` は、`WPDump` のコンストラクタから渡されたデータ定義の `'cache'` キーに `'file'` または `'memory'` が指定されているかを見て、データタイプごとに最適な戦略を選択します。

このアプローチにより、メモリ効率と処理速度のバランスを柔軟に調整しながら、巨大なWordPressダンプファイルを安定して扱うことが可能になります。

なお、キャッシュは `load()` のたびに完全に再生成・上書きされるため、最新のダンプデータに基づいた処理が保証されます。
よって、このライブラリは主にバッチ処理に適しており、リアルタイム性を要求されるWebアプリケーションには向いていません。

## テスト

このライブラリには、PHPUnitを用いた包括的なユニットテストが含まれています。テストは `tests` ディレクトリに配置されており、主要なクラスとメソッドの動作を検証しています。利用者は、開発環境で `composer install` を実行し、`./vendor/bin/phpunit` コマンドでテストを実行できます。

`$ ./vendor/bin/phpunit`
