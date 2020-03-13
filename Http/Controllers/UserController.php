<?php

namespace Modules\LaravelUser\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\Client\Entities\Client;
use Modules\Client\Events\ClientCreatedEvent;
use Modules\Module\Entities\Module;
use Modules\Module\Entities\ModuleUser;
use Modules\LaravelUser\Emails\ForgotPassword;
use Modules\LaravelUser\Emails\WelcomeMail;
use Modules\LaravelUser\Entities\Role;
use Modules\LaravelUser\Entities\UserType;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $filters = request([
            'name',
            'email',
            'enabled',
            'role_ids',
            'client_id',
            'sort_by',
            'num_items',
            'not_in_module_id',
        ]);

        $users = User::filter($filters)
            ->with('type')
            ->with('roles');

        return ["items" => $users->get()];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            "name" => "required|string",
            "email" => "required|email",
            "password" => "required",
        ];

        if (auth()->user()->user_type_id == UserType::BIS_ADMIN) {
            $rules["client_id"] = "required|exists:clients,id";
            $rules["user_type_id"] = "required|numeric|min:2";
        }

        $this->validate(request(), $rules);

        $user = new User();
        $user->name = request('name');
        $user->email = request('email');
        $user->password = bcrypt(request('password'));
        $user->user_type_id = request('user_type_id');
        $user->is_temporary_password = true;

        if (auth()->user()->user_type_id == UserType::BIS_ADMIN) {
            $user->client_id = request('client_id');
        } else {
            $user->client_id = auth()->user()->client_id;
        }

        if (!$user->save()) {
            return response()->json(['error' => "Failed to add the new user. "], 422);
        }

        $roles = request('roles') ?: [];
        if (is_array($roles)) {
            foreach ($roles as $rid => $selected) {
                if (!$selected) {
                    continue;
                }
                $role = Role::find($rid);
                $user->assignRole($role->slug);
            }
        }

        $modules = Module::get();
        Mail::to($user->email)->queue(new WelcomeMail($user, $modules, request('password')));
        return ["item" => $user];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => "Invalid data sent. "], 422);
        }
        $user->roles;

        return ["item" => $user];
    }

    public function currentUser()
    {
        $id = auth()->id();
        $user = \Illuminate\Support\Facades\Auth::user();

        if (null == $user) {
            return ["user" => new User()];
        }

        $permissions = new \Illuminate\Database\Eloquent\Collection();
        if (count($user->roles)) {
            foreach ($user->roles as $role) {
                $permissions = $permissions->merge($role->permissions);
            }
        }

        $user->permissions = $permissions->unique();
        $user->load('client.subscriptions');

        return ["user" => $user, "id" => $id];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
            "name" => "required",
            "email" => "required",
        ];

        if (auth()->user()->user_type_id == UserType::BIS_ADMIN) {
            $rules["user_type_id"] = "required|numeric|min:2";
        }

        $this->validate(request(), $rules);

        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => "Invalid data sent. "], 422);
        }

        $user->name = request('name');
        $user->email = request('email');
        $user->user_type_id = request('user_type_id');

        if (request('password') && "" != trim(request("password")) && null != request("password")) {
            $user->password = bcrypt(request('password'));
        }

        if (!$user->save()) {
            return response()->json(['error' => "Failed to add the new user. "], 422);
        }

        $user->roles()->detach();

        $roles = request('roles') ?: [];
        if (is_array($roles)) {
            foreach ($roles as $rid => $selected) {
                if (!$selected) {
                    continue;
                }
                $role = Role::find($rid);
                $user->assignRole($role->slug);
            }
        }

        return ["item" => $user];
    }

    public function updateCurrentUserProfile(Request $request)
    {
        $rules = [
            "name" => "required",
            "email" => "required",
        ];

        $this->validate(request(), $rules);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => "Invalid data sent. "], 422);
        }

        $user->name = request('name');
        $user->email = request('email');
        $user->temporarily_invited = false;

        if (request('password') && "" != trim(request("password")) && null != request("password")) {
            $user->password = bcrypt(request('password'));
            $user->is_temporary_password = false;
        }

        if (!$user->save()) {
            return response()->json(['error' => "Failed to add the new user. "], 422);
        }

        $permissions = new \Illuminate\Database\Eloquent\Collection();
        if (count($user->roles)) {
            foreach ($user->roles as $role) {
                $permissions = $permissions->merge($role->permissions);
            }
        }

        $user->permissions = $permissions->unique();
        $user->client;

        return ["item" => $user];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($id == auth()->id()) {
            return response()->json(['error' => "You can not delete your own account."], 422);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => "Invalid data sent. "], 422);
        }

        if (auth()->id() == $user->id) {
            return response()->json(['error' => "You're not allowed to delete your own account"], 422);
        }

        /* Delete all related objects */
        $user->roles()->detach();

        /* Delete all releated user modules */
        ModuleUser::where('user_id', $user->id)
            ->delete();

        /* Delete the user object */
        $deleted = $user->delete();

        return ["item" => $user, "success" => $deleted];
    }

    /**
     * Change the the current user password
     *
     * @todo Check if the logged in user is the same as the user changing the password
     *
     */
    public function user_password_change_own()
    {
        $this->validate(request(), [
            "newPassword" => "required",
            "currentPassword" => "required",
        ]);

        $user = User::find(auth()->id());

        /* Check if the current password entered is the actual current password */
        if (!Hash::check(request('currentPassword'), $user->password)) {
            return response()->json(['error' => "Invalid current password entered. "], 422);
        }

        $user->password = bcrypt(request('newPassword'));
        $user->is_temporary_password = false;

        if (!$user->save()) {
            return response()->json(['error' => "Failed to add the new user. "], 422);
        }

        return ["item" => $user, "success" => true];
    }

    /**
     * Enable the user account
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function enable($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => "No such user exists. "], 422);
        }

        $user->enabled = true;
        if (!$user->save()) {
            return response()->json(['error' => "Failed to enable the user. "], 422);
        }

        return ["item" => $user];
    }

    /**
     * Disable the user account
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function disable($id)
    {
        if ($id == auth()->id()) {
            return response()->json(['error' => "You can not disable your own account."], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => "No such user exists. "], 422);
        }

        $user->enabled = false;
        if (!$user->save()) {
            return response()->json(['error' => "Failed to disable the user. "], 422);
        }

        return ["item" => $user];
    }

    public function companySignUp(Request $request)
    {
        $this->validate(request(), [
            "business_name" => "required",
            "business_email" => "required|unique:clients,email",
            "user_name" => "required",
            "user_email" => "required|unique:users,email",
        ]);

        $client = new Client();
        $client->name = request('business_name');
        $client->email = request('business_email');
        $client->phone = "";
        $client->address = "";
        $client->logo = "";
        $client->letterhead_image = "";
        $client->tin = "";

        if (!$client->save()) {
            return response()->json(['error' => 'Failed to save the Client.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /* Lets setup our event */
        event(new ClientCreatedEvent($client));

        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pin = mt_rand(1000000, 9999999)
            . $characters[rand(0, strlen($characters) - 1)];
        $originalPassword = str_shuffle($pin);

        $user = new User();
        $user->name = request('user_name');
        $user->email = request('user_email');
        $user->password = bcrypt($originalPassword);
        $user->is_temporary_password = true;
        $user->user_type_id = UserType::CLIENT_ADMIN;
        $user->client_id = $client->id;

        if (!$user->save()) {
            return response()->json(['error' => 'Failed to save the User.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Mail::to($user->email)->queue(new WelcomeMail($user, [], $originalPassword));

        return ["item" => $user];
    }

    private function randomPassword()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $pin = mt_rand(1000000, 9999999)
            . $characters[rand(0, strlen($characters) - 1)];

        return str_shuffle($pin);
    }

    public function forgotPassword()
    {

        $this->validate(request(), [
            "email" => "required",
        ]);

        $user = User::where('email', request('email'))
            ->first();

        if (!$user) {
            return response()->json(['error' => "Invalid email."], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $originalPassword = $this->randomPassword();
        $user->password = bcrypt($originalPassword);
        $user->is_temporary_password = true;

        if (!$user->save()) {
            return response()->json(['error' => "Failed to update the password. "], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $password = $originalPassword;
        $subject = "Your Password has been reset";

        Mail::to($user->email)->send(new ForgotPassword($user, $subject, $password));

        return ["item" => $user];
    }
}
