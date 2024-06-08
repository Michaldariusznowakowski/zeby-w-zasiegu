<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Offer;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Http\Controllers\EmailController;

class AppointmentController extends Controller
{
    function saveAppointmentFromPatient(Request $request)
    {
        $request->validate([
            'doctor_id' => 'numeric | required | min:1',
            'date' => 'date | required',
            'time' => 'required',
            'description' => 'string | required | min:3 | max:255',
        ]);

        $doctor = User::find($request->doctor_id);
        if (!$doctor) {
            return back()->with('error', 'Nie znaleziono lekarza');
        }
        $offer = Offer::where('doctor_id', $request->doctor_id)->first();
        if (!$offer) {
            return back()->with('error', 'Nie znaleziono oferty');
        }
        $doctor_working_hours = json_decode($offer->working_hours, true);
        $day = strtolower(date('l', strtotime($request->date)));
        $start = $doctor_working_hours[$day]['start'];
        $end = $doctor_working_hours[$day]['end'];
        $ignore = $doctor_working_hours[$day]['ignore'];
        if ($ignore) {
            return back()->with('error', 'Lekarz nie pracuje w ten dzień');
        }
        $start = strtotime($start);
        $end = strtotime($end);
        $time = strtotime($request->time);
        if ($time < $start || $time > $end) {
            return back()->with('error', 'Nie można zarezerwować wizyty na tę godzinę');
        }
        $duration = $request->duration;
        if ($time + $duration * 60 > $end) {
            return back()->with('error', 'Nie można zarezerwować wizyty na tę godzinę');
        }
        $events = Event::where('doctor_id', $request->doctor_id)
            ->where('start_date', '>=', $request->date . date('H:i:s', $time))
            ->where('end_date', '<=', $request->date . date('H:i:s', $time + $duration * 60))
            ->where('cancelled', false)->get();
        if (count($events) > 0) {
            return back()->with('error', 'Zarezerwowana wizyta na tę godzinę');
        }
        $event = new Event();
        $event->doctor_id = $request->doctor_id;
        $event->patient_id = auth()->user()->id;
        $event->start_date = $request->date . ' ' . date('H:i:s', $time);
        $event->end_date = $request->date . ' ' . date('H:i:s', $time + $duration * 60);
        $event->description = $request->description;
        $event->save();
        $patient = auth()->user();
        EmailController::sendEmailAppointmentDoctorInfo($patient, $doctor, $time, $request->date, $request->description);
        return redirect()->route('home')->with('success', 'Wizyta została zarezerwowana,
        oczekuj na potwierdzenie od lekarza');
    }
    public static function saveMove(Request $request)
    {
        $request->validate([
            'id' => 'numeric | required | min:1',
            'date' => 'date | required',
            'time' => 'required',
            'duration' => 'numeric | required | min:1',
        ]);
        $event = Event::find($request->id);
        if (!$event) {
            return back()->with('error', 'Nie znaleziono wizyty');
        }
        $offer = Offer::where('doctor_id', $event->doctor_id)->first();
        if (!$offer) {
            return back()->with('error', 'Nie znaleziono oferty');
        }
        $doctor_working_hours = json_decode($offer->working_hours, true);
        $day = strtolower(date('l', strtotime($request->date)));
        $start = $doctor_working_hours[$day]['start'];
        $end = $doctor_working_hours[$day]['end'];
        $ignore = $doctor_working_hours[$day]['ignore'];
        if ($ignore) {
            return back()->with('error', 'Lekarz nie pracuje w ten dzień');
        }
        $start = strtotime($start);
        $end = strtotime($end);
        $time = strtotime($request->time);
        if ($time < $start || $time > $end) {
            return back()->with('error', 'Nie można zarezerwować wizyty na tę godzinę');
        }

        $duration = $request->duration;
        if ($time + $duration * 60 > $end) {
            return back()->with('error', 'Nie można zarezerwować wizyty na tę godzinę');
        }
        $events = Event::where('doctor_id', $event->doctor_id)
            ->where('start_date', '>=', $request->date . date('H:i:s', $time))
            ->where('end_date', '<=', $request->date . date('H:i:s', $time + $duration * 60))
            ->where('cancelled', false)->get();
        if (count($events) > 0) {
            return back()->with('error', 'Zarezerwowana wizyta na tę godzinę');
        }
        $event->start_date = $request->date . ' ' . date('H:i:s', $time);
        $event->end_date = $request->date . ' ' . date('H:i:s', $time + $duration * 60);
        $event->confirmed = true;
        $event->save();
        $today = date('Y-m-d');
        $patient = User::find($event->patient_id);
        $doctor = User::find($event->doctor_id);
        EmailController::sendEmailAppointmentMoved($patient, $doctor, $time, $request->date, $event->description);
        return redirect()->route('home')->with('success', 'Wizyta została przeniesiona na dzień ' . $request->date . ' o godzinie ' . $request->time . '.
        Przesłano powiadomienie o zmianie terminu wizyty do pacjenta');
    }
    private static function calcDaysInMonth($month, $year)
    {

        $days = 0;
        switch ($month) {
            case 1:
                $days = 31;
                break;
            case 2:
                if ($year % 4 == 0) {
                    $days = 29;
                } else {
                    $days = 28;
                }
                break;
            case 3:
                $days = 31;
                break;
            case 4:
                $days = 30;
                break;
            case 5:
                $days = 31;
                break;
            case 6:
                $days = 30;
                break;
            case 7:
                $days = 31;
                break;
            case 8:
                $days = 31;
                break;
            case 9:
                $days = 30;
                break;
            case 10:
                $days = 31;
                break;
            case 11:
                $days = 30;
                break;
            case 12:
                $days = 31;
                break;
        }
        return $days;
    }
    function showCalendar(Request $request)
    {
        validator($request->route()->parameters(), [
            'date' => 'date | required',
        ]);
        return self::showCalendarAt($request->date);
    }
    function showCalendarToday()
    {
        $today = date('Y-m-d');
        return self::showCalendarAt($today);
    }
    private static function showCalendarAt($date)
    {
        $selected_date = $date;
        $number_of_days = self::calcDaysInMonth(date('m', strtotime($selected_date)), date('Y', strtotime($selected_date)));

        $year = date('Y', strtotime($selected_date));
        $month = date('m', strtotime($selected_date));

        $calendar = [];
        $number_of_days_to_skip = 0;
        $first_day_of_week = date('w', strtotime($year . '-' . $month . '1'));
        if ($first_day_of_week > 0) {
            $number_of_days_to_skip = $first_day_of_week;
        }
        $counter = $number_of_days_to_skip;
        for ($i = 1; $i <= $number_of_days; $i++) {
            if ($counter == 7) {
                $counter = 0;
            }
            $calendar[] = [
                'day' => date('d', strtotime($year . '-' . $month . '-' . $i)),
                'appointments' => Event::whereBetween('start_date', [
                    date('Y-m-d H:i:s', strtotime($year . '-' . $month . '-' . $i . ' 00:00:00')),
                    date('Y-m-d H:i:s', strtotime($year . '-' . $month . '-' . $i . ' 23:59:59'))
                ])->get(),
                'day_of_week' => $counter,
            ];
            $counter++;
        }

        $today = date('Y-m-d');
        $events = Event::where('start_date', '>=', $today)->get();

        $next_date = date('Y-m-d', strtotime($selected_date . ' +1 month'));
        $previous_date = date('Y-m-d', strtotime($selected_date . ' -1 month'));
        return view('appointment.calendar', [
            'year' => $year, 'month' => $month,
            'calendar' => $calendar,
            'number_of_days_to_skip' => $number_of_days_to_skip,
            'today' => $today,
            'events' => $events,
            'next_date' => $next_date,
            'previous_date' => $previous_date,
        ]);
    }
    function cancel(Request $request)
    {
        $request->validate([
            'id' => 'numeric | required | min:1',
        ]);
        $event = Event::find($request->id);
        if (!$event) {
            return back()->with('error', 'Nie znaleziono wizyty');
        }
        if ($event->patient_id != auth()->user()->id && $event->doctor_id != auth()->user()->id) {
            return back()->with('error', 'Nie masz uprawnień do anulowania tej wizyty');
        }
        $event->cancelled = true;
        $event->save();
        $patient = User::find($event->patient_id);
        $doctor = User::find($event->doctor_id);
        if (Auth::user()->role == 'patient') {
            EmailController::sendEmailAppointmentCancelled($doctor, $event->start_date, $event->start_date, false);
        } else {
            EmailController::sendEmailAppointmentCancelled($patient, $event->start_date, $event->start_date, true);
        }
        return back()->with('success', 'Wizyta została anulowana');
    }
    function move(Request $request)
    {
        validator($request->route()->parameters(), [
            'id' => 'numeric | required | min:1 | exists:events,id',
        ])->validate();
        $event = Event::find($request->id);
        if (!$event) {
            return back()->with('error', 'Nie znaleziono wizyty');
        }
        if ($event->doctor_id != auth()->user()->id) {
            return back()->with('error', 'Nie masz uprawnień do przeniesienia tej wizyty');
        }
        $appointments = [];
        return view('appointment.move', ['event' => $event, 'id' => $request->id, 'appointments' => $appointments]);
    }
    function confirm(Request $request)
    {
        $request->validate([
            'id' => 'numeric | required | min:1',
        ]);
        $event = Event::find($request->id);
        if (!$event) {
            return back()->with('error', 'Nie znaleziono wizyty');
        }
        if ($event->doctor_id != auth()->user()->id) {
            return back()->with('error', 'Nie masz uprawnień do potwierdzenia tej wizyty');
        }
        $event->confirmed = true;
        $event->save();
        $patient = User::find($event->patient_id);
        EmailController::sendEmailAppointmentConfirmed($patient, auth()->user(), $event->start_date, $event->start_date, $event->description);
        return back()->with('success', 'Wizyta została potwierdzona');
    }
    function showDay(Request $request)
    {
        validator($request->route()->parameters(), [
            'date' => 'date | required',
        ]);
        $date = $request->date;
        $day  = date('d', strtotime($date));
        $month = date('m', strtotime($date));
        $year = date('Y', strtotime($date));
        $events = Event::where('start_date', '>=', $date . ' 00:00:00')
            ->where('end_date', '<=', $date . ' 23:59:59')
            ->get();
        if (Auth::user()->role == 'patient') {
            foreach ($events as $event) {
                $event->person = User::find($event->doctor_id);
                $event->offer_id = Offer::where('doctor_id', $event->doctor_id)->first()->id;
            }
        } else {
            foreach ($events as $event) {
                $event->person = User::find($event->patient_id);
                $event->offer_id = null;
            }
        }
        return view('appointment.day', ['date' => $date, 'events' => $events, 'day' => $day, 'month' => $month, 'year' => $year]);
    }
    function searchAppointmentForm($id)
    {
        if (!is_numeric($id) || $id < 1) {
            return redirect()->route('home')->with('error', 'Nieprawidłowy identyfikator lekarza');
        }
        $appointments = null;
        $offer = Offer::where('doctor_id', $id)->first();
        if (!$offer) {
            return redirect()->route('home')->with('error', 'Nie znaleziono oferty');
        }
        return view('appointment.index', ['id' => $id, 'appointments' => $appointments]);
    }
    function searchAppointment(Request $request)
    {
        $request->validate([
            'id' => 'numeric | required | min:1',
            'dateStart' => 'date | required',
            'dateEnd' => 'date | required',
        ]);
        $diff = strtotime($request->dateEnd) - strtotime($request->dateStart);
        if ($diff > 15 * 24 * 60 * 60) {
            return redirect()->route('appointment.index', ['id' => $request->id])->with('error', 'Różnica między datą początkową a datą końcową nie może być większa niż 15 dni');
        }
        if ($request->dateStart > $request->dateEnd) {
            return redirect()->route('appointment.index', ['id' => $request->id])->with('error', 'Data początkowa nie może być większa niż data końcowa');
        }
        $appointments = null;
        $doctor = User::find($request->id);
        if (!$doctor) {
            return redirect()->route('appointment.index', ['id' => $request->id])->with('error', 'Nie znaleziono lekarza');
        }
        $offer = Offer::where('doctor_id', $request->id)->first();
        if (!$offer) {
            return redirect()->route('appointment.index', ['id' => $request->id])->with('error', 'Nie znaleziono oferty');
        }
        $doctor_working_hours = json_decode($offer->working_hours, true);
        $default_duration = $offer->default_appointment_duration;
        $events = Event::where('doctor_id', $request->id)
            ->where('start_date', '>=', $request->dateStart)
            ->where('end_date', '<=', $request->dateEnd)
            ->get();

        $appointments = [];
        $date = $request->dateStart;
        while ($date <= $request->dateEnd) {
            $day = strtolower(date('l', strtotime($date)));
            $start = $doctor_working_hours[$day]['start'];
            $end = $doctor_working_hours[$day]['end'];
            $ignore = $doctor_working_hours[$day]['ignore'];
            if ($ignore) {
                $date = date('Y-m-d', strtotime($date . ' +1 day'));
                continue;
            }
            $start = strtotime($start);
            $end = strtotime($end);
            $current = $start;
            while (
                $current + $default_duration * 60
                < $end
            ) {
                $is_free = true;
                foreach ($events as $event) {
                    $event_start = strtotime($event->start_date);
                    $event_end = strtotime($event->end_date);
                    if ($current >= $event_start && $current < $event_end) {
                        $is_free = false;
                        break;
                    }
                }
                if ($is_free) {
                    $appointments[] = [
                        'date' => $date,
                        'time' => date('H:i', $current),
                        'duration' => $default_duration
                    ];
                }
                $current += $default_duration * 60;
            }
            $date = date('Y-m-d', strtotime($date . ' +1 day'));
        }
        return view('appointment.index', [
            'id' => $request->id,
            'appointments' => $appointments
        ]);
    }
    function searchMove(Request $request)
    {
        $request->validate([
            'id' => 'numeric | required | min:1',
            'dateStart' => 'date | required',
            'dateEnd' => 'date | required',
            'duration' => 'numeric | required | min:1',
        ]);

        $diff = strtotime($request->dateEnd) - strtotime($request->dateStart);
        if ($diff > 15 * 24 * 60 * 60) { // 15 days
            return back()->with('error', 'Różnica między datą początkową a datą końcową nie może być większa niż 15 dni');
        }
        if ($request->dateStart > $request->dateEnd) {
            return back()->with('error', 'Data początkowa nie może być większa niż data końcowa');
        }
        $appointments = null;
        $event_id = $request->id;
        $event = Event::find($event_id);
        $offer = Offer::where('doctor_id', $event->doctor_id)->first();
        if (!$offer) {
            return back()->with('error', 'Nie znaleziono oferty');
        }
        $doctor = User::find($offer->doctor_id);
        if (!$doctor) {
            return back()->with('error', 'Nie znaleziono lekarza');
        }
        $doctor_working_hours = json_decode($offer->working_hours, true);
        $events = Event::where('doctor_id', $request->id)
            ->where('start_date', '>=', $request->dateStart)
            ->where('end_date', '<=', $request->dateEnd)
            ->where('id', '!=', $event_id)->get();


        $appointments = [];
        $date = $request->dateStart;
        while ($date <= $request->dateEnd) {
            $day = strtolower(date('l', strtotime($date)));
            $start = $doctor_working_hours[$day]['start'];
            $end = $doctor_working_hours[$day]['end'];
            $ignore = $doctor_working_hours[$day]['ignore'];
            if ($ignore) {
                $date = date('Y-m-d', strtotime($date . ' +1 day'));
                continue;
            }
            $start = strtotime($start);
            $end = strtotime($end);
            $current = $start;
            while (
                $current + $request->duration * 60
                < $end
            ) {
                $is_free = true;
                foreach ($events as $event) {
                    $event_start = strtotime($event->start_date);
                    $event_end = strtotime($event->end_date);
                    if ($current >= $event_start && $current < $event_end) {
                        $is_free = false;
                        break;
                    }
                }
                if ($is_free) {
                    $appointments[] = [
                        'date' => $date,
                        'time' => date('H:i', $current),
                        'duration' => $request->duration
                    ];
                }
                $current += $request->duration * 60;
            }
            $date = date('Y-m-d', strtotime($date . ' +1 day'));
        }
        return view('appointment.move', [
            'id' => $request->id,
            'appointments' => $appointments,
            'event_id' => $event_id
        ]);
    }
}
