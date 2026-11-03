<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PDO;
use Throwable;

class PesananLaundryController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim(strip_tags((string) $request->query('q', '')));
        $jenisLayananId = $request->query('jenis_layanan_id');
        $jenisLayananId = is_numeric($jenisLayananId) && (int) $jenisLayananId > 0
            ? (int) $jenisLayananId
            : null;

        $pesanan = [];
        $jenisLayanan = [];
        $error = null;

        try {
            $pdo = DB::connection()->getPdo();

            $jenisLayanan = $pdo->query(
                'SELECT id, nama_layanan FROM jenis_layanan ORDER BY nama_layanan ASC'
            )->fetchAll(PDO::FETCH_OBJ);

            $sql = 'SELECT p.id, p.kode_pesanan, p.nama_pelanggan, p.nomor_telepon, p.berat_kg,
                           p.jenis_layanan_id, p.created_at, p.updated_at, j.nama_layanan
                    FROM pesanan_laundry p
                    INNER JOIN jenis_layanan j ON j.id = p.jenis_layanan_id
                    WHERE 1 = 1';

            $params = [];

            if ($keyword !== '') {
                $sql .= ' AND (p.kode_pesanan LIKE :keyword_kode
                            OR p.nama_pelanggan LIKE :keyword_nama
                            OR p.nomor_telepon LIKE :keyword_telepon)';
                $like = '%'.$keyword.'%';
                $params[':keyword_kode']    = [$like, PDO::PARAM_STR];
                $params[':keyword_nama']    = [$like, PDO::PARAM_STR];
                $params[':keyword_telepon'] = [$like, PDO::PARAM_STR];
            }

            if ($jenisLayananId !== null) {
                $sql .= ' AND p.jenis_layanan_id = :jenis_layanan_id';
                $params[':jenis_layanan_id'] = [$jenisLayananId, PDO::PARAM_INT];
            }

            $sql .= ' ORDER BY p.id DESC';

            $statement = $pdo->prepare($sql);

            foreach ($params as $name => [$value, $type]) {
                $statement->bindValue($name, $value, $type);
            }

            $statement->execute();
            $pesanan = $statement->fetchAll(PDO::FETCH_OBJ);
        } catch (Throwable $exception) {
            report($exception);
            $error = 'Data pesanan belum dapat dimuat. Silakan coba lagi.';
        }

        return view('pesanan.index', compact('pesanan', 'jenisLayanan', 'keyword', 'jenisLayananId', 'error'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        try {
            $pdo = DB::connection()->getPdo();

            $duplicateMessage = $this->duplicateMessage($pdo, $data);

            if ($duplicateMessage !== null) {
                return back()->withErrors($duplicateMessage)->withInput();
            }

            if (! $this->jenisLayananExists($pdo, $data['jenis_layanan_id'])) {
                return back()->withErrors(['jenis_layanan_id' => 'Jenis layanan tidak tersedia.'])->withInput();
            }

            $now = now()->format('Y-m-d H:i:s');

            $statement = $pdo->prepare(
                'INSERT INTO pesanan_laundry
                    (kode_pesanan, nama_pelanggan, nomor_telepon, berat_kg, jenis_layanan_id, created_at, updated_at)
                 VALUES
                    (:kode_pesanan, :nama_pelanggan, :nomor_telepon, :berat_kg, :jenis_layanan_id, :created_at, :updated_at)'
            );

            $statement->bindValue(':kode_pesanan', $data['kode_pesanan'], PDO::PARAM_STR);
            $statement->bindValue(':nama_pelanggan', $data['nama_pelanggan'], PDO::PARAM_STR);
            $statement->bindValue(':nomor_telepon', $data['nomor_telepon'], PDO::PARAM_STR);
            $statement->bindValue(':berat_kg', $data['berat_kg'], PDO::PARAM_INT);
            $statement->bindValue(':jenis_layanan_id', $data['jenis_layanan_id'], PDO::PARAM_INT);
            $statement->bindValue(':created_at', $now, PDO::PARAM_STR);
            $statement->bindValue(':updated_at', $now, PDO::PARAM_STR);
            $statement->execute();

            return redirect()
                ->route('pesanan.index')
                ->with('success', 'Pesanan '.$data['kode_pesanan'].' berhasil ditambahkan.');
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Pesanan gagal disimpan karena terjadi kesalahan pada basis data.');
        }
    }

    public function edit($id)
    {
        $pesanan = null;
        $jenisLayanan = [];

        try {
            $pdo = DB::connection()->getPdo();

            $statement = $pdo->prepare(
                'SELECT id, kode_pesanan, nama_pelanggan, nomor_telepon, berat_kg, jenis_layanan_id
                 FROM pesanan_laundry
                 WHERE id = :id'
            );
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $statement->execute();
            $pesanan = $statement->fetch(PDO::FETCH_OBJ);

            $jenisLayanan = $pdo->query(
                'SELECT id, nama_layanan FROM jenis_layanan ORDER BY nama_layanan ASC'
            )->fetchAll(PDO::FETCH_OBJ);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('pesanan.index')
                ->with('error', 'Form edit tidak dapat dibuka karena terjadi kesalahan pada basis data.');
        }

        if (! $pesanan) {
            abort(404);
        }

        return view('pesanan.edit', compact('pesanan', 'jenisLayanan'));
    }

    public function update(Request $request, $id)
    {
        $id   = (int) $id;
        $data = $this->validatedData($request);

        try {
            $pdo = DB::connection()->getPdo();

            $statement = $pdo->prepare('SELECT id FROM pesanan_laundry WHERE id = :id');
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->execute();

            if (! $statement->fetchColumn()) {
                return redirect()
                    ->route('pesanan.index')
                    ->with('error', 'Pesanan yang akan diperbarui tidak ditemukan.');
            }

            $duplicateMessage = $this->duplicateMessage($pdo, $data, $id);

            if ($duplicateMessage !== null) {
                return back()->withErrors($duplicateMessage)->withInput();
            }

            if (! $this->jenisLayananExists($pdo, $data['jenis_layanan_id'])) {
                return back()->withErrors(['jenis_layanan_id' => 'Jenis layanan tidak tersedia.'])->withInput();
            }

            $statement = $pdo->prepare(
                'UPDATE pesanan_laundry
                 SET kode_pesanan = :kode_pesanan,
                     nama_pelanggan = :nama_pelanggan,
                     nomor_telepon = :nomor_telepon,
                     berat_kg = :berat_kg,
                     jenis_layanan_id = :jenis_layanan_id,
                     updated_at = :updated_at
                 WHERE id = :id'
            );

            $statement->bindValue(':kode_pesanan', $data['kode_pesanan'], PDO::PARAM_STR);
            $statement->bindValue(':nama_pelanggan', $data['nama_pelanggan'], PDO::PARAM_STR);
            $statement->bindValue(':nomor_telepon', $data['nomor_telepon'], PDO::PARAM_STR);
            $statement->bindValue(':berat_kg', $data['berat_kg'], PDO::PARAM_INT);
            $statement->bindValue(':jenis_layanan_id', $data['jenis_layanan_id'], PDO::PARAM_INT);
            $statement->bindValue(':updated_at', now()->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->execute();

            return redirect()
                ->route('pesanan.index')
                ->with('success', 'Pesanan '.$data['kode_pesanan'].' berhasil diperbarui.');
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Pesanan gagal diperbarui karena terjadi kesalahan pada basis data.');
        }
    }

    public function destroy($id)
    {
        try {
            $pdo = DB::connection()->getPdo();

            $statement = $pdo->prepare('DELETE FROM pesanan_laundry WHERE id = :id');
            $statement->bindValue(':id', (int) $id, PDO::PARAM_INT);
            $statement->execute();

            if ($statement->rowCount() === 0) {
                return redirect()
                    ->route('pesanan.index')
                    ->with('error', 'Pesanan tidak ditemukan atau sudah dihapus.');
            }

            return redirect()
                ->route('pesanan.index')
                ->with('success', 'Pesanan berhasil dihapus.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('pesanan.index')
                ->with('error', 'Pesanan gagal dihapus karena terjadi kesalahan pada basis data.');
        }
    }

    private function validatedData(Request $request): array
    {
        $data = [
            'kode_pesanan'    => trim(strip_tags((string) $request->input('kode_pesanan', ''))),
            'nama_pelanggan'  => trim(strip_tags((string) $request->input('nama_pelanggan', ''))),
            'nomor_telepon'   => trim(strip_tags((string) $request->input('nomor_telepon', ''))),
            'berat_kg'        => $request->input('berat_kg'),
            'jenis_layanan_id' => $request->input('jenis_layanan_id'),
        ];

        $validator = Validator::make($data, [
            'kode_pesanan'    => ['required', 'string', 'size:10', 'regex:/^[A-Za-z0-9]+$/'],
            'nama_pelanggan'  => ['required', 'string', 'min:3', 'max:100'],
            'nomor_telepon'   => ['required', 'string', 'min:10', 'max:20'],
            'berat_kg'        => ['required', 'integer', 'between:1,20'],
            'jenis_layanan_id' => ['required', 'integer'],
        ], [
            'kode_pesanan.required'    => 'Kode pesanan wajib diisi.',
            'kode_pesanan.size'        => 'Kode pesanan harus tepat 10 karakter.',
            'kode_pesanan.regex'       => 'Kode pesanan hanya boleh berisi huruf dan angka.',
            'nama_pelanggan.required'  => 'Nama pelanggan wajib diisi.',
            'nama_pelanggan.min'       => 'Nama pelanggan minimal 3 karakter.',
            'nama_pelanggan.max'       => 'Nama pelanggan maksimal 100 karakter.',
            'nomor_telepon.required'   => 'Nomor telepon wajib diisi.',
            'nomor_telepon.min'        => 'Nomor telepon minimal 10 karakter.',
            'nomor_telepon.max'        => 'Nomor telepon maksimal 20 karakter.',
            'berat_kg.required'        => 'Berat cucian wajib diisi.',
            'berat_kg.integer'         => 'Berat cucian harus berupa bilangan bulat.',
            'berat_kg.between'         => 'Berat cucian harus antara 1 sampai 20 kg.',
            'jenis_layanan_id.required' => 'Jenis layanan wajib dipilih.',
            'jenis_layanan_id.integer'  => 'Jenis layanan tidak valid.',
        ]);

        $validator->validate();

        return $data;
    }

    private function duplicateMessage(PDO $pdo, array $data, ?int $ignoreId = null): ?array
    {
        $messages = [];

        if ($this->duplicateExists($pdo, 'kode_pesanan', $data['kode_pesanan'], $ignoreId)) {
            $messages['kode_pesanan'] = 'Kode pesanan sudah digunakan.';
        }

        if ($this->duplicateExists($pdo, 'nomor_telepon', $data['nomor_telepon'], $ignoreId)) {
            $messages['nomor_telepon'] = 'Nomor telepon sudah digunakan.';
        }

        return $messages === [] ? null : $messages;
    }

    private function duplicateExists(PDO $pdo, string $column, string $value, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT id FROM pesanan_laundry WHERE '.$column.' = :value';

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :id';
        }

        $statement = $pdo->prepare($sql);
        $statement->bindValue(':value', $value, PDO::PARAM_STR);

        if ($ignoreId !== null) {
            $statement->bindValue(':id', $ignoreId, PDO::PARAM_INT);
        }

        $statement->execute();

        return (bool) $statement->fetchColumn();
    }

    private function jenisLayananExists(PDO $pdo, int $id): bool
    {
        $statement = $pdo->prepare('SELECT id FROM jenis_layanan WHERE id = :id');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return (bool) $statement->fetchColumn();
    }
}
