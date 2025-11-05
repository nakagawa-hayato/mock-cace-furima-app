# mock-cace-furima-app
# フリマアプリ

## 環境構築

### Docker ビルド

1. git clone git@github.com:nakagawa-hayato/mock-cace-furima-app.git
2. docker-compose up -d build

### ＊ MySQL は、OS によって起動しない場合があるのでそれぞれの PC に合わせて docker-compose.yml ファイルを編集してください。

### Laravel 環境構築

1. docker-compose exec php bash
2. composer install
3. .env.example ファイルから.env を作成し、環境変数を変更
4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed

### mailhogについて
## コンテナ設定

- docker-compose.ymlに以下記述

- mailhog:
-   image: mailhog/mailhog
-   platform: linux/amd64
-   ports:
-   -"1025:1025" # SMTP (Laravel から送信)
-   -"8025:8025" # Web UI (ブラウザで確認)

- .envに記述

- MAIL_MAILER=smtp
- MAIL_HOST=mailhog
- MAIL_PORT=1025
- MAIL_USERNAME=null
- MAIL_PASSWORD=null
- MAIL_ENCRYPTION=null
- MAIL_FROM_ADDRESS=example@example.com
- MAIL_FROM_NAME="${APP_NAME}"

## 確認方法

- Laravel からメール送信後、ブラウザで以下にアクセス：
- http://localhost:8025
- ここで送信メールの一覧・本文を確認できます（実際のメールは外部送信されません）。


### Stripeについて
- コンビニ支払いとカード支払いのオプションがありますが、決済画面にてコンビニ支払いを選択しますと、レシートを印刷する画面に遷移します。そのため、カード支払いを成功させた場合に意図する画面遷移が行える想定です。

- また、StripeのAPIキーは以下のように設定をお願いいたします。

- STRIPE_PUBLIC_KEY="パブリックキー"
- STRIPE_SECRET_KEY="シークレットキー"
- 以下のリンクは公式ドキュメントです。
- https://docs.stripe.com/payments/checkout?locale=ja-JP


## 使用技術

- PHP 8.2.28
- Laravel 8.83.29
- MySQL 8.0.26
- Mailhog 1.0.1

## テーブル仕様

<img width="2257" height="1848" alt="Image" src="https://github.com/user-attachments/assets/c5a87f8b-ce72-4c26-8458-08bf21d6f80d" />

## ER 図

<img width="1281" height="1160" alt="Image" src="https://github.com/user-attachments/assets/af6eec93-63f1-4dde-8dc7-655bf507dc03" />

## URL

- 開発環境：http:/localhost/
- phpMyAdmin : http://localhost:8080/

## PHPUnitを利用したテストに関して
以下のコマンド:
```
//テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
//パスワードはrootと入力
create database test_database;

docker-compose exec php bash
php artisan migrate:fresh --env=testing
./vendor/bin/phpunit
```
※.env.testingにもStripeのAPIキーを設定してください。
