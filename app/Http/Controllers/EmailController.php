<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\EmailVerification;
use App\Mail\EmailConfirmVerification;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentMoved;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentDoctorInfo;

class EmailController extends Controller
{
    public static function sendEmailRegisterVerification($user, $token)
    {

        $email = User::where('id', $user->id)->first()->email;
        if (Mail::to($email)->send(new EmailVerification($user, $token))) {
            return true;
        } else {
            return false;
        }
    }
    public static function sendEmailConfirmVerification($user)
    {
        $email = User::where('id', $user->id)->first()->email;
        if (Mail::to($email)->send(new EmailConfirmVerification($user))) {
            return true;
        } else {
            return false;
        }
    }
    public static function sendEmailAppointmentDoctorInfo($patient, $doctor, $time, $date, $description)
    {
        $email = User::where('id', $doctor->id)->first()->email;
        if (Mail::to($email)->send(new AppointmentDoctorInfo($patient, $doctor, date('H:i', $time), $date, $description, $patient))) {
            return true;
        } else {
            return false;
        }
    }
    public static function sendEmailAppointmentMoved($patient, $doctor, $time, $date, $description)
    {
        $email = User::where('id', $patient->id)->first()->email;
        if (Mail::to($email)->send(new AppointmentMoved($patient, $doctor, date('H:i', $time), $date, $description))) {
            return true;
        } else {
            return false;
        }
    }
    public static function sendEmailAppointmentCancelled($doctor, $time, $date, $isPatient)
    {
        $email = User::where('id', $doctor->id)->first()->email;
        if (Mail::to($email)->send(new AppointmentCancelled($doctor, date('H:i', strtotime($time)), date('Y-m-d', strtotime($date)), $isPatient))) {
            return true;
        } else {
            return false;
        }
    }
    public static function sendEmailAppointmentConfirmed($patient, $doctor, $time, $date, $description)
    {
        $email = User::where('id', $patient->id)->first()->email;
        if (Mail::to($email)->send(new AppointmentConfirmed($patient, $doctor, date('H:i', strtotime($time)), date('Y-m-d', strtotime($date)), $description))) {
            return true;
        } else {
            return false;
        }
    }
}
