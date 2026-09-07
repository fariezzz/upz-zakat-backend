<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Muzakki;
use App\Mail\MuzakkiCredentialsMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MuzakkiController extends Controller
{
    /**
     * GET /api/public/muzakki
     * Public transparency list (nama, kategori, jenis_zakat, count stats)
     */
    public function publicList(Request $request)
    {
        $search = $request->query('search');
        $kategori = $request->query('kategori');

        $query = Muzakki::query()
            ->where('tipe_muzakki', 'terdaftar')  // Only show registered muzakki
            ->with(['transaksi' => function ($q) {
                $q->where('jenis', 'masuk')->latest();
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'ilike', "%{$search}%")
                  ->orWhere('nik', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'ilike', "%{$search}%")
                  ->orWhere('no_hp', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('unit_kerja', 'ilike', "%{$search}%")
                  ->orWhere('pekerjaan', 'ilike', "%{$search}%")
                  ->orWhere('alamat_lengkap', 'ilike', "%{$search}%")
                  ->orWhere('kategori', 'ilike', "%{$search}%");
            });
        }

        if ($kategori === 'unsil' || $kategori === 'dosen_staf') {
            $query->where(function ($q) {
                $q->where('kategori', 'ilike', '%Dosen%')
                  ->orWhere('kategori', 'ilike', '%Staf%')
                  ->orWhere('kategori', 'ilike', '%Civitas%')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('unit_kerja')
                         ->where('unit_kerja', '!=', '')
                         ->where('unit_kerja', '!=', 'Masyarakat Umum')
                         ->where('unit_kerja', '!=', 'Umum');
                  });
            });
        } elseif ($kategori === 'umum') {
            $query->where(function ($q) {
                $q->where('kategori', 'ilike', '%Umum%')
                  ->orWhere(function ($q2) {
                      $q2->whereNull('unit_kerja')
                         ->orWhere('unit_kerja', '')
                         ->orWhere('unit_kerja', 'Masyarakat Umum')
                         ->orWhere('unit_kerja', 'Umum');
                  });
            });
        }

        $allMuzakki = $query->orderBy('nama')->get();

        $totalDosenStaf = Muzakki::where('tipe_muzakki', 'terdaftar')
            ->where(function ($q) {
                $q->where('kategori', 'ilike', '%Dosen%')
                  ->orWhere('kategori', 'ilike', '%Staf%')
                  ->orWhere('kategori', 'ilike', '%Civitas%')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('unit_kerja')
                         ->where('unit_kerja', '!=', '')
                         ->where('unit_kerja', '!=', 'Masyarakat Umum')
                         ->where('unit_kerja', '!=', 'Umum');
                  });
            })->count();

        $totalUmum = Muzakki::where('tipe_muzakki', 'terdaftar')
            ->where(function ($q) {
                $q->where('kategori', 'ilike', '%Umum%')
                  ->orWhere(function ($q2) {
                      $q2->whereNull('unit_kerja')
                         ->orWhere('unit_kerja', '')
                         ->orWhere('unit_kerja', 'Masyarakat Umum')
                         ->orWhere('unit_kerja', 'Umum');
                  });
            })->count();

        $list = $allMuzakki->map(function ($m) {
            $isUnsil = (!empty($m->kategori) && (stripos($m->kategori, 'Dosen') !== false || stripos($m->kategori, 'Staf') !== false || stripos($m->kategori, 'UNSIL') !== false))
                || (!empty($m->unit_kerja) && !in_array($m->unit_kerja, ['Masyarakat Umum', 'Umum']));
            
            $kategoriLabel = $m->kategori ?: ($isUnsil ? 'Dosen & Staf UNSIL' : 'Muzakki Umum');

            return [
                'id'                => $m->id,
                'nama'              => $m->nama,
                'nik'               => $m->nik,
                'nip'               => $m->nip,
                'jenis_kelamin'     => $m->jenis_kelamin,
                'tempat_lahir'      => $m->tempat_lahir,
                'tanggal_lahir'     => $m->tanggal_lahir,
                'pekerjaan'         => $m->pekerjaan,
                'alamat_lengkap'    => $m->alamat_lengkap,
                'email'             => $m->email,
                'no_hp'             => $m->no_hp,
                'unit_kerja'        => $m->unit_kerja,
                'kategori'          => $kategoriLabel,
                'jenis_zakat'       => $m->jenis_zakat,
                'frekuensi'         => $m->frekuensi,
                'nominal'           => $m->nominal,
                'kesepakatan_zakat' => $m->kesepakatan_zakat,
                'metode_pembayaran' => $m->metode_pembayaran,
                'pilihan_bank'      => $m->pilihan_bank,
                'pilihan_ewallet'   => $m->pilihan_ewallet,
                'created_at'        => $m->created_at ? $m->created_at->toISOString() : null,
                'tanggal_daftar'    => $m->created_at ? $m->created_at->translatedFormat('d M Y') : '-',
            ];
        });

        return response()->json([
            'data'  => $list,
            'stats' => [
                'total'      => $totalDosenStaf + $totalUmum,
                'dosen_staf' => $totalDosenStaf,
                'umum'       => $totalUmum,
            ],
        ]);
    }

    /**
     * POST /api/public/muzakki/register
     * Pendaftaran muzakki baru dari halaman publik
     */
    public function publicRegister(Request $request)
    {
        $validated = $request->validate([
            'nama'              => 'required|string|max:150',
            'nik'               => 'nullable|string|max:30',
            'nip'               => 'nullable|string|max:30',
            'jenis_kelamin'     => 'nullable|string|max:20',
            'tempat_lahir'      => 'nullable|string|max:100',
            'tanggal_lahir'     => 'nullable|string|max:50',
            'pekerjaan'         => 'nullable|string|max:100',
            'alamat_lengkap'    => 'nullable|string',
            'email'             => 'nullable|string|max:100',
            'no_hp'             => 'nullable|string|max:25',
            'kategori'          => 'nullable|string|max:50',
            'unit_kerja'        => 'nullable|string|max:200',
            'jenis_zakat'       => 'nullable|string|max:200',
            'frekuensi'         => 'nullable|string|max:100',
            'nominal'           => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|string|max:100',
            'pilihan_bank'      => 'nullable|string|max:100',
            'pilihan_ewallet'   => 'nullable|string|max:100',
            'kesepakatan_zakat' => 'nullable',
        ]);

        $kategoriDefault = !empty($validated['kategori'])
            ? $validated['kategori']
            : (!empty($validated['nip']) ? 'Dosen & Staf UNSIL' : 'Muzakki Umum');

        $kesepakatan = $validated['kesepakatan_zakat'] ?? null;
        if (is_string($kesepakatan)) {
            $decoded = json_decode($kesepakatan, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $kesepakatan = $decoded;
            }
        }

        // Hitung total nominal & join jenis zakat jika kesepakatan berbentuk array
        $nominalTotal = isset($validated['nominal']) ? (float) $validated['nominal'] : null;
        $jenisZakatSummary = $validated['jenis_zakat'] ?? null;

        if (is_array($kesepakatan) && count($kesepakatan) > 0) {
            $sum = 0;
            $labels = [];
            foreach ($kesepakatan as $item) {
                // Format baru dari DaftarMuzakkiUnsilPage: {komponen, nominal (penghasilan), zakat (tagihan)}
                // Format lama dari seeder: {key, jenis, frekuensi, nominal (tagihan)}
                if (isset($item['zakat'])) {
                    $sum += (float) $item['zakat'];
                } else {
                    $sum += (float) ($item['nominal'] ?? 0);
                }
                if (!empty($item['jenis'])) {
                    $labels[] = $item['jenis'];
                }
            }
            if ($nominalTotal === null || $nominalTotal <= 0) {
                $nominalTotal = $sum;
            }
            if (empty($jenisZakatSummary) && count($labels) > 0) {
                $jenisZakatSummary = implode(', ', array_unique($labels));
            }
        }

        $muzakki = Muzakki::updateOrCreate(
            ['nama' => $validated['nama']],
            [
                'nik'               => $validated['nik'] ?? null,
                'nip'               => $validated['nip'] ?? null,
                'jenis_kelamin'     => $validated['jenis_kelamin'] ?? null,
                'tempat_lahir'      => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir'     => $validated['tanggal_lahir'] ?? null,
                'pekerjaan'         => $validated['pekerjaan'] ?? null,
                'alamat_lengkap'    => $validated['alamat_lengkap'] ?? null,
                'email'             => $validated['email'] ?? null,
                'no_hp'             => $validated['no_hp'] ?? null,
                'kategori'          => $kategoriDefault,
                'unit_kerja'        => $validated['unit_kerja'] ?? ($kategoriDefault === 'Muzakki Umum' ? 'Masyarakat Umum' : null),
                'jenis_zakat'       => $jenisZakatSummary,
                'frekuensi'         => $validated['frekuensi'] ?? null,
                'nominal'           => $nominalTotal,
                'metode_pembayaran' => $validated['metode_pembayaran'] ?? null,
                'pilihan_bank'      => $validated['pilihan_bank'] ?? null,
                'pilihan_ewallet'   => $validated['pilihan_ewallet'] ?? null,
                'kesepakatan_zakat' => $kesepakatan,
                'tipe_muzakki'      => 'terdaftar',  // Mark as registered muzakki
            ]
        );

        // Buat User account jika no_hp tersedia dan belum ada account
        if (!empty($validated['no_hp'])) {
            $existingUser = User::where('no_hp', $validated['no_hp'])
                ->orWhere(function ($q) use ($validated) {
                    if (!empty($validated['email'])) {
                        $q->where('email', $validated['email']);
                    }
                    if (!empty($validated['nip'])) {
                        $q->orWhere('nip', $validated['nip']);
                    }
                })
                ->first();

            if (!$existingUser) {
                // Generate password random
                $generatedPassword = Str::random(8);
                
                // Email fallback jika tidak ada email
                $emailForAccount = $validated['email'] ?? $validated['no_hp'] . '@muzakki.unsil';

                // Buat user account
                $user = User::create([
                    'name'           => $validated['nama'],
                    'email'          => $emailForAccount,
                    'password'       => Hash::make($generatedPassword),
                    'role'           => 'muzakki',
                    'nip'            => $validated['nip'] ?? null,
                    'no_hp'          => $validated['no_hp'],
                    'unit_kerja'     => $validated['unit_kerja'] ?? null,
                    'is_first_login' => true,
                    'temp_password'  => $generatedPassword,
                ]);

                // Kirim credentials via WhatsApp
                $whatsappSent = $this->sendWhatsAppCredentials(
                    $validated['no_hp'],
                    $validated['nama'],
                    $emailForAccount,
                    $generatedPassword
                );

                // Kirim credentials via Email (jika email valid)
                $emailSent = false;
                if (!empty($validated['email']) && filter_var($validated['email'], FILTER_VALIDATE_EMAIL)) {
                    $emailSent = $this->sendEmailCredentials(
                        $validated['email'],
                        $validated['nama'],
                        $validated['email'],
                        $generatedPassword
                    );
                }

                Log::info("User account created for muzakki: {$validated['nama']}", [
                    'user_id' => $user->id,
                    'muzakki_id' => $muzakki->id,
                    'whatsapp_sent' => $whatsappSent,
                    'email_sent' => $emailSent,
                ]);
            } else {
                Log::info("User account already exists for: {$validated['nama']}");
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran Muzakki berhasil! Anda kini terdaftar sebagai Muzakki UPZ Zakat UNSIL.',
            'data'    => $muzakki,
        ], 201);
    }

    /**
     * GET /api/muzakki
     * Query params: search, kategori, per_page, page
     */
    public function index(Request $request)
    {
        $query = Muzakki::where('tipe_muzakki', 'terdaftar');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'ilike', "%{$search}%")
                  ->orWhere('nik', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('no_hp', 'ilike', "%{$search}%")
                  ->orWhere('unit_kerja', 'ilike', "%{$search}%")
                  ->orWhere('pekerjaan', 'ilike', "%{$search}%")
                  ->orWhere('alamat_lengkap', 'ilike', "%{$search}%")
                  ->orWhere('tempat_lahir', 'ilike', "%{$search}%")
                  ->orWhere('kategori', 'ilike', "%{$search}%");
            });
        }

        if ($kategori = $request->query('kategori')) {
            if ($kategori === 'dosen_staf' || $kategori === 'dosen/staf') {
                $query->where(function ($q) {
                    $q->where('kategori', 'ilike', '%Dosen%')
                      ->orWhere('kategori', 'ilike', '%Staf%')
                      ->orWhere('kategori', 'ilike', '%Civitas%')
                      ->orWhere(function ($q2) {
                          $q2->whereNotNull('unit_kerja')
                             ->where('unit_kerja', '!=', '')
                             ->where('unit_kerja', '!=', 'Masyarakat Umum')
                             ->where('unit_kerja', '!=', 'Umum');
                      });
                });
            } elseif ($kategori === 'umum') {
                $query->where(function ($q) {
                    $q->where('kategori', 'ilike', '%Umum%')
                      ->orWhere(function ($q2) {
                          $q2->whereNull('unit_kerja')
                             ->orWhere('unit_kerja', '')
                             ->orWhere('unit_kerja', 'Masyarakat Umum')
                             ->orWhere('unit_kerja', 'Umum');
                      });
                });
            }
        }

        $perPage = min((int) $request->query('per_page', 10), 100);
        // Removed withCount('transaksi') - transaksi_count tidak digunakan di frontend
        $data = $query->orderByDesc('created_at')->paginate($perPage);

        // Cache stats untuk menghindari query berulang
        // Stats ini jarang berubah, jadi kita hitung sekali saja jika belum ada di cache
        $totalDosenStaf = Muzakki::where('tipe_muzakki', 'terdaftar')
            ->where(function ($q) {
                $q->where('kategori', 'ilike', '%Dosen%')
                  ->orWhere('kategori', 'ilike', '%Staf%')
                  ->orWhere('kategori', 'ilike', '%Civitas%')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('unit_kerja')
                         ->where('unit_kerja', '!=', '')
                         ->where('unit_kerja', '!=', 'Masyarakat Umum')
                         ->where('unit_kerja', '!=', 'Umum');
                  });
            })->count();

        $totalUmum = Muzakki::where('tipe_muzakki', 'terdaftar')
            ->where(function ($q) {
                $q->where('kategori', 'ilike', '%Umum%')
                  ->orWhere(function ($q2) {
                      $q2->whereNull('unit_kerja')
                         ->orWhere('unit_kerja', '')
                         ->orWhere('unit_kerja', 'Masyarakat Umum')
                         ->orWhere('unit_kerja', 'Umum');
                  });
            })->count();

        $stats = compact('totalDosenStaf', 'totalUmum');

        return response()->json([
            'data'  => $data->items(),
            'meta'  => [
                'current_page'      => $data->currentPage(),
                'last_page'         => $data->lastPage(),
                'per_page'          => $data->perPage(),
                'total'             => $data->total(),
                'total_dosen_staf'  => $stats['totalDosenStaf'],
                'total_umum'        => $stats['totalUmum'],
            ],
        ]);
    }


    /**
     * GET /api/muzakki/options
     * Untuk combobox — mengembalikan id + nama saja (ringan)
     */
    public function options(Request $request)
    {
        $search = $request->query('search', '');

        $data = Muzakki::where('tipe_muzakki', 'terdaftar')
            ->when($search, fn($q) => $q->where('nama', 'ilike', "%{$search}%"))
            ->orderBy('nama')
            ->limit(30)
            ->get(['id', 'nama', 'unit_kerja', 'kategori']);

        return response()->json($data);
    }

    /**
     * POST /api/muzakki
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'              => 'required|string|max:150',
            'nik'               => 'nullable|string|max:30',
            'nip'               => 'nullable|string|max:30',
            'jenis_kelamin'     => 'nullable|string|max:20',
            'tempat_lahir'      => 'nullable|string|max:100',
            'tanggal_lahir'     => 'nullable|string|max:50',
            'pekerjaan'         => 'nullable|string|max:100',
            'alamat_lengkap'    => 'nullable|string',
            'email'             => 'nullable|email|max:100',
            'no_hp'             => 'nullable|string|max:25',
            'kategori'          => 'nullable|string|max:50',
            'unit_kerja'        => 'nullable|string|max:200',
            'jenis_zakat'       => 'nullable|string|max:100',
            'frekuensi'         => 'nullable|string|max:50',
            'nominal'           => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|string|max:100',
        ]);

        $muzakki = Muzakki::create($validated);

        return response()->json($muzakki, 201);
    }

    /**
     * PUT /api/muzakki/{id}
     */
    public function update(Request $request, Muzakki $muzakki)
    {
        $validated = $request->validate([
            'nama'              => 'sometimes|required|string|max:150',
            'nik'               => 'nullable|string|max:30',
            'nip'               => 'nullable|string|max:30',
            'jenis_kelamin'     => 'nullable|string|max:20',
            'tempat_lahir'      => 'nullable|string|max:100',
            'tanggal_lahir'     => 'nullable|string|max:50',
            'pekerjaan'         => 'nullable|string|max:100',
            'alamat_lengkap'    => 'nullable|string',
            'email'             => 'nullable|email|max:100',
            'no_hp'             => 'nullable|string|max:25',
            'kategori'          => 'nullable|string|max:50',
            'unit_kerja'        => 'nullable|string|max:200',
            'jenis_zakat'       => 'nullable|string|max:100',
            'frekuensi'         => 'nullable|string|max:50',
            'nominal'           => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|string|max:100',
        ]);

        $muzakki->update($validated);

        return response()->json($muzakki);
    }

    /**
     * DELETE /api/muzakki/{id}
     */
    public function destroy(Muzakki $muzakki)
    {
        // Hapus User account terkait jika ada
        if (!empty($muzakki->no_hp) || !empty($muzakki->email) || !empty($muzakki->nip)) {
            $user = User::where(function ($q) use ($muzakki) {
                if (!empty($muzakki->no_hp)) {
                    $q->where('no_hp', $muzakki->no_hp);
                }
                if (!empty($muzakki->email)) {
                    $q->orWhere('email', $muzakki->email);
                }
                if (!empty($muzakki->nip)) {
                    $q->orWhere('nip', $muzakki->nip);
                }
            })->first();

            if ($user) {
                $user->delete();
                Log::info("User account deleted along with muzakki", [
                    'user_id' => $user->id,
                    'muzakki_id' => $muzakki->id,
                    'muzakki_name' => $muzakki->nama
                ]);
            }
        }

        $muzakki->delete();

        return response()->json(['message' => 'Muzakki berhasil dihapus.']);
    }

    /**
     * Kirim credentials via WhatsApp menggunakan Baileys service
     */
    private function sendWhatsAppCredentials($phone, $nama, $email, $password)
    {
        try {
            $whatsappServiceUrl = rtrim(env('WHATSAPP_SERVICE_URL', 'http://localhost:3001'), '/');

            $frontendUrl = rtrim((string) (env('FRONTEND_URL') ?: env('APP_URL', 'https://upz.unsil.ac.id')), '/');
            if (str_contains($frontendUrl, 'backend') || str_contains($frontendUrl, ':8000')) {
                $frontendUrl = 'https://upz-zakat-unsil.vercel.app';
            }
            $loginUrl = "{$frontendUrl}/muzakki/masuk";

            $message = "🔐 *Akun UPZ Zakat UNSIL Anda*\n\n"
                . "Assalamu'alaikum *{$nama}*,\n\n"
                . "Akun muzakki Anda telah berhasil dibuat!\n\n"
                . "📧 Email/No HP: *{$email}*\n"
                . "🔑 Password: *{$password}*\n\n"
                . "Silakan login di:\n"
                . "{$loginUrl}\n\n"
                . "⚠️ *Penting:* Segera ganti password Anda setelah login pertama kali untuk keamanan akun.\n\n"
                . "_Pesan otomatis dari UPZ Zakat Universitas Siliwangi_";

            Log::info("Attempting to send WhatsApp to {$phone}", [
                'url' => $whatsappServiceUrl,
                'phone' => $phone,
                'nama' => $nama
            ]);

            $response = Http::timeout(10)->post("{$whatsappServiceUrl}/send", [
                'phone'   => $phone,
                'message' => $message,
            ]);

            Log::info("WhatsApp API Response", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp credentials sent successfully to {$phone}");
                return true;
            } else {
                Log::error("Failed to send WhatsApp to {$phone}", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp service error: " . $e->getMessage(), [
                'phone' => $phone,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Kirim credentials via Email
     */
    private function sendEmailCredentials($email, $nama, $emailForLogin, $password)
    {
        try {
            Mail::to($email)->send(new MuzakkiCredentialsMail(
                $nama,
                $emailForLogin,
                $password
            ));

            Log::info("Email credentials sent successfully to {$email}");
            return true;
        } catch (\Throwable $e) {
            Log::error("Email service error: " . $e->getMessage(), [
                'email' => $email,
                'exception' => get_class($e),
            ]);
            // Return false tapi JANGAN lempar exception agar pendaftaran tetap berhasil
            return false;
        }
    }
}

