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
                'title' => 'Real Madrid vs Bayern Munich: Drama Semifinal Liga Champions Berakhir Spektakuler',
                'description' => '<p>Pertarungan sengit antara dua raksasa Eropa, Real Madrid dan Bayern Munich, berakhir dengan dramatis di Santiago Bernabéu. Gol menit-menit akhir memastikan langkah sang juara bertahan ke babak final Liga Champions.</p><p>Pelatih memuji mental juara para pemain yang tidak pernah menyerah hingga peluit panjang berbunyi. Pertandingan ini menyuguhkan aksi jual beli serangan, penyelamatan kelas dunia dari kedua penjaga gawang, serta atmosfer stadion yang luar biasa dari puluhan ribu suporter yang hadir.</p>',
                'image' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Real Madrid vs Bayern Munich - Semifinal UCL',
                'meta_description' => 'Ringkasan laga sengit Real Madrid vs Bayern Munich di semifinal Liga Champions.',
                'meta_keywords' => 'Real Madrid, Bayern Munich, Champions League, UCL, Sepak Bola',
                'read_counts' => 5420,
                'is_top' => true,
                'is_published' => true,
                'published_at' => Carbon::now()->subHours(2),
            ],
            [
                'title' => 'Manchester City Kokoh di Puncak Klasemen Usai Kemenangan Telak',
                'description' => '<p>Manchester City kembali menunjukkan dominasinya di Premier League setelah meraih kemenangan meyakinkan 4-1 dalam laga derby penuh gengsi. Taktik penguasaan bola dan transisi cepat menjadi kunci keberhasilan skuad asuhan Pep Guardiola.</p><p>Dengan hasil ini, City menjaga jarak keunggulan di papan atas klasemen sementara liga Inggris dan semakin dekat menuju tangga juara musim ini.</p>',
                'image' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Manchester City Menang Telak di Premier League',
                'meta_description' => 'Hasil pertandingan Manchester City dan update klasemen Liga Inggris.',
                'meta_keywords' => 'Manchester City, Premier League, Liga Inggris, Sepak Bola',
                'read_counts' => 4180,
                'is_top' => true,
                'is_published' => true,
                'published_at' => Carbon::now()->subHours(4),
            ],
            [
                'title' => 'El Clasico Memanas: Barcelona dan Real Madrid Siap Bertarung Akhir Pekan Ini',
                'description' => '<p>Jelang laga akbar El Clasico di LaLiga Spanyol, kedua kubu telah merilis daftar skuad terbaik mereka. Persaingan ketat di puncak klasemen membuat pertandingan ini menjadi penentu gelar juara musim ini.</p><p>Para penggemar sepak bola di seluruh dunia bersiap menantikan pertarungan taktik antara dua klub terbesar di dunia, dengan siaran langsung berkualitas HD tersedia di platform NOBARLEK.</p>',
                'image' => 'https://images.unsplash.com/photo-1575361204480-aadea25e6e68?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Preview El Clasico: Barcelona vs Real Madrid',
                'meta_description' => 'Preview lengkap laga akbar El Clasico di LaLiga Spanyol.',
                'meta_keywords' => 'El Clasico, Barcelona, Real Madrid, LaLiga, Spanyol',
                'read_counts' => 6890,
                'is_top' => true,
                'is_published' => true,
                'published_at' => Carbon::now()->subHours(6),
            ],
            [
                'title' => 'Persib Bandung Amankan Tiga Poin Krusial di Menit Akhir Liga 1',
                'description' => '<p>Persib Bandung sukses mengamankan kemenangan dramatis 2-1 di hadapan ribuan bobotoh. Gol kemenangan yang tercipta lewat skema serangan balik cepat di menit ke-92 mengunci posisi Maung Bandung di papan atas Liga 1 Indonesia.</p><p>Pelatih kepala mengapresiasi kerja keras lini belakang dan disiplin taktik para pemain hingga menit akhir pertandingan.</p>',
                'image' => 'https://images.unsplash.com/photo-1517466787929-bc90951d0974?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Persib Bandung Menang Dramatis di Liga 1',
                'meta_description' => 'Gol menit akhir pastikan kemenangan Persib Bandung di kompetisi Liga 1.',
                'meta_keywords' => 'Persib Bandung, Liga 1, Indonesia, Bobotoh',
                'read_counts' => 3870,
                'is_top' => true,
                'is_published' => true,
                'published_at' => Carbon::now()->subHours(8),
            ],
            [
                'title' => 'Arsenal Tundukkan Rival London Lewat Penampilan Menawan di Emirates',
                'description' => '<p>The Gunners tampil perkasa saat menjamu rival sekota di Emirates Stadium. Kombinasi umpan satu-dua yang cepat serta pressing tinggi membuat lawan kesulitan mengembangkan permainan sejak menit awal babak pertama.</p><p>Kemenangan ini memberikan suntikan moral besar bagi skuad muda Arsenal dalam perburuan gelar kompetisi domestik dan Eropa.</p>',
                'image' => 'https://images.unsplash.com/photo-1560272564-c83b66b1ad12?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Arsenal Raih Kemenangan Gemilang di Kandang',
                'meta_description' => 'Arsenal tampil solid amankan tiga poin dalam derby London.',
                'meta_keywords' => 'Arsenal, Premier League, London Derby, Emirates',
                'read_counts' => 2950,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDay(),
            ],
            [
                'title' => 'Inter Milan Kunci Kemenangan Penting Menuju Scudetto Serie A',
                'description' => '<p>Inter Milan meraih poin penuh dalam lawatan ke San Siro dengan skor meyakinkan 2-0. Pertahanan solid dan efektivitas serangan balik menjadi kunci kemenangan armada Nerazzurri dalam mengamankan posisi puncak klasemen Serie A Italia.</p>',
                'image' => 'https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Inter Milan Mantapkan Posisi di Puncak Serie A',
                'meta_description' => 'Inter Milan raih kemenangan tandang berharga di Serie A.',
                'meta_keywords' => 'Inter Milan, Serie A, Liga Italia, Scudetto',
                'read_counts' => 2410,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDay()->subHours(3),
            ],
            [
                'title' => 'Borussia Dortmund Tampil Dominan di Signal Iduna Park',
                'description' => '<p>Gemuruh Yellow Wall kembali membakar semangat Borussia Dortmund saat melumat tamunya dengan skor 3-0 di Bundesliga. Penyerang andalan mereka menyumbangkan brace lewat penyelesaian akhir yang klinis.</p>',
                'image' => 'https://images.unsplash.com/photo-1516731415730-0c607149933a?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Borussia Dortmund Menang Meyakinkan di Bundesliga',
                'meta_description' => 'Dortmund raih 3 poin penuh di hadapan suporter Signal Iduna Park.',
                'meta_keywords' => 'Borussia Dortmund, Bundesliga, Jerman, Signal Iduna Park',
                'read_counts' => 1980,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDay()->subHours(7),
            ],
            [
                'title' => 'Timnas Indonesia Gelar Latihan Perdana Jelang Kualifikasi Piala Dunia',
                'description' => '<p>Skuad Garuda telah berkumpul di pemusatan latihan untuk mempersiapkan strategi jelang laga kualifikasi putaran ketiga. Pelatih fokus pada peningkatan fisik dan koordinasi antarlini demi meraih hasil maksimal di hadapan publik sendiri di Stadion Utama Gelora Bung Karno.</p>',
                'image' => 'https://images.unsplash.com/photo-1511886929837-354d827aae26?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Persiapan Timnas Indonesia Kualifikasi Piala Dunia',
                'meta_description' => 'Agenda latihan Timnas Indonesia jelang laga penting kualifikasi.',
                'meta_keywords' => 'Timnas Indonesia, Garuda, Kualifikasi Piala Dunia, SUGBK',
                'read_counts' => 7150,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Paris Saint-Germain Lolos ke Babak Selanjutnya Usai Duel Alot di Paris',
                'description' => '<p>Laga ketat di Parc des Princes berakhir dengan keunggulan tipis bagi PSG. Gol tunggal di babak kedua sudah cukup mengantar klub ibu kota Prancis tersebut melaju ke perempat final dengan agregat meyakinkan.</p>',
                'image' => 'https://images.unsplash.com/photo-1459865264687-595d652de67e?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'PSG Melaju ke Perempat Final Liga Champions',
                'meta_description' => 'PSG sukses amankan tiket babak gugur Liga Champions.',
                'meta_keywords' => 'PSG, Ligue 1, Liga Champions, Paris',
                'read_counts' => 2230,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(2)->subHours(5),
            ],
            [
                'title' => 'Sorotan Pekan Ini: Deretan Penyelamatan Terbaik dan Gol Spektakuler',
                'description' => '<p>Pekan pertandingan sepak bola internasional menyuguhkan aksi-aksi tak terlupakan dari para kiper top dan tendangan jarak jauh roket. Tonton video highlight lengkap serta analisis statistik hanya di NOBARLEK.</p>',
                'image' => 'https://images.unsplash.com/photo-1518091043644-c1d4457512c6?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Highlights Gol dan Penyelamatan Terbaik Pekan Ini',
                'meta_description' => 'Rangkuman momen-momen terbaik sepak bola dunia pekan ini.',
                'meta_keywords' => 'Highlights, Sepak Bola, Gol Terbaik, NOBARLEK',
                'read_counts' => 3100,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Persebaya Surabaya Raih Kemenangan Krusial di Stadion Gelora Bung Tomo',
                'description' => '<p>Diwarnai atmosfer luar biasa dari puluhan ribu Bonek, Persebaya Surabaya menundukkan tamunya dengan skor 2-0. Permainan tempo cepat dan agresif Bajol Ijo sukses memecah kebuntuan di babak kedua.</p>',
                'image' => 'https://images.unsplash.com/photo-1553778263-73a83bab9b0c?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Persebaya Surabaya Menang di GBT',
                'meta_description' => 'Persebaya petik poin penuh dalam lanjutan kompetisi Liga 1 di GBT.',
                'meta_keywords' => 'Persebaya, Bonek, Liga 1, Surabaya, GBT',
                'read_counts' => 3450,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(3)->subHours(4),
            ],
            [
                'title' => 'Panduan Menonton Siaran Langsung Sepak Bola Kualitas HD di NOBARLEK',
                'description' => '<p>Nikmati pengalaman streaming pertandingan favoritmu tanpa jeda iklan, multi-device di smartphone, tablet, laptop, dan Smart TV dengan server berkecepatan tinggi dan kualitas video jernih hingga Full HD.</p>',
                'image' => 'https://images.unsplash.com/photo-1486286701208-1d58e9338013?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Panduan Streaming Olahraga di NOBARLEK',
                'meta_description' => 'Cara menonton siaran live streaming sepak bola kualitas HD di NOBARLEK.',
                'meta_keywords' => 'NOBARLEK, Streaming, Panduan, Live Match, Sepak Bola',
                'read_counts' => 8400,
                'is_top' => false,
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(4),
            ],
        ];

        // Update existing news with working images if they had broken localhost images
        News::where('image', 'like', '%localhost%')->orWhereNull('image')->delete();

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
