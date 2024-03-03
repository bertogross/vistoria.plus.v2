<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendingEmail;
use App\Models\User;

class PostmarkappController extends Controller
{
    public static function sendEmail($to, $name, $subject, $content, $template)
    {
        Mail::to($to)->send(new SendingEmail($name, $subject, $content, $template));

        return 'Email sent successfully!';
    }
}
