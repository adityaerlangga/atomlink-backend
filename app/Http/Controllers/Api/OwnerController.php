<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ApiController;
use App\Http\Repositories\CoinRepository;
use App\Http\Repositories\LogBalanceRepository;

class OwnerController extends ApiController
{
    // FLOW
    // 1. INPUT OWNER_WHATSAPP_NUMBER
    // 2. KIRIM OTP KE OWNER_WHATSAPP_NUMBER
    // 3. OWNER MASUKKAN OTP
    // 4.1. JIKA OTP BENAR, CEK APAKAH DATA OWNER (OWNER_NAME, CITY_CODE, OWNER_EMAIL) SUDAH TERDAFTAR
    // 4.2. JIKA OWNER SUDAH TERDAFTAR, LOGIN DASHBOARD
    // 4.3. JIKA OWNER BELUM TERDAFTAR, REGISTER

    protected $coinRepository;

    public function __construct(CoinRepository $coinRepository)
    {
        $this->middleware('auth:api', ['except' => ['requestOtp', 'validateOtp', 'register', 'login']]);
        $this->coinRepository = $coinRepository;
    }

    public function requestOtp(Request $request)
    {
        $rules = [
            'owner_whatsapp_number' => 'required|min:11|max:14',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        // VALIDASI NOMOR WHATSAPP OWNER VALID ATAU TIDAK
        $whatsapp_number = $request->owner_whatsapp_number;
        $whatsapp_number_status = $this->checkWhatsappNumber($whatsapp_number);

        if (!$whatsapp_number_status) {
            return $this->sendError(2, 'INVALID WHATSAPP NUMBER');
        }

        $whatsapp_send_endpoint = env('WHATSAPP_API_PROVIDER_URL', 'https://api.fonnte.com') . '/send';
        $whatsapp_api_token = env('WHATSAPP_API_PROVIDER_TOKEN', '85+cYvzaD59_k9hzPz4z');

        // Generate the OTP
        $otp = rand(100000, 999999);
        $message = "Kode OTP Aplikasi Atomlink: {$otp}";

        try {
            $response = Http::withHeaders([
                'Authorization' => $whatsapp_api_token
            ])->post($whatsapp_send_endpoint, [
                'target' => $request->owner_whatsapp_number,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->failed()) {
                return $this->sendError(2, 'Whatsapp API Provider Error: CONNECTION', $response->body());
            }

            if ($response->successful()) {
                // Buat atau ambil data owner berdasarkan owner_whatsapp_number
                $data_owner = Owner::where('owner_whatsapp_number', $whatsapp_number)->first();
                if (!$data_owner) {
                    $data_owner = Owner::create([
                        'owner_code' => generateFiledCode('OWNER'),
                        'owner_whatsapp_number' => $whatsapp_number,
                    ]);
                }

                $data_owner->owner_otp = $otp;
                $data_owner->owner_otp_expired_at = now()->addMinutes(5);
                $data_owner->update();

                return $this->sendResponse(0, 'OTP berhasil dikirim ke ' . $whatsapp_number, [
                    'otp' => $otp,
                ]);
            } else {
                $error = $response->body();
                return $this->sendError(2, 'Whatsapp API Provider Error: ENDPOINT *SEND_MESSAGE*', $error);
            }
        } catch (\Exception $e) {
            return $this->sendError(2, 'Whatsapp API Provider Error: EXCEPTION', $e->getMessage());
        }
    }

    public function validateOtp(Request $request)
    {
        $rules = [
            'owner_whatsapp_number' => 'required|min:11|max:14',
            'otp' => 'required|min:6|max:6',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $whatsapp_number = $request->owner_whatsapp_number;
        $otp = $request->otp;

        $data_owner = Owner::where('owner_whatsapp_number', $whatsapp_number)->first();

        if (!$data_owner) {
            return $this->sendError(2, 'Owner not found');
        }

        if ($data_owner->owner_otp != $otp) {
            return $this->sendError(2, 'Invalid OTP');
        }

        if ($data_owner->owner_otp_expired_at < now()) {
            return $this->sendError(2, 'OTP expired');
        }

        $token = JWTAuth::fromUser($data_owner);

        $is_data_completed = true;
        if ($data_owner->owner_name == null || $data_owner->city_code == null || empty($data_owner->owner_name) || empty($data_owner->city_code)) {
            $is_data_completed = false;
        }

        $data = [
            'is_data_completed' => $is_data_completed,
            'owner' => $data_owner,
            'token' => $token,
        ];

        return $this->sendResponse(0, 'OTP berhasil divalidasi', $data);
    }

    private function checkWhatsappNumber($whatsapp_number)
    {
        $whatsapp_send_endpoint = env('WHATSAPP_API_PROVIDER_URL', 'https://api.fonnte.com') . '/validate';
        $whatsapp_api_token = env('WHATSAPP_API_PROVIDER_TOKEN', '85+cYvzaD59_k9hzPz4z');

        try {
            $response = Http::withHeaders([
                'Authorization' => $whatsapp_api_token
            ])->post($whatsapp_send_endpoint, [
                'target' => $whatsapp_number,
                'countryCode' => '62',
            ]);

            if ($response->failed()) {
                return $this->sendError(2, 'Whatsapp API Provider Error: CONNECTION', $response->body());
            }


            if ($response->successful()) {
                // cek response key 'statys'
                $response_body = json_decode($response->body(), true);

                $validateNumber = count($response_body['not_registered']) == 0; // JIKA NOT_REGISTERED = 0, MAKA NOMOR WA VALID

                return $validateNumber;
            } else {
                return $this->sendError(2, 'Whatsapp API Provider Error: ENDPOINT *VALIDATE*', $response->body());
            }
        } catch (\Exception $e) {
            return $this->sendError(2, 'Whatsapp API Provider Error: EXCEPTION', $e->getMessage());
        }
    }

    public function register(Request $request)
    {
        $rules = [
            'owner_code' => 'required', // FROM OTP VALIDATION
            'owner_name' => 'required',
            'city_code' => 'required',
            'owner_email' => 'email|nullable',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        try {
            $data_owner = Owner::where('owner_code', $request->owner_code)->first();
            if (!$data_owner) {
                return $this->sendError(2, 'OWNER_CODE not found');
            }

            if ($data_owner->owner_name || $data_owner->city_code || $data_owner->owner_email) {
                return $this->sendError(2, 'Owner already registered');
            }


            // BONUS OWNER BARU
            $bonus_saldo = 25000;

            // UPDATE OWNER BALANCE
            $this->coinRepository->updateBalance($request->owner_code, 'INCOME', 'REGISTER', $bonus_saldo, 'Bonus Saldo Pendaftaran Owner Baru');

            $data_owner->owner_name = $request->owner_name;
            $data_owner->city_code = $request->city_code;
            $data_owner->owner_email = $request->owner_email;
            $data_owner->update();

            // JSON FOR NEW BALANCE
            $data_owner = Owner::where('owner_code', $request->owner_code)->first();

            $token = JWTAuth::fromUser($data_owner);

            $data = [
                'owner' => $data_owner,
                'token' => $token
            ];

            return $this->sendResponse(0, "Data Owner berhasil disimpan", $data);
        } catch (\Exception $e) {
            return $this->sendError(2, "Data Owner gagal disimpan", $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $rules = [
            'owner_code' => 'required', // FROM OTP VALIDATION
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        try {
            $data_owner = Owner::where('owner_code', $request->owner_code)->first();
            if (!$data_owner) {
                return $this->sendError(2, 'Data owner tidak ditemukan');
            }

            $token = JWTAuth::fromUser($data_owner);

            $data = [
                'owner' => $data_owner,
                'token' => $token
            ];

            return $this->sendResponse(0, "Berhasil login ke dalam aplikasi", $data);
        } catch (\Exception $e) {
            return $this->sendError(2, "Gagal login ke dalam aplikasi", $e->getMessage());
        }
    }

    public function finance(Request $request)
    {
        // KAS || FINANCE || KEUANGAN
        $rules = [
            'owner_code' => 'required|max:255',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $total_cash_drawers = 1000000;
        $total_cash_banks = 2000000;
        $total_cash_merchants = 3000000;

        $financial = [
            'owner_code' => $request->owner_code,
            'total_cash_drawers' => $total_cash_drawers,
            'total_cash_banks' => $total_cash_banks,
            'total_cash_merchants' => $total_cash_merchants,
        ];

        return $this->sendResponse(0, 'Data kas berhasil ditemukan', $financial);
    }

    public function cash_drawers(Request $request)
    {
        // PEMASUKAN & PENGELUARAN KAS || CASHFLOW
        $rules = [
            'owner_code' => 'required|max:255',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $data = [
            'total_cash_drawers' => 3000000,
            'date' => '10 Juni 2024',
            'outlets' => [
                [
                    'outlet_code' => 'OUTLET-0001',
                    'outlet_name' => 'Superwash',
                    'city_name' => 'Jakarta',
                    'outlet_address' => 'Jl Indonesia, Kel Indonesia Kec Indonesia, Indonesia',
                    'cash' => 1000000,
                ],
                [
                    'outlet_code' => 'OUTLET-0002',
                    'outlet_name' => 'Superclean',
                    'city_name' => 'Jakarta',
                    'outlet_address' => 'Jl Indonesia, Kel Indonesia Kec Indonesia, Indonesia',
                    'cash' => 2000000,
                ],
            ]
        ];

        return $this->sendResponse(0, 'Data Pemasukan dan Pengeluaran berhasil ditemukan', $data);
    }

    public function cash_banks(Request $request)
    {
        // PEMASUKAN & PENGELUARAN KAS || CASHFLOW
        $rules = [
            'owner_code' => 'required|max:255',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $data = [
            'total_cash_banks' => 4000000,
            'date' => '10 Juni 2024',
            'banks' => [
                [
                    'bank_code' => 'BANK-0001',
                    'bank_name' => 'BCA',
                    'bank_logo' => 'https://www.bca.co.id/assets/images/logo-bca.png',
                    'cash' => 1000000,
                ],
                [
                    'bank_code' => 'BANK-0002',
                    'bank_name' => 'Mandiri',
                    'bank_logo' => 'https://www.bankmandiri.co.id/assets/images/logo-mandiri.png',
                    'cash' => 2000000,
                ],
                [
                    'bank_code' => 'BANK-0003',
                    'bank_name' => 'BRI',
                    'bank_logo' => 'https://www.bri.co.id/assets/images/logo-bri.png',
                    'cash' => 3000000,
                ],
                [
                    'bank_code' => 'BANK-0004',
                    'bank_name' => 'BNI',
                    'bank_logo' => 'https://www.bni.co.id/assets/images/logo-bni.png',
                    'cash' => 4000000,
                ],
            ]
        ];

        return $this->sendResponse(0, 'Data Pemasukan dan Pengeluaran berhasil ditemukan', $data);
    }

    public function cash_merchants(Request $request)
    {
        // PEMASUKAN & PENGELUARAN KAS || CASHFLOW
        $rules = [
            'owner_code' => 'required|max:255',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $data = [
            'total_cash_merchants' => 2000000,
            'date' => '10 Juni 2024',
            'merchants' => [
                [
                    'merchant_code' => 'MERCHANT-0001',
                    'merchant_name' => 'OVO',
                    'merchant_logo' => 'https://www.ovo.id/assets/images/logo-ovo.png',
                    'cash' => 1000000,
                ],
                [
                    'merchant_code' => 'MERCHANT-0002',
                    'merchant_name' => 'DANA',
                    'merchant_logo' => 'https://www.dana.id/assets/images/logo-dana.png',
                    'cash' => 2000000,
                ],
                [
                    'merchant_code' => 'MERCHANT-0003',
                    'merchant_name' => 'GOPAY',
                    'merchant_logo' => 'https://www.gopay.id/assets/images/logo-gopay.png',
                    'cash' => 3000000,
                ],
            ]
        ];

        return $this->sendResponse(0, 'Data Pemasukan dan Pengeluaran berhasil ditemukan', $data);
    }

    public function cashflow(Request $request)
    {
        // PEMASUKAN & PENGELUARAN KAS || CASHFLOW
        $rules = [
            'owner_code' => 'required|max:255',
            'date_type' => 'nullable|in:TODAY,WEEK,MONTH,YEAR',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $data = [
            'income_cash' => 2000000,
            'income_bank' => 3500000,
            'expenses' => 1000000,
        ];

        return $this->sendResponse(0, 'Data Pemasukan dan Pengeluaran berhasil ditemukan', $data);
    }

    public function gross_revenue(Request $request)
    {
        // OMSET || GROSS REVENUE
        $rules = [
            'owner_code' => 'required|max:255',
            'date_type' => 'nullable|in:WEEK,MONTH,YEAR',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }


        $data_tahun = [
            ['Januari' => 100],
            ['Februari' => 200],
            ['Maret' => 300],
            ['April' => 400],
            ['Mei' => 500],
            ['Juni' => 600],
            ['Juli' => 700],
            ['Agustus' => 800],
            ['September' => 900],
            ['Oktober' => 1000],
            ['November' => 1100],
            ['Desember' => 1200],
        ];

        $data_minggu = [
            ['Senin' => 100],
            ['Selasa' => 200],
            ['Rabu' => 300],
            ['Kamis' => 400],
            ['Jumat' => 500],
            ['Sabtu' => 600],
            ['Minggu' => 700],
        ];

        if ($request->date_type == 'YEAR') {
            $data = $data_tahun;
        } else if ($request->date_type == 'WEEK') {
            $data = $data_minggu;
        } else {
            $data = $data_tahun;
        }

        return $this->sendResponse(0, 'Data Pemasukan dan Pengeluaran berhasil ditemukan', $data);
    }

    public function today_activities(Request $request)
    {
        // PEMASUKAN & PENGELUARAN KAS || CASHFLOW
        $rules = [
            'owner_code' => 'required|max:255',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $data = [
            'quota_delivery_transaction' => 3,
            'total_delivery_transaction' => 5,
            'quota_late_transaction' => 1,
            'total_late_transaction' => 2,
            'quota_success_transaction' => 4,
            'total_success_transaction' => 3,
        ];

        return $this->sendResponse(0, 'Data Pemasukan dan Pengeluaran berhasil ditemukan', $data);
    }

    public function employee_attendances(Request $request)
    {
        // PEMASUKAN & PENGELUARAN KAS || CASHFLOW
        $rules = [
            'owner_code' => 'required|max:255',
        ];

        $validator = validateThis($request, $rules);

        if ($validator->fails()) {
            return $this->sendError(2, 'Params not complete', validationMessage($validator->errors()));
        }

        $data = [
            'total_employees' => 10,
            'total_attendances' => 8,
            'total_absences' => 2,
        ];

        return $this->sendResponse(0, 'Data Pemasukan dan Pengeluaran berhasil ditemukan', $data);
    }
}
