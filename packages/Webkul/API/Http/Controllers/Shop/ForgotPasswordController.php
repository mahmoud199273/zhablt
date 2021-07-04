<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'email' => 'required|email',
        ]);

        //$response = $this->broker()->sendResetLink(request(['email']));
        $response = true;

        //if ($response == Password::RESET_LINK_SENT) {
        if ($response) {
            return response()->json([
                'status_code' => 200,
                'message' => "email sent successfully",
            ]);
        }

        return response()->json([
            'status_code' => 401,
            'error' => trans($response),
        ]);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker('customers');
    }
}