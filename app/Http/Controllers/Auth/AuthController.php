<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Models\UserRole;
use App\Models\User;
use App\Models\Role;

use Illuminate\Support\Facades\Mail;

use App\Mail\NotifyMail;

use Exception;

use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Response trait to handle return responses.
     */
    use ResponseTrait;

    /**
     * Auth related functionalities.
     *
     * @var AuthRepository
     */
    public $authRepository;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(AuthRepository $ar)
    {
        $this->authRepository = $ar;
    }

    public function login(LoginRequest $request): JsonResponse
    {

        try {
            $credentials = $request->only('email', 'password');

            $token = auth()->attempt($credentials);
            if ($token = auth()->attempt($credentials)) {
                $data =  $this->respondWithToken($token);
                $data['refresh_token'] =  auth()->setTTL(7200)->attempt($credentials);
            } 
            else {
                throw new Exception(' Email veya şifre hatalı !', 401);
                $data = "deneme";
            }

            $data->roleId = UserRole::where(['user_id' => $data->id])->get()->first()->role_id;
            $data->role = Role::where(['id' => $data->roleId])->get()->first()->name;

            $logVariable = [
                'message' => ' numaralı user Giriş Başarılı. status_code: 200'
            ];

            return $this->responseSuccess($data, $logVariable, 'Logged In Successfully !', 200);
        } catch (\Exception $e) {
            $logVariable = [
                'message' =>  $e->getMessage() . " status_code: " . $e->getCode()
            ];
            return $this->responseError("Email veya şifre hatalı", "Email veya şifre hatalı", "Email veya şifre hatalı", 401);
        }
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $requestData = $request->only('phone_number', 'email', 'password', 'first_name', 'last_name', 'gender', 'birth_date');
            // Carbon::parse($requestData['birth_date'])->format('Y-m-d');
            $user = $this->authRepository->register($requestData);
            if ($user) {

                $userRole = UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $request->role_id
                ]);

                

                $role = Role::where(['id' => $userRole->role_id])->get()->first()->name;

                if ($token = auth()->attempt($requestData)) {
                    $data =  $this->respondWithToken($token);
                    $data['refresh_token'] =  auth()->setTTL(7200)->attempt($requestData);
                    $data['role'] = $role;
                    $data['roleId'] = $userRole->role_id;

                    $logVariable = [
                        'message' => $data->email . ' email kullanıcı başarılı bir şekilde kayıt oldu. status_code: 200'
                    ];

                    return $this->responseSuccess($data, $logVariable, "Başarılı bir şekilde kayıt olundu.e-mail onay linki mailinize gönderildi", 200);
                }
            }
        } catch (\Exception $e) {

            $logVariable = [
                'message' => $e->getMessage() . " status_code: " . $e->getCode()
            ];

            return $this->responseError(null, $logVariable, $e->getMessage(), $e->getCode());
        }
    }

    public function refresh(): JsonResponse
    {
        try {
            $data = $this->respondWithToken($this->guard()->refresh());
            return $this->responseSuccess($data, 'Token Refreshed Successfully !');
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), 500);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $data = $this->guard()->user();
        $data['access_token'] = $token;
        return $data;
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard(): \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
    {
        return Auth::guard();
    }

    public function emailConfirm(Request $request): JsonResponse
    {
        try {
            $email = $request->input('email');
            $user = User::where('email', '=', $email)->get()->first();

            if (!$user) {
                throw new Exception("Böyle bir kullanıcı bulunamadı", 404);
            }

            if ($token = JWTAuth::fromUser($user)) {
                $user['token'] =  $this->respondWithToken($token)['access_token'];
            }
            Mail::to($user['email'])->send(new NotifyMail($user));

            $logVariable = [
                'message' => $user->email . ' adlı emaile e-mail onay linki mailinize gönderildi. status_code: 200'
            ];

            return $this->responseSuccess(null, $logVariable, "email gönderildi.", 200);
        } catch (\Exception $e) {
            $logVariable = [
                'message' => "AuthController emailConfirmde bir hata oldu! " . $e->getMessage() . " status_code: " . $e->getCode()
            ];

            return $this->responseError(null, $logVariable, $e->getMessage(), $e->getCode());
        }
    }

    public function emailConfirmCheck(Request $request): JsonResponse
    {
        try {

            $headers = apache_request_headers();
            $request->headers->set('Authorization', "Bearer " . $request->token);
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                throw new Exception("Böyle bir kullanıcı bulunamadı", 404);
            }

            $data = User::where(['id' => $user['id']])->get()->first();

            if (!$data) {
                return $this->responseError(null, 'Böyle bir kullanıcı bulunamadı', 404);
            }

            $data->update(['email_active' => 1]);

            $logVariable = [
                'message' => $user->email . ' adlı e-mail de e-mail başarılı bir şekilde onaylandı. status_code: 200'
            ];

            return $this->responseSuccess($user['id'], $logVariable, "email Confirm Check success", 200);
        } catch (\Exception $e) {
            $logVariable = [
                'message' => "AuthController emailConfirmCheckde bir hata oldu! " . $e->getMessage() . " status_code: " . $e->getCode()
            ];

            return $this->responseError(null, $logVariable, $e->getMessage(), $e->getCode());
        }
    }
}
