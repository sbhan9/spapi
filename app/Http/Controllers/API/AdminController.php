<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /* method cek apakah di dalam url ada api key atau tidak */
    private function cek_api_key($api_key)
    {
        /* ambil data user yang sedang login */
        $data_user = User::firstWhere('username', auth()->user()->username);

        /* cek url apakah ada request key */
        if (!empty($api_key)) {
            /* cek apakah api key yang login sudah sesuai dengan user yang login */
            if ($data_user->api_key == $api_key) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function students(Request $request)
    {
        if ($this->cek_api_key($request->key)) {
            return response()->json([
                'msg' => 'success',
                'students' => Student::with('user')->get()
            ]);
        } else {
            return response()->json([
                'msg' => 'api key not registered'
            ]);
        }
    }

    public function add_siswa(Request $request)
    {
        /* cek request dalam url apakah ada api key atau ngga */
        if ($this->cek_api_key($request->key)) {
            /* validasi input data siswa */
            $validasi_siswa = Validator::make($request->all(), [
                'nama' => 'required|string',
                'kelas' => 'required',
                'nis' => 'required|numeric|digits_between:6,10|unique:students',
                'nisn' => 'required|numeric|digits_between:6,13|unique:students',
                'email' => 'required|email|unique:students',
                'alamat' => 'required',
            ]);

            /* jika input data siswa terdapat kesalahan maka tampilkan pesan error */
            if ($validasi_siswa->fails()) {
                return response()->json([
                    'msg' => 'add siswa failed',
                    'errors' => $validasi_siswa->errors()
                ]);
            }

            /* proses tambah siswa baru */
            $add_siswa = Student::create([
                'nama' => $request->nama,
                'kelas' => $request->kelas,
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'email' => $request->email,
                'alamat' => $request->alamat,
                'user_id' => auth()->user()->id,
            ]);

            /* tampilkan pesan jika proses input data berhasil atau ngga */
            if ($add_siswa) {
                return response()->json([
                    'msg' => 'add siswa success',
                    'siswa' => $request->post()
                ]);
            } else {
                return response()->json([
                    'msg' => 'add siswa failed'
                ]);
            }
        } else {
            return response()->json([
                'msg' => 'api key not registered'
            ]);
        }
    }

    public function update_siswa(Request $request)
    {
        /* cek apakah dalam request url terdapat id siswa atau ngga */
        if (!empty($request->id_siswa)) {
            /* cari siswa yang idnya sama dengan request pada url */
            if (Student::firstWhere('id', $request->id_siswa)) {
                /* jika ada maka ambil semua datanya */
                $data_siswa = Student::firstWhere('id', $request->id_siswa);
                /* cek request api key */
                if ($this->cek_api_key($request->key)) {
                    /* jika user merubah nis siswa maka menggunakan validasi di bawah */
                    if ($request->nis != $data_siswa->nis) {
                        $validasi_nis = 'required|numeric|digits_between:6,10|unique:students';
                    } else {
                        /* jika user tidak merubah nis siswa maka menggunakan validasi di bawah */
                        $validasi_nis = 'required|numeric';
                    }

                    /* jika user merubah nisn siswa maka menggunakan validasi di bawah */
                    if ($request->nisn != $data_siswa->nisn) {
                        /* jika user tidak merubah nisn siswa maka menggunakan validasi di bawah */
                        $validasi_nisn = 'required|numeric|digits_between:6,13|unique:students';
                    } else {
                        $validasi_nisn = 'required|numeric';
                    }

                    if ($request->email != $data_siswa->email) {
                        /* jika user merubah email siswa maka menggunakan validasi di bawah */
                        $validasi_email = 'required|email|unique:students';
                    } else {
                        /* jika user tidak merubah email siswa maka menggunakan validasi di bawah */
                        $validasi_email = 'required|email';
                    }

                    /* cek validasi input data */
                    $validasi_siswa = Validator::make($request->all(), [
                        'nama' => 'required|string',
                        'kelas' => 'required',
                        'nis' => $validasi_nis,
                        'nisn' => $validasi_nisn,
                        'email' => $validasi_email,
                        'alamat' => 'required',
                    ]);

                    /* tampilkan pesan error jika terdapat kesalahan input data */
                    if ($validasi_siswa->fails()) {
                        return response()->json([
                            'msg' => 'update siswa failed',
                            'errors' => $validasi_siswa->errors()
                        ]);
                    }

                    /* update data siswa ke database */
                    $update_siswa = Student::where('id', $request->id_siswa)->update([
                        'nama' => $request->nama,
                        'kelas' => $request->kelas,
                        'nis' => $request->nis,
                        'nisn' => $request->nisn,
                        'email' => $request->email,
                        'alamat' => $request->alamat,
                        'user_id' => auth()->user()->id,
                    ]);

                    /* tampilkan pesan jika update data berhasil atau gagal */
                    if ($update_siswa) {
                        return response()->json([
                            'msg' => 'update siswa success',
                            'siswa' => $request->post()
                        ]);
                    } else {
                        return response()->json([
                            'msg' => 'update siswa failed'
                        ]);
                    }
                } else {
                    return response()->json([
                        'msg' => 'api key not registered'
                    ]);
                }
            } else {
                return response()->json([
                    'msg' => 'id siswa not registered'
                ]);
            }
        } else {
            return response()->json([
                'msg' => 'id siswa required'
            ]);
        }
    }

    public  function delete_siswa(Request $request)
    {
        /* cek di dalam formnya ada request id ngga */
        if (!empty($request->id_siswa)) {
            /* jika ada cek datanya */
            $data_siswa = Student::firstWhere('id', $request->id_siswa);
            if ($data_siswa) {
                /* cek apikey di dalam request */
                if ($this->cek_api_key($request->key)) {
                    /* proses delete siswa */
                    $delete_siswa = Student::where('id', $request->id_siswa)->delete();
                    /* jika prosesnya berhasil atau gagal maka tampilkan pesan */
                    if ($delete_siswa) {
                        return response()->json([
                            'msg' => 'deleted siswa success',
                        ]);
                    } else {
                        return response()->json([
                            'msg' => 'deleted siswa failed',
                        ]);
                    }
                } else {
                    return response()->json([
                        'msg' => 'api key not registered'
                    ]);
                }
            } else {
                return response()->json([
                    'msg' => 'id siswa not registered'
                ]);
            }
        } else {
            return response()->json([
                'msg' => 'id siswa required'
            ]);
        }
    }
}