<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Muzakki;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MuzakkiAccountController extends Controller
{
    /**
     * POST /api/muzakki/create-account
     * Membuat akun muzakki otomatis setelah pendaftaran
     * Body: { nama, nip, email, noHp, password, role, unit_kerja }
     */
    public function createAccount(Request $request)
    {
        $validated = $request->validate([
            'nama'       => 'required|string|max:150',
            'nip'        => 'required|string|max:30',
            'email'      => 'nullable|email|max:100',
            'noHp'       => 'required|string|max:25',
            'password'   => 'required|string|min:6',
            'role'       => 'nullable|string|max:20',
            'unit_kerja' => 'nullable|string|max:200',
        ]);

        // Cek apakah user sudah ada
        $existingUser = User::where('email', $validated['email'])
            ->orWhere('nip', $validated['nip'])
            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'Akun dengan email atau NIP ini sudah terdaftar.',
            ], 400);
        }

        // Buat user baru
        $user = User::create([
            'name'              => $validated['nama'],
            'email'             => $validated['email'] ?: $validated['noHp'] . '@unsil.muzakki',
            'password'          => Hash::make($validated['password']),
            'role'              => $validated['role'] ?? 'muzakki',
            'nip'               => $validated['nip'],
            'no_hp'             => $validated['noHp'],
            'unit_kerja'        => $validated['unit_kerja'] ?? null,
            'is_first_login'    => true,
            'temp_password'     => $validated['password'], // Simpan temporary untuk debugging
        ]);

        // Kirim WhatsApp via Baileys service
        $this->sendWhatsAppCredentials(
            $validated['noHp'],
            $validated['nama'],
            $validated['email'] ?: $validated['noHp'],
            $validated['password']
        );

        return response()->json([
            'success' => true,
            'message' => 'Akun muzakki berhasil dibuat. Credentials telah dikirim via WhatsApp.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'nip'   => $user->nip,
                'role'  => $user->role,
            ],
        ], 201);
    }

    /**
     * POST /api/muzakki/login
     * Login untuk muzakki dengan email/noHp dan password
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari user berdasarkan email atau no_hp (tanpa filter role dulu)
        $user = User::where('email', $request->email)
            ->orWhere('no_hp', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email/No HP atau password salah.',
            ], 401);
        }

        // Validasi role: hanya muzakki yang bisa login di halaman ini
        if ($user->role !== 'muzakki') {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini tidak memiliki akses ke halaman muzakki.',
            ], 403);
        }

        // Hapus token lama
        $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('muzakki-token')->plainTextToken;

        // Ambil data muzakki lengkap dari tabel muzakki
        $muzakki = Muzakki::where('nip', $user->nip)
            ->orWhere('email', $user->email)
            ->orWhere('no_hp', $user->no_hp)
            ->first();

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'nip'            => $user->nip,
                'no_hp'          => $user->no_hp,
                'role'           => $user->role,
                'is_first_login' => $user->is_first_login ?? false,
                'unit_kerja'     => $user->unit_kerja,
            ],
            'muzakki' => $muzakki ? [
                'nama'              => $muzakki->nama,
                'unit_kerja'        => $muzakki->unit_kerja,
                'kategori'          => $muzakki->kategori,
                'jenis_zakat'       => $muzakki->jenis_zakat,
                'nominal'           => $muzakki->nominal,
                'frekuensi'         => $muzakki->frekuensi,
                'kesepakatan_zakat' => $muzakki->kesepakatan_zakat,
            ] : null,
        ]);
    }

    /**
     * POST /api/muzakki/set-password
     * Set password baru saat first login
     */
    public function setPassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'new_password'              => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required|string',
        ]);

        $user->update([
            'password'       => Hash::make($validated['new_password']),
            'is_first_login' => false,
            'temp_password'  => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diatur.',
        ]);
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
            $loginUrl = "{$frontendUrl}/masuk-muzakki";

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
     * GET /api/muzakki/dashboard
     * Mengambil data dashboard muzakki yang sedang login
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Ambil data muzakki dari tabel muzakki
        $muzakki = Muzakki::where('nip', $user->nip)
            ->orWhere('email', $user->email)
            ->orWhere('no_hp', $user->no_hp)
            ->first();

        if (!$muzakki) {
            return response()->json([
                'success' => false,
                'message' => 'Data muzakki tidak ditemukan.',
            ], 404);
        }

        // Ambil transaksi pengumpulan (zakat yang dibayar muzakki ini)
        $transaksi = Transaksi::where('muzakki_id', $muzakki->id)
            ->where('jenis', 'pengumpulan')
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung statistik tahun ini
        $currentYear = date('Y');
        $transaksiTahunIni = $transaksi->filter(function ($t) use ($currentYear) {
            return $t->tahun == $currentYear || date('Y', strtotime($t->created_at)) == $currentYear;
        });

        $totalZakatTahunIni = $transaksiTahunIni->sum('nominal');
        $jumlahPembayaran = $transaksiTahunIni->count();

        // Hitung rata-rata bulanan (jika ada pembayaran)
        $zakatBulanan = $jumlahPembayaran > 0 
            ? round($totalZakatTahunIni / $jumlahPembayaran) 
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nip' => $user->nip,
                    'no_hp' => $user->no_hp,
                    'role' => $user->role,
                    'unit_kerja' => $user->unit_kerja,
                ],
                'muzakki' => [
                    'id' => $muzakki->id,
                    'nama' => $muzakki->nama,
                    'nik' => $muzakki->nik,
                    'nip' => $muzakki->nip,
                    'jenis_kelamin' => $muzakki->jenis_kelamin,
                    'email' => $muzakki->email,
                    'no_hp' => $muzakki->no_hp,
                    'kategori' => $muzakki->kategori,
                    'unit_kerja' => $muzakki->unit_kerja,
                    'pekerjaan' => $muzakki->pekerjaan,
                    'alamat_lengkap' => $muzakki->alamat_lengkap,
                ],
                'stats' => [
                    'total_zakat_tahun_ini' => $totalZakatTahunIni,
                    'jumlah_pembayaran' => $jumlahPembayaran,
                    'zakat_bulanan' => $zakatBulanan,
                    'tahun' => $currentYear,
                ],
                'transaksi' => $transaksi->take(10)->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'kode' => $t->kode,
                        'tanggal' => $t->created_at->format('d M Y'),
                        'jenis_zakat' => $t->kategori ?? 'Zakat Penghasilan',
                        'periode' => $t->bulan && $t->tahun 
                            ? date('F Y', mktime(0, 0, 0, $t->bulan, 1, $t->tahun))
                            : $t->created_at->format('F Y'),
                        'nominal' => $t->nominal,
                        'metode' => $t->metode ?? 'Transfer Bank',
                        'status' => 'Lunas',
                        'deskripsi' => $t->deskripsi,
                    ];
                }),
            ],
        ]);
    }
}
