<?php

namespace Database\Seeders;

use App\Helpers\SnakeCaseHelper;
use App\Models\News;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Seed published news for the public API and frontend berita pages.
     */
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Persib Bandung amankan tiga poin di laga tandang',
                'description' => 'Persib Bandung menutup laga tandang dengan kemenangan tipis setelah gol penentu di injury time. Pelatih tim menegaskan fokus tetap pada konsistensi di sisa musim Liga 1.',
                'image' => 'https://images.unsplash.com/photo-1574629810360-7dfebbe27ef2?w=800&q=80',
                'meta_title' => 'Persib Bandung menang tandang',
                'meta_description' => 'Ringkasan laga dan reaksi pelatih setelah Persib Bandung meraih tiga poin.',
                'meta_keywords' => 'Persib, Liga 1, sepak bola Indonesia',
                'read_counts' => 1840,
                'is_top' => true,
                'is_published' => true,
                'published_at' => Carbon::now()->subHours(3),
            ],
            [
                'title' => 'Real Madrid vs Manchester City: preview laga besar Liga Champions',
                'description' => 'Dua raksasa Eropa bersiap bentrok di leg pertama. Statistik head-to-head, prediksi susunan pemain, dan opsi siaran langsung di NOBARLEK.',
                'image' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?w=800&q=80',
                'meta_title' => 'Preview Real Madrid vs Manchester City',
                'meta_description' => 'Preview lengkap laga Liga Champions Real Madrid vs Manchester City.',
                'meta_keywords' => 'Liga Champions, Real Madrid, Manchester City',
                'read_counts' => 3260,
                'is_top' => true,
                'is_published' => true,
                'published_at' => Carbon::now()->subHours(5),
            ],
            [
                'title' => 'Jadwal lengkap Serie B pekan ini',
                'description' => 'Semua pertandingan Serie B pekan ini dengan kickoff WIB, status siaran, dan link sorotan. Filter liga favoritmu di halaman jadwal.',
                'image' => 'https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?w=800&q=80',
                'meta_title' => 'Jadwal Serie B pekan ini',
                'meta_description' => 'Daftar pertandingan Serie B pekan ini beserta jam kickoff.',
                'meta_keywords' => 'Serie B, jadwal, Italia',
                'read_counts' => 920,
                'is_top' => true,
                'is_published' => true,
                'published_at' => Carbon::now()->subHours(8),
            ],
            [
                'title' => 'Timnas Indonesia siap jalani pemusatan latihan jelang kualifikasi',
                'description' => 'Skuad Garuda mulai berkumpul untuk pemusatan latihan. Beberapa pemain naturalisasi dipanggil pelatih untuk memperkuat lini tengah dan serangan.',
                'image' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&q=80',
                'meta_title' => 'Pemusatan latihan Timnas Indonesia',
                'meta_description' => 'Update skuad dan agenda latihan Timnas Indonesia.',
                'meta_keywords' => 'Timnas Indonesia, kualifikasi, PSSI',
                'read_counts' => 2410,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDay(),
            ],
            [
                'title' => 'Premier League: hasil pekan 3 dan klasemen sementara',
                'description' => 'Ringkasan hasil seluruh laga Premier League pekan 3. Perubahan posisi di papan klasemen dan daftar pencetak gol sementara.',
                'image' => 'https://images.unsplash.com/photo-1560272564-c83b66b1ad12?w=800&q=80',
                'meta_title' => 'Hasil Premier League pekan 3',
                'meta_description' => 'Hasil dan klasemen Premier League pekan 3.',
                'meta_keywords' => 'Premier League, Inggris, klasemen',
                'read_counts' => 1580,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDay()->subHours(4),
            ],
            [
                'title' => 'LaLiga: El Clasico mendekat, tiket dan siaran mulai ramai diburu',
                'description' => 'Antusiasme El Clasico meningkat jelang kickoff. NOBARLEK merangkum opsi menonton legal dan statistik pertemuan terakhir kedua tim.',
                'image' => 'https://images.unsplash.com/photo-1489944440615-453fc1155552?w=800&q=80',
                'meta_title' => 'El Clasico mendekat',
                'meta_description' => 'Informasi siaran dan preview El Clasico.',
                'meta_keywords' => 'LaLiga, El Clasico, Barcelona, Real Madrid',
                'read_counts' => 2750,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Bundesliga: pemain muda Borussia Dortmund cetak hat-trick',
                'description' => 'Penampilan gemilang pemain muda Dortmund jadi sorotan usai hat-trick di kandang. Analisis singkat peran taktik pelatih dalam laga tersebut.',
                'image' => 'https://images.unsplash.com/photo-1517466787929-bc90951d0974?w=800&q=80',
                'meta_title' => 'Hat-trick di Bundesliga',
                'meta_description' => 'Sorotan pemain Borussia Dortmund usai hat-trick.',
                'meta_keywords' => 'Bundesliga, Dortmund, Jerman',
                'read_counts' => 740,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(2)->subHours(6),
            ],
            [
                'title' => 'Arema FC perkuat lini belakang dengan bek asing berpengalaman',
                'description' => 'Arema FC resmi mengumumkan kedatangan bek asing yang pernah tampil di liga Asia Tenggara. Kontrak diumumkan untuk satu musim dengan opsi perpanjangan.',
                'image' => 'https://images.unsplash.com/photo-1551958219-ac0fb0a4c2a8?w=800&q=80',
                'meta_title' => 'Arema FC datangkan bek asing',
                'meta_description' => 'Arema FC umumkan rekrutmen bek asing baru.',
                'meta_keywords' => 'Arema FC, Liga 1, transfer',
                'read_counts' => 610,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Ligue 1: PSG unggul lima poin di puncak klasemen',
                'description' => 'Kemenangan akhir pekan membawa PSG semakin dekat dengan gelar. Statistik xG dan dominasi bola jadi kunci tiga poin kali ini.',
                'image' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?w=800&q=80',
                'meta_title' => 'PSG puncaki Ligue 1',
                'meta_description' => 'Update klasemen Ligue 1 dan performa PSG.',
                'meta_keywords' => 'Ligue 1, PSG, Prancis',
                'read_counts' => 880,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(3)->subHours(5),
            ],
            [
                'title' => 'Formula 1: jadwal sprint race akhir pekan ini',
                'description' => 'Jadwal lengkap sesi latihan, kualifikasi sprint, dan balapan utama GP akhir pekan. Waktu tayang disesuaikan zona WIB.',
                'image' => 'https://images.unsplash.com/photo-1541443138872-47fedd0c2a1a?w=800&q=80',
                'meta_title' => 'Jadwal F1 akhir pekan ini',
                'meta_description' => 'Jadwal sesi Formula 1 akhir pekan ini.',
                'meta_keywords' => 'Formula 1, motorsport, jadwal',
                'read_counts' => 1120,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(4),
            ],
            [
                'title' => 'MotoGP: rider favorit pole position di sirkuit panas',
                'description' => 'Kualifikasi MotoGP menghadirkan persaingan ketat di sector terakhir. Pole position diraih dengan selisih sepersekian detik.',
                'image' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=800&q=80',
                'meta_title' => 'Pole position MotoGP',
                'meta_description' => 'Hasil kualifikasi MotoGP terbaru.',
                'meta_keywords' => 'MotoGP, motorsport, kualifikasi',
                'read_counts' => 530,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(4)->subHours(8),
            ],
            [
                'title' => 'Cara menonton siaran olahraga legal di NOBARLEK',
                'description' => 'Panduan singkat memilih pertandingan live, mengaktifkan pengingat jadwal, dan memahami kapan siaran tersedia di platform.',
                'image' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8121?w=800&q=80',
                'meta_title' => 'Panduan menonton di NOBARLEK',
                'meta_description' => 'Panduan menonton siaran olahraga di NOBARLEK.',
                'meta_keywords' => 'NOBARLEK, streaming, panduan',
                'read_counts' => 4200,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(5),
            ],
        ];

        foreach ($articles as $article) {
            $slug = SnakeCaseHelper::toSnakeCase($article['title']);
            $keywords = array_map('trim', explode(',', $article['meta_keywords']));

            News::query()->updateOrCreate(
                ['slug' => $slug],
                array_merge($article, [
                    'slug' => $slug,
                    'meta_keywords' => $keywords,
                ]),
            );
        }
    }
}
