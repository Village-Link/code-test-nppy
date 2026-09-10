<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CustomerRegistrationService;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    private $customerRegistrationService;

    public function __construct(CustomerRegistrationService $customerRegistrationService)
    {
        $this->customerRegistrationService = $customerRegistrationService;
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
    }

    protected function create(array $data): User
    {
        return $this->customerRegistrationService->create($data);
    }
}
