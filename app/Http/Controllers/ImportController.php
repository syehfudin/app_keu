<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Donatur;
use App\Models\User;
use App\Models\Pekerjaan;
use Auth;
use DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:import-list|import-create', ['only' => ['index']]);
        $this->middleware('permission:import-create', ['only' => ['importPenghimpun', 'importNasabah', 'templatePenghimpun', 'templateNasabah']]);
    }

    public function index()
    {
        $title = 'Import Data';
        $pekerjaanList = Pekerjaan::orderBy('nama')->pluck('nama')->toArray();
        return view('import.index', compact('title', 'pekerjaanList'));
    }

    // Template download Penghimpun
    public function templatePenghimpun()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Penghimpun');

        $headers = ['NIP', 'Nama', 'Alamat', 'Username', 'Password'];
        $cols = ['A', 'B', 'C', 'D', 'E'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue($cols[$i] . '1', $header);
            $sheet->getStyle($cols[$i] . '1')->getFont()->setBold(true);
        }

        // Sample data
        $sheet->setCellValue('A2', '1.00-00-000.01-22');
        $sheet->setCellValue('B2', 'Contoh Penghimpun');
        $sheet->setCellValue('C2', 'Bandung');
        $sheet->setCellValue('D2', 'penghimpun1');
        $sheet->setCellValue('E2', 'password123');

        // Auto width
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Instructions sheet
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Instruksi');
        $sheet2->setCellValue('A1', 'CARA IMPORT DATA PENGHIMPUN');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->setCellValue('A3', '1. Isi data pada sheet "Data Penghimpun" sesuai kolom');
        $sheet2->setCellValue('A4', '2. NIP: Nomor Induk Pegawai (wajib, unik)');
        $sheet2->setCellValue('A5', '3. Nama: Nama lengkap penghimpun (wajib)');
        $sheet2->setCellValue('A6', '4. Alamat: Alamat lengkap (opsional)');
        $sheet2->setCellValue('A7', '5. Username: Username untuk login (wajib, unik)');
        $sheet2->setCellValue('A8', '6. Password: Password untuk login (wajib)');
        $sheet2->setCellValue('A9', '7. Role akan otomatis di-set sebagai Penghimpun');
        $sheet2->setCellValue('A10', '8. Hapus baris contoh sebelum upload');
        $sheet2->getColumnDimension('A')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_penghimpun.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // Template download Nasabah
    public function templateNasabah()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Nasabah');

        $headers = ['Nama Penghimpun', 'Nama Nasabah', 'Alamat', 'No Telepon', 'Pekerjaan'];
        $cols = ['A', 'B', 'C', 'D', 'E'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue($cols[$i] . '1', $header);
            $sheet->getStyle($cols[$i] . '1')->getFont()->setBold(true);
        }

        // Sample data
        $sheet->setCellValue('A2', 'Muhamad Syehfudin');
        $sheet->setCellValue('B2', 'Contoh Nasabah');
        $sheet->setCellValue('C2', 'Jl. Contoh No. 1');
        $sheet->setCellValue('D2', '081234567890');
        $sheet->setCellValue('E2', 'Karyawan Swasta');

        // Pekerjaan list sheet
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('List Pekerjaan');
        $sheet2->setCellValue('A1', 'Pilih salah satu pekerjaan berikut:');
        $sheet2->getStyle('A1')->getFont()->setBold(true);
        $pekerjaan = Pekerjaan::orderBy('nama')->get();
        $row = 2;
        foreach ($pekerjaan as $p) {
            $sheet2->setCellValue('A' . $row, $p->nama);
            $row++;
        }
        $sheet2->getColumnDimension('A')->setAutoSize(true);

        // Instructions sheet
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Instruksi');
        $sheet3->setCellValue('A1', 'CARA IMPORT DATA NASABAH');
        $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet3->setCellValue('A3', '1. Isi data pada sheet "Data Nasabah" sesuai kolom');
        $sheet3->setCellValue('A4', '2. Nama Penghimpun: Nama pegawai yang sudah terdaftar (wajib)');
        $sheet3->setCellValue('A5', '3. Nama Nasabah: Nama lengkap nasabah (wajib)');
        $sheet3->setCellValue('A6', '4. Alamat: Alamat lengkap (opsional)');
        $sheet3->setCellValue('A7', '5. No Telepon: Nomor telepon (opsional)');
        $sheet3->setCellValue('A8', '6. Pekerjaan: Pilih dari sheet "List Pekerjaan" (opsional)');
        $sheet3->setCellValue('A9', '7. Hapus baris contoh sebelum upload');
        $sheet3->getColumnDimension('A')->setAutoSize(true);

        // Auto width
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_nasabah.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // Import Penghimpun
    public function importPenghimpun(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true);

        $imported = 0;
        $errors = [];
        $rowNum = 0;

        // Get Penghimpun role
        $penghimpunRole = DB::table('roles')->where('name', 'Penghimpun')->first();
        if (!$penghimpunRole) {
            return redirect()->back()->with('error', 'Role Penghimpun tidak ditemukan');
        }

        foreach ($data as $row) {
            $rowNum++;
            if ($rowNum == 1) continue; // Skip header

            $nip = trim($row['A'] ?? '');
            $nama = trim($row['B'] ?? '');
            $alamat = trim($row['C'] ?? '');
            $username = trim($row['D'] ?? '');
            $password = trim($row['E'] ?? '');

            if (empty($nama) || empty($username) || empty($password)) {
                $errors[] = "Baris $rowNum: Nama, Username, Password wajib diisi";
                continue;
            }

            // Check duplicate username
            $existingUser = User::where('username', $username)->first();
            if ($existingUser) {
                $errors[] = "Baris $rowNum: Username '$username' sudah ada";
                continue;
            }

            // Check duplicate NIP
            $existingPegawai = Pegawai::where('nip', $nip)->first();
            if ($existingPegawai) {
                $errors[] = "Baris $rowNum: NIP '$nip' sudah ada";
                continue;
            }

            DB::transaction(function () use ($nip, $nama, $alamat, $username, $password, $penghimpunRole, &$imported) {
                $pegawai = Pegawai::create([
                    'nip' => $nip,
                    'nama' => $nama,
                    'alamat' => $alamat,
                    'default' => false,
                ]);

                $user = User::create([
                    'pegawai_id' => $pegawai->id,
                    'username' => $username,
                    'password' => bcrypt($password),
                ]);

                DB::table('model_has_roles')->insert([
                    'role_id' => $penghimpunRole->id,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $user->id,
                ]);

                $imported++;
            });
        }

        $msg = "Berhasil import $imported data penghimpun.";
        if (count($errors) > 0) {
            $msg .= " " . count($errors) . " error: " . implode('; ', array_slice($errors, 0, 5));
        }

        return redirect()->route('import.index')->with('success', $msg);
    }

    // Import Nasabah
    public function importNasabah(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true);

        $imported = 0;
        $errors = [];
        $rowNum = 0;

        // Build pegawai name → id map
        $pegawaiMap = Pegawai::pluck('id', 'nama')->toArray();

        // Build pekerjaan set
        $pekerjaanList = Pekerjaan::pluck('nama')->toArray();

        foreach ($data as $row) {
            $rowNum++;
            if ($rowNum == 1) continue; // Skip header

            $namaPenghimpun = trim($row['A'] ?? '');
            $namaNasabah = trim($row['B'] ?? '');
            $alamat = trim($row['C'] ?? '');
            $noTelepon = trim($row['D'] ?? '');
            $pekerjaan = trim($row['E'] ?? '');

            if (empty($namaNasabah) || empty($namaPenghimpun)) {
                $errors[] = "Baris $rowNum: Nama Penghimpun dan Nama Nasabah wajib diisi";
                continue;
            }

            // Find pegawai by name
            $pegawaiId = $pegawaiMap[$namaPenghimpun] ?? null;
            if (!$pegawaiId) {
                $errors[] = "Baris $rowNum: Penghimpun '$namaPenghimpun' tidak ditemukan";
                continue;
            }

            // Validate pekerjaan
            if (!empty($pekerjaan) && !in_array($pekerjaan, $pekerjaanList)) {
                $errors[] = "Baris $rowNum: Pekerjaan '$pekerjaan' tidak valid";
                $pekerjaan = '';
            }

            Donatur::create([
                'pegawai_id' => $pegawaiId,
                'nama' => $namaNasabah,
                'alamat' => $alamat,
                'no_telepon' => $noTelepon,
                'pekerjaan' => $pekerjaan,
            ]);

            $imported++;
        }

        $msg = "Berhasil import $imported data nasabah.";
        if (count($errors) > 0) {
            $msg .= " " . count($errors) . " error: " . implode('; ', array_slice($errors, 0, 5));
        }

        return redirect()->route('import.index')->with('success', $msg);
    }
}