<?php

namespace Database\Seeders;

use App\Models\Berita;
use App\Models\User;
use Illuminate\Database\Seeder;

class BeritaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        $beritaList = [
            [
                'judul' => 'Gerai Zakat UPZ Unsil Hadirkan Kemudahan Berzakat',
                'slug' => 'gerai-zakat-upz-unsil-hadirkan-kemudahan-berzakat',
                'kategori' => 'Kegiatan',
                'ringkasan' => 'UPZ Zakat Universitas Siliwangi membuka layanan Gerai Zakat sebagai sarana bagi sivitas akademika dan masyarakat untuk menunaikan zakat, infak, dan sedekah dengan lebih mudah.',
                'konten' => '<p>UPZ Zakat Universitas Siliwangi menghadirkan layanan Gerai Zakat sebagai salah satu upaya untuk memberikan kemudahan kepada sivitas akademika dan masyarakat dalam menunaikan zakat, infak, dan sedekah.</p><p>Gerai Zakat menjadi salah satu layanan yang dapat dimanfaatkan oleh masyarakat untuk memperoleh informasi mengenai zakat sekaligus menyalurkan dana zakat dengan lebih mudah. Kehadiran layanan ini diharapkan dapat meningkatkan kesadaran masyarakat untuk menunaikan kewajiban zakat serta memperkuat budaya berbagi di lingkungan Universitas Siliwangi.</p><p>Melalui Gerai Zakat, UPZ Zakat Universitas Siliwangi terus berkomitmen untuk memberikan pelayanan yang amanah, transparan, dan mudah dijangkau oleh masyarakat. Dana yang dihimpun selanjutnya akan dikelola dan disalurkan kepada penerima manfaat sesuai dengan ketentuan yang berlaku.</p>',
                'gambar' => '/storage/berita/berita-1.jpeg',
                'status' => 'published',
                'published_at' => now()->subDays(15),
                'author_id' => $admin?->id,
            ],
            [
                'judul' => 'UPZ Unsil Salurkan Bantuan Beras bagi Keluarga Membutuhkan',
                'slug' => 'upz-unsil-salurkan-bantuan-beras-bagi-keluarga-membutuhkan',
                'kategori' => 'Penyaluran',
                'ringkasan' => 'Sebagai bentuk kepedulian terhadap masyarakat, UPZ Zakat Universitas Siliwangi menyalurkan bantuan beras kepada sejumlah keluarga penerima manfaat untuk membantu memenuhi kebutuhan pangan sehari-hari.',
                'konten' => '<p>UPZ Zakat Universitas Siliwangi kembali melaksanakan penyaluran bantuan beras kepada sejumlah keluarga yang membutuhkan. Kegiatan ini merupakan bentuk kepedulian UPZ terhadap kondisi masyarakat, khususnya keluarga yang memiliki keterbatasan dalam memenuhi kebutuhan pangan sehari-hari.</p><p>Bantuan beras diberikan kepada penerima manfaat yang telah melalui proses pendataan dan verifikasi. Penyaluran dilakukan secara langsung agar bantuan dapat diterima oleh masyarakat yang benar-benar membutuhkan.</p><p>Program bantuan pangan ini diharapkan dapat membantu meringankan beban pengeluaran keluarga penerima manfaat. UPZ Zakat Universitas Siliwangi akan terus berupaya menghadirkan program penyaluran yang memberikan manfaat nyata dan tepat sasaran bagi masyarakat.</p>',
                'gambar' => '/storage/berita/berita-2.jpeg',
                'status' => 'published',
                'published_at' => now()->subDays(10),
                'author_id' => $admin?->id,
            ],
            [
                'judul' => 'Penyaluran Rutin UPZ Unsil Terus Berikan Manfaat',
                'slug' => 'penyaluran-rutin-upz-unsil-terus-berikan-manfaat',
                'kategori' => 'Penyaluran',
                'ringkasan' => 'UPZ Zakat Universitas Siliwangi secara rutin menyalurkan dana zakat kepada penerima manfaat yang telah terdata sebagai bagian dari komitmen untuk memastikan dana zakat tepat sasaran.',
                'konten' => '<p>UPZ Zakat Universitas Siliwangi secara rutin melaksanakan penyaluran dana zakat kepada masyarakat yang telah ditetapkan sebagai penerima manfaat. Program ini menjadi salah satu bentuk komitmen UPZ dalam memastikan dana zakat yang telah dihimpun dapat memberikan manfaat secara langsung.</p><p>Penyaluran dilakukan berdasarkan data penerima manfaat yang telah dikumpulkan dan diverifikasi oleh tim UPZ. Bantuan diberikan sesuai dengan kebutuhan dan kondisi masing-masing penerima sehingga diharapkan dapat memberikan dampak yang lebih tepat sasaran.</p>',
                'gambar' => '/storage/berita/berita-3.jpeg',
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'author_id' => $admin?->id,
            ],
            [
                'judul' => 'UPZ Unsil Salurkan Al-Qur’an untuk Masyarakat',
                'slug' => 'upz-unsil-salurkan-al-quran-untuk-masyarakat',
                'kategori' => 'Kegiatan',
                'ringkasan' => 'UPZ Zakat Universitas Siliwangi menyalurkan bantuan Al-Qur’an kepada masyarakat dan lembaga yang membutuhkan sebagai bentuk dukungan terhadap kegiatan keagamaan.',
                'konten' => '<p>UPZ Zakat Universitas Siliwangi menyalurkan bantuan Al-Qur’an kepada masyarakat dan lembaga yang membutuhkan. Program ini menjadi salah satu bentuk kepedulian UPZ dalam mendukung kegiatan keagamaan serta meningkatkan akses masyarakat terhadap Al-Qur’an.</p><p>Bantuan Al-Qur’an disalurkan kepada beberapa penerima yang membutuhkan, termasuk tempat-tempat yang digunakan untuk kegiatan pembelajaran dan pengajian masyarakat.</p>',
                'gambar' => '/storage/berita/berita-4.jpeg',
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'author_id' => $admin?->id,
            ],
        ];

        foreach ($beritaList as $item) {
            Berita::updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }
    }
}
