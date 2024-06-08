<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Offer;
use Illuminate\Support\Facades\Auth;
use Nette\Utils\Image;
use App\Models\Service;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class OffersController extends Controller
{
    function search()
    {
        return view('offers.search');
    }
    function getNearestOffers(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if ($data == null) {
            return response()->json(['error' => 'Invalid JSON'], 400);
        }
        $validator = Validator::make($data, [
            'latitude' => 'required | numeric',
            'longitude' => 'required | numeric',
            'range' => 'required | integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid JSON'], 400);
        }
        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        $range = $data['range'];
        $offers = Offer::whereRaw("6371 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude)) ) < $range AND active = 1")->get();
        $list = [];
        foreach ($offers as $offer) {
            $doctor_name = User::find($offer->doctor_id)->name;
            $doctor_surname = User::find($offer->doctor_id)->surname;
            $list[] = [
                'id' => $offer->id,
                'doctor_id' => $offer->doctor_id,
                'doctor_name' => $doctor_name,
                'doctor_surname' => $doctor_surname,
                'address' => $offer->address,
                'latitude' => $offer->latitude,
                'longitude' => $offer->longitude,
                'image' => $offer->image,
                'description' => $offer->description,
            ];
        }
        $json = json_encode($list);
        return response($json, 200)
            ->header('Content-Type', 'application/json');
    }
    function show($id)
    {
        $offer = Offer::find($id);
        if ($offer == null) {
            return redirect()->route('home')->with('error', 'Nie znaleziono oferty');
        }
        if (
            $offer->active == false &&
            Auth::check() &&
            $offer->doctor_id != Auth::id() &&
            Auth::user()->role != 'admin'
        ) {
            return redirect()->route('home')->with('error', 'Oferta jest nieaktywna');
        }
        $services = Service::where('offer_id', $id)->get();
        $user = User::find($offer->doctor_id);
        $working_hours =  JSON_decode($offer->working_hours, true);
        return view('offers.index', ['offer' => $offer, 'services' => $services, 'user' => $user, 'working_hours' => $working_hours]);
    }
    function edit()
    {
        $id = Auth::id();
        $offer = Offer::where('doctor_id', $id)->first();
        $services = Service::where('offer_id', $offer->id)->get();
        $working_hours =  JSON_decode($offer->working_hours, true);
        if ($offer == null) {
            return redirect()->route('home')->with('error', 'Nie posiadasz oferty');
        }
        $user = Auth::user();
        return view('offers.edit', ['offer' => $offer, 'user' => $user, 'services' => $services, 'working_hours' => $working_hours]);
    }
    private static function checkPermission()
    {
        if (Auth::user()->role != 'dentist' && Auth::user()->role != 'admin') {
            return redirect()->route('home')->with('error', 'Nie posiadasz uprawnień');
        }
        if (Auth::user()->role == 'dentist') {
            $offer = Offer::where('doctor_id', Auth::id())->first();
            if ($offer == null) {
                return redirect()->route('home')->with('error', 'Nie posiadasz oferty');
            }
            if ($offer->offer_id != request('id')) {
                return redirect()->route('home')->with('error', 'Nie posiadasz uprawnień');
            }
        }
        if (Auth::user()->role == 'admin') {
            $offer = Offer::find(request('id'));
            if ($offer == null) {
                return redirect()->route('home')->with('error', 'Nie znaleziono oferty');
            }
        }
    }
    static function isActive()
    {
        $current = Auth::id();
        $offer = Offer::where('doctor_id', $current)->first();
        if ($offer == null) {
            return -2;
        }
        if ($offer->active == true) {
            return 0;
        } else {
            return -1;
        }
    }
    function updatePhoto(Request $request)
    {
        $request->validate([
            'id' => 'required | integer',
            'photo' => 'required|image|mimes:jpeg,png,jpg | max:2048',
        ]);
        $this->checkPermission();
        $offer = Offer::find($request->id);
        $img = Image::fromFile($request->file('photo'));
        $img->resize(300, 300);
        $img->save(storage_path('app/public/profile/' . time() . '_' . $request->id . '.webp'), 70, Image::WEBP);
        if ($offer->image != null && strpos($offer->image, 'default') === false) {
            unlink(storage_path('app/public/' . $offer->image));
        }
        $imageName = 'profile/' . time() . '_' . $request->id . '.webp';
        $offer->image = $imageName;
        $offer->save();
        return redirect()->route('offers.edit')->with('success', 'Zdjęcie zostało zaktualizowane');
    }

    function updateDescription(Request $request)
    {
        $request->validate([
            'id' => 'required | integer',
            'description' => 'required | string | max:500',
        ]);
        $this->checkPermission();
        $offer = Offer::find($request->id);
        if ($offer == null) {
            return redirect()->route('home')->with('error', 'Nie znaleziono oferty');
        }
        $offer->description = $request->description;
        $offer->save();
        return redirect()->route('offers.edit')->with('success', 'Opis został zaktualizowany');
    }
    function updateAddress(Request $request)
    {
        $request->validate([
            'id' => 'required | integer',
            'address' => 'required | string | max:255',
            'latitude' => 'required | numeric',
            'longitude' => 'required | numeric',
        ]);
        $this->checkPermission();
        $offer = Offer::find($request->id);
        $offer->address = $request->address;
        $offer->latitude = $request->latitude;
        $offer->longitude = $request->longitude;
        $offer->save();
        return redirect()->route('offers.edit')->with('success', 'Adres został zaktualizowany');
    }
    function addService(Request $request)
    {
        $request->validate([
            'id' => 'required | integer',
            'name' => 'required | string | max:255',
        ]);
        $this->checkPermission();
        $service = new Service();
        $service->offer_id = $request->id;
        $service->name = $request->name;
        $service->save();
        return redirect()->route('offers.edit')->with('success', 'Usługa została dodana');
    }
    function updateService(Request $request)
    {
        $request->validate([
            'id' => 'required | integer',
            'service_id' => 'required | integer',
            'name' => 'required | string | max:255',
            'description' => 'required | string | max:500',
            'minprice' => 'required | integer | min:0',
            'maxprice' => 'required | integer | min:0',
        ]);
        $this->checkPermission();
        $service = Service::find($request->service_id);
        if ($service == null) {
            return redirect()->route('home')->with('error', 'Nie znaleziono usługi');
        }
        if ($request->minprice > $request->maxprice) {
            return redirect()->route('offers.edit')->with('error', 'Cena minimalna nie może być większa od ceny maksymalnej');
        }
        $service->name = $request->name;
        $service->description = $request->description;
        $service->minprice = $request->minprice;
        $service->maxprice = $request->maxprice;
        $service->save();
        return redirect()->route('offers.edit')->with('success', 'Usługa została zaktualizowana');
    }
    function deleteService(Request $request)
    {
        $request->validate([
            'id' => 'required | integer',
            'service_id' => 'required | integer',
        ]);
        $this->checkPermission();
        $service = Service::find($request->service_id);
        if ($service == null) {
            return redirect()->route('home')->with('error', 'Nie znaleziono usługi');
        }
        $service->delete();
        return redirect()->route('offers.edit')->with('success', 'Usługa została usunięta');
    }
    function updateWorkingHours(Request $request)
    {
        $request->validate([
            'id' => 'required | integer',
            'mondayStart' => 'regex:/(\d+\:\d+)/',
            'mondayEnd' => 'regex:/(\d+\:\d+)/ | after:mondayStart',
            'tuesdayStart' => 'regex:/(\d+\:\d+)/',
            'tuesdayEnd' => 'regex:/(\d+\:\d+)/ | after:tuesdayStart',
            'wednesdayStart' => 'regex:/(\d+\:\d+)/',
            'wednesdayEnd' => 'regex:/(\d+\:\d+)/ | after:wednesdayStart',
            'thursdayStart' => 'regex:/(\d+\:\d+)/',
            'thursdayEnd' => 'regex:/(\d+\:\d+)/ | after:thursdayStart',
            'thursdayIgnore' => ' boolean',
            'fridayStart' => 'regex:/(\d+\:\d+)/',
            'fridayEnd' => 'regex:/(\d+\:\d+)/ | after:fridayStart',
            'saturdayStart' => 'regex:/(\d+\:\d+)/',
            'saturdayEnd' => 'regex:/(\d+\:\d+)/ | after:saturdayStart',
            'sundayStart' => 'regex:/(\d+\:\d+)/',
            'sundayEnd' => 'regex:/(\d+\:\d+)/ | after:sundayStart',
        ]);
        $this->checkPermission();
        $offer = Offer::find($request->id);
        $working_hours = json_decode($offer->working_hours, true);
        if ($request->mondayStart != null && $request->mondayEnd != null) {
            $working_hours['monday']['start'] = $request->mondayStart;
            $working_hours['monday']['end'] = $request->mondayEnd;
            $working_hours['monday']['ignore'] = false;
        } else {
            $working_hours['monday']['ignore'] = true;
        }
        if ($request->tuesdayStart != null && $request->tuesdayEnd != null) {
            $working_hours['tuesday']['start'] = $request->tuesdayStart;
            $working_hours['tuesday']['end'] = $request->tuesdayEnd;
            $working_hours['tuesday']['ignore'] = false;
        } else {
            $working_hours['tuesday']['ignore'] = true;
        }
        if ($request->wednesdayStart != null && $request->wednesdayEnd != null) {
            $working_hours['wednesday']['start'] = $request->wednesdayStart;
            $working_hours['wednesday']['end'] = $request->wednesdayEnd;
            $working_hours['wednesday']['ignore'] = false;
        } else {
            $working_hours['wednesday']['ignore'] = true;
        }
        if ($request->thursdayStart != null && $request->thursdayEnd != null) {
            $working_hours['thursday']['start'] = $request->thursdayStart;
            $working_hours['thursday']['end'] = $request->thursdayEnd;
            $working_hours['thursday']['ignore'] = false;
        } else {
            $working_hours['thursday']['ignore'] = true;
        }
        if ($request->fridayStart != null && $request->fridayEnd != null) {
            $working_hours['friday']['start'] = $request->fridayStart;
            $working_hours['friday']['end'] = $request->fridayEnd;
            $working_hours['friday']['ignore'] = false;
        } else {
            $working_hours['friday']['ignore'] = true;
        }
        if ($request->saturdayStart != null && $request->saturdayEnd != null) {
            $working_hours['saturday']['start'] = $request->saturdayStart;
            $working_hours['saturday']['end'] = $request->saturdayEnd;
            $working_hours['saturday']['ignore'] = false;
        } else {
            $working_hours['saturday']['ignore'] = true;
        }
        if ($request->sundayStart != null && $request->sundayEnd != null) {
            $working_hours['sunday']['start'] = $request->sundayStart;
            $working_hours['sunday']['end'] = $request->sundayEnd;
            $working_hours['sunday']['ignore'] = false;
        } else {
            $working_hours['sunday']['ignore'] = true;
        }
        $open = false;
        foreach ($working_hours as $day) {
            if ($day['ignore'] == false) {
                $open = true;
            }
        }
        if ($open == false) {
            return redirect()->route('offers.edit')->with('error', 'Przynajmniej jeden dzień musi być otwarty');
        }
        $offer->working_hours = json_encode($working_hours);


        $offer->save();
        return redirect()->route('offers.edit')->with('success', 'Godziny pracy zostały zaktualizowane');
    }

    function activate(Request $request)
    {
        $request->validate([
            'id' => 'required | integer',
            'active' => 'boolean | required',
        ]);
        $this->checkPermission();
        if ($request->active == false) {
            $offer = Offer::find($request->id);
            $offer->active = false;
            $offer->save();
            return redirect()->route('offers.edit')->with('success', 'Oferta została dezaktywowana');
        }
        $offer = Offer::find($request->id);
        if ($offer->address == null || $offer->address == '' || $offer->latitude == 0.0 || $offer->longitude == 0.0 || $offer->image == null || $offer->image == '' || $offer->description == null || $offer->description == '') {
            return redirect()->route('offers.edit')->with('error', 'Oferta musi zawierać adres.');
        }
        if ($offer->image == null || $offer->image == '' || strpos($offer->image, 'default') !== false) {
            return redirect()->route('offers.edit')->with('error', 'Oferta musi zawierać zdjęcie.');
        }
        if ($offer->description == null || $offer->description == '') {
            return redirect()->route('offers.edit')->with('error', 'Oferta musi zawierać opis.');
        }
        $services = Service::where('offer_id', $offer->id)->get();
        if ($services->count() == 0) {
            return redirect()->route('offers.edit')->with('error', 'Oferta musi zawierać przynajmniej jedną usługę');
        }
        $offer->active = true;
        $offer->save();
        return redirect()->route('offers.edit')->with('success', 'Oferta została aktywowana');
    }
    function updateDuration(Request $request)
    {
        $request->validate([
            'id' => 'required | integer',
            'duration' => 'required | integer | min:15 | max:120',
        ]);
        $this->checkPermission();
        $offer = Offer::find($request->id);
        $offer->default_appointment_duration = $request->duration;
        $offer->save();
        return redirect()->route('offers.edit')->with('success', 'Czas trwania wizyty został zaktualizowany');
    }
}
