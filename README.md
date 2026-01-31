# lib-worker

Library yang bertugas menjalankan aksi-aksi aplikasi di background.

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
$queue  = 'job-1';

Worker::add($name, $router, $data, $time, $queue);
```

Semua aktifitas worker dijalankan melalui cli, untuk itu perlu menambahkan konten
seperti di bawah pada cronjob anda:

```cron
* * * * * cd /path/to/app/dir/ && php index.php worker start
```

Atau jika cli mim terpasang, bisa juga dengan perintah:

```cron
* * * * * cd /path/to/app/dir/ && mim app worker start
```

## Method

### addMany(array $data): bool

Menambah beberapa worker sekaligus. Fungsi ini akan melewati data yang sudah
ada di databse. Masing-masing array item harus memiliki properti yang sama persis
dengan data pada method `add`:

```php
$data = [
    [
        'name' => 'x',
        'router' => ['x', []],
        'data' => [],
        'time' => [],
        'queue' => 'x'
    ],
    ...
];
```

### add(string $name, array $router, array $data, int $time, ?string $queue = null): ?bool

Menambahkan satu worker dengan parameter sebagai berikut:

1. `$name` Nama worker.
1. `$router` Parameter untuk membuat router yang akan dipanggil.
1. `$data` Data yang akan dikirim ke router.
1. `$time` Waktu job akan dijalankan.
1. `$queue` Nama antrian

Sebagai catatan, jika job dengan nama yang sama sudah pernah ada, fungsi ini
akan mengembalikan nilai `false`.

Tidak ada job yang dijalankan secara bersamaan dengan nilai `queue` yang sama
kecuali nilai tersebut adalah null.

### get(string $name): ?object

Mengambil informasi details suatu job. Fungsi ini akan mengembalikan objek dengan
bentuk seperti berikut:

```
stdClass Object
(
    [id] => 5
    [name] => my-job
    [queue] => my-queue
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

## Konfigurasi

Di bawah ini adalah konfigurasi umum worker.

```php
return [
    // ...
    'libWorker' => [
        'concurency' => 10,
        'pidFile' => 'etc',
        'phpBinary' => 'php',
        'keepResponse' => true
    ]
    // ...
];
```

1. `concurency` Total worker yang akan dijalankan untuk mengerjakan job.
1. `pidFile` Folder penyimpanan pid file.
1. `phpBinary` Path ke php-cli jika php tidak tersedia di PATH OS.
1. `keepResponse` Menyimpan semua execution log pada table `worker_result`.

Jika melakukan perubahan pada aplikasi setelah worker berjalan, maka pastikan
untuk merestart worker agar konfig tersebut digunakan.

## Handler Response

Masing-masing router yang akan digunakan untuk menangani worker, harus
mengembalikan data dengan bentuk JSON dalam bentuk seperti di bawah:

```json
{
    "error": false,
    "delay": 60
}
```

Worker akan dipending dan dijalankan ulang jika router pekerja:

1. Mengembalikan *empty string*
1. Mengembalikan data yang tidak bisa di `json_decode`.
1. Nilai `error` adalah `true`.

Jika nilai yang dikembalikan oleh pekerja adalah `error: true`, maka job tersebut
akan dijalankan lagi 1 menit kemudian. Pekerja bisa menentukan delay job akan
dijalankan lagi dengan mengeset properti `delay` dalam satuan detik.
