# lib-worker

Library untuk memenej job worker. Perlu juga memasang module `cli-app-worker` yang
akan mengerjakan semua job yang sudah terdaftar.

## Instalasi

Jalankan perintah di bawah di folder aplikasi:

```
mim app install lib-worker
```

## penggunaan

Module ini menambahkan satu library umum dengan nama `LibWorker\Library\Worker`
yang bisa digunakan untuk memenej jobs.

```php
use LibWorker\Library\Worker;

$name   = 'my-job';
$router = ['siteHome', ['param1'=>12], ['query1'=>12]];
$data   = ['name'=>'lorem'];
$time   = time() + 3600;

Worker::add($name, $router, $data, $time);
```

Semua aktifitas worker dijalankan melalui cli, untuk itu perlu menambahkan konten
seperti di bawah pada cronjob anda:

```cron
* * * * * cd /path/to/app/dir/ && php index.php app worker start
```

Atau jika cli mim terpasang, bisa juga dengan perintah:

```cron
* * * * * cd /path/to/app/dir/ && mim app worker start
```

## Method

### add(string $name, array $router, array $data, int $time): ?bool

Menambahkan satu worker dengan parameter sebagai berikut:

1. `$name` Nama worker.
1. `$router` Parameter untuk membuat router yang akan dipanggil.
1. `$data` Data yang akan dikirim ke router.
1. `$time` Waktu job akan dijalankan.

Sebagai catatan, jika job dengan nama yang sama sudah pernah ada, fungsi ini
akan mengembalikan nilai `false`.

### get(string $name): ?object

Mengambil informasi details suatu job. Fungsi ini akan mengembalikan objek dengan
bentuk seperti berikut:

```
stdClass Object
(
    [id] => 5
    [name] => my-job
    [router] => Array
        (
            [0] => siteHome
            [1] => stdClass Object
                (
                    [param1] => 12
                )

            [2] => stdClass Object
                (
                    [query1] => 12
                )

        )

    [data] => stdClass Object
        (
            [name] => lorem
        )

    [time] => 2019-02-19 13:58:18
    [created] => 2019-02-19 05:58:18
)
```

### exists(string $name): ?bool

Fungsi untuk mengecek jika suatu job sudah terdaftar.

### remove(string $name): ?bool

Menghapus job yang sudah terdaftar.