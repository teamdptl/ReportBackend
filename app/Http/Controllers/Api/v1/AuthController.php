<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignUpRequest;
use App\Http\Resources\Error\RequestNotValidated;
use App\Http\Traits\ApiResponse;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'mssv' => 'required',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        if ($validator->fails()){
            return new RequestNotValidated($validator);
        }
        $user = $this->loginWithCustomUsername($request);
        if ($user == null)
            return $this->responseJSON([
                'error' => 1,
                'message' => 'Sai mật khẩu hoặc tên đăng nhập (mssv) !'
            ]);
        $roles = $this->getRoleNames($user);
        $token = $user->createToken($request->input('device_name', 'null'), $roles);
        return $this->responseJSON([
            'error' => 0,
            'message' => 'Đăng nhập thành công',
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
            'device_name' => $token->accessToken->name,
            'role' => join(",", $roles),
        ]);
    }

    private function loginWithCustomUsername(Request $request){
        $validator = Validator::make($request->all(), [
            'mssv' => 'numeric',
        ]);
        $field_for_username = 'student_code';

        // Xử lý cho các user không dùng mã số sinh viên (worker, manager)
        if ($validator->fails()){
            $field_for_username = 'username';
        }

        // Xử lý cho sinh viên dùng mssv
        $user = User::where($field_for_username, $request->mssv)->first();
        if (!$user || !Hash::check($request->password, $user->password))
            return null;
        return $user;
    }

    public function getCurrentUser(Request $request){
        $user = $request->user();
        return $this->responseJSON([
            'name' => $user->name,
            'username' => $user->username,
            'student_code' => $user->student_code,
            'is_active' => $user->is_active,
            'email' => $user->email,
            'role' => $user->roles->map(function ($item){
                return $item['name'];
            })->join('|')
        ]);

    }

    public function logout(Request $request){
        if ($request->user() != null){
            $request->user()->currentAccessToken()->delete();
            return $this->responseJSON([
                'error' => 0,
                'message' => 'Đăng xuất thành công'
            ]);
        }
        return $this->responseJSON([
            'error' => 1,
            'message' => 'Không thể đăng xuất'
        ]);
    }

    public function register(SignUpRequest $request){
        $validated = $request->validated();
        $user = new User([
            "name" => $validated['firstName']. " ". $validated['lastName'],
            "email" => $validated['email'],
            "username" => $validated['email'],
            "password" => $validated['password'],
            "student_code" => $validated['student_code'],
            "is_active" => true,
        ]);
        try {
            Role::find(1)->users()->save($user);
        } catch (\Exception $e) {
            return [
                "error" => 1,
                "message" => "Không thể lưu database do trùng dữ liệu (email)"
            ];
        }

        return [
            "error" => 0,
            "message" => "Tạo thành công tài khoản",
        ];
    }

    private function getRoleNames($user): array {
        $roleNames = [];
        $roles = $user->roles()->get();
        foreach ($roles as $role){
            $roleNames[] = $role->name;
        }
        return $roleNames;
    }
}
