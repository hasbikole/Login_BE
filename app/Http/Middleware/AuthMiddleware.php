<?php

namespace App\Http\Middleware;

use App\Http\Requests\UploadRequest;
use Closure;
use Illuminate\Http\Request;
use Exception;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Role;
use App\Traits\ResponseTrait;

use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class authMiddleware
{
    use ResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $role = collect(explode(" ", $role));

        $headers = apache_request_headers(); //get header

        if (!$headers) return $this->responseError(null, 'Yetkisiz Erişim ! ', 404);

        if (!$request->headers->get('Authorization')) {
            return $this->responseError(null, 'Yetkisiz Erişim ! ', 404);
        }
        $request->headers->set('Authorization', $headers['Authorization']); // set header in request

        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) return $this->responseError(null, 'Yetkisiz Erişim ! ', 404);

        if (!$user->is_active || !$user->email_active) {
            return $this->responseError(null, 'Yetkisiz Erişim ! ', 404);
        }

        $userRole = UserRole::where(['user_id' => $user->id])->get()->first();

        $roleName = Role::where(['id' => $userRole->role_id])->get()->first();

        $roleIds = $role->map(function ($element) {
            $roleValue = Role::where(['name' => $element])->get()->first();
            if ($roleValue) {
                return $roleValue->id;
            }
        });

        if (count($roleIds->filter(fn ($element) => $element == $user->id)) == 0) {
            return $this->responseError(null, 'Yetkisiz Erişim ! ', 404);
        }

        $request->decodedToken = [
            "id" => $user->id,
            "email" => $user->email,
            "phone_number" => $user->phone_number,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "roleId" => $userRole->role_id,
            "role" => $roleName->name,
        ];

        return $next($request);
    }
}
