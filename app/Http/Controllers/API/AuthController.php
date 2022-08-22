<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        /* Validasi Data */
        $validasi = Validator::make($request->all(), [
            'name' => 'required|min:5|max:10|string',
            'no_hp' => 'required|numeric|digits_between:11,13|unique:users',
            'username' => 'required|min:5|max:10|unique:users',
            'password' => 'required|min:5',
            'alamat' => 'required',
        ]);

        /* Cek Validasi Input Data */
        if ($validasi->fails()) {
            return response()->json([
                'msg' => 'registrasi failed',
                'errors' => $validasi->errors()
            ]);
        }

        /* Insert Data ke Database */
        $api_key = bcrypt($request->username);
        $register = User::create([
            'name' => $request->name,
            'no_hp' => $request->no_hp,
            'username' => $request->username,
            'password' =>  bcrypt($request->password),
            'alamat' => $request->alamat,
            'api_key' => hash('crc32b', $api_key)
        ]);

        /* Cek Berhasil Insert atau Ngga */
        if ($register) {
            return response()->json([
                'msg' => 'registrasi success'
            ]);
        } else {
            return response()->json([
                'msg' => 'registrasi failed'
            ]);
        }
    }

    public function login(Request $request)
    {
        /* Cek Validasi Login */
        $data_login = $request->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);

        /* Cek Username dan Password di Database */
        if (Auth::attempt($data_login)) {
            $token = $request->user()->createToken(auth()->user()->username)->plainTextToken;
            User::where('username', auth()->user()->username)->update(['user_token' => $token]);

            return response()->json([
                'msg' => 'login success',
                'result' => [
                    'token' => $token
                ]
            ]);
        }

        /* Username atau Password Salah */
        return response()->json([
            'msg' => 'login failed'
        ]);
    }
}