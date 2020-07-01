<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserWithTicketRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Auth;
use App\Policies\UserPolicy;
use App\User;
use App\Brand;
use App\Person;
use App\Company;
use App\TestSchedule;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('manage', User::class);
        $users = User::with('company', 'testSchedules', 'roles', 'person')
            ->orderBy('users.username')
            ->paginate(20);
        $testSchedules = TestSchedule::all();
        /* foreach ($users as $user) {
            $user->{'name'} = $user->person->name;
            $user->{'firstname'} = $user->person->firstname;
            $billets = [];
            foreach ($user->testSchedules as $key => $testSchedule) {
                $day = date('d/m/Y', strtotime($testSchedule['startTime']));
                $endTime = date('H:i', strtotime($testSchedule['endTime']));
                $startTime = date('H:i', strtotime($testSchedule['startTime']));
                array_push($billets, array('schedule' => $day . " - " . $startTime . " : " . $endTime, 'id' => $testSchedule->id));
            }
            $user->{'schedules'} = $billets;
        } */

        /* foreach ($testSchedules as $testSchedule) {
            $testSchedule['day'] = date('d/m/Y', strtotime($testSchedule['startTime']));
            $testSchedule['endTime'] = date('H:i', strtotime($testSchedule['endTime']));
            $testSchedule['startTime'] = date('H:i', strtotime($testSchedule['startTime']));
        } */
        //dd($users);
        return view('adminConsultation', compact('users', 'testSchedules'));
    }

    public function edit()
    {
        $id = htmlspecialchars($_GET["user_id"]);
        $user = User::where('id', '=', $id)->with('person', 'company', 'roles')->first();
        $user->roles = $user->roles->pluck('name')->toArray();
        $companies = Company::all();
        $testSchedules = TestSchedule::all();
        //dd($user->roles);
        //dd($companies);
        return view('adminModifyUser', compact('user', 'companies', 'testSchedules'));
    }

    public function show() {
        if(Auth::user()){
            $id = Auth::user()->id;
            $user = User::find($id);
            $person = Person::find($user->id);
            $user->{'firstname'} = $person->firstname;
            $user->{'name'} = $person->name;
            return view('auth/my-profile')->with('user', $user);
        } else {
            return redirect('/login');
        }
        
    }

    public function updateProfile(UpdateUserRequest $request) {
        $user = User::find($request['id']);
        $person = Person::find($request['id']);
        $person->name = $request['name'];
        $person->firstname = $request['firstname'];
        if(strlen($request['password'] >= 8)) {
            $user->password = $request['password'];
        }
        $user->username = $request['username'];
        $person->save();
        $user->save();
        return redirect('/profil');
    }

    public function updateUser(Request $request) {
        $user = User::find($request['id']);
        $person = Person::find($request['id']);
        $person->name = $request['name'];
        $person->firstname = $request['firstname'];
        $user->username = $request['username'];
        $user->email = $request['email'];

        $user->roles()->detach();
        $user->testSchedules()->detach();
        $user->company_id = null;
        
        $user->roles()->attach($request->role);
        if($request->role == "visitor") {
            $user->testSchedules()->attach($request['testSchedule']);
        } else if ($request->role == "exhibitor") {
            $user->company_id = $request->company;
        }
        $person->save();
        $user->save();
        return redirect('/gestion-utilisateurs');
    }

    public function createWithTicket(UserWithTicketRequest $request) {
        $person = Person::create([
            'name' => $request['name'],
            'firstname' => $request['firstname'],
        ]);

        $user = User::create([
            'id' => $person->id,
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => $request['password'],
        ]);

        $user->testSchedules()->attach($request['plage']);
        $user->roles()->attach('visitor');
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
        }
    }

    public function search(Request $request){
        $results = User::where('username', 'like', $request->username.'%')->with('person')->get();
        return (response()->json(['results'=>$results ]));
    }
}
