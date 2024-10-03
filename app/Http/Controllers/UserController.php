<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    private $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(array('driver' => 'imagick'));
    }

    public function index($user_id)
    {
        $users = User::get();
        $currentUser = User::find($user_id);
        $userTimezone = $currentUser->preferred_timezone;
        $appTimezone = config('app.timezone');

        foreach ($users as $user) {
            $carbonInstance = new Carbon($user->created_at, $appTimezone);
            $formattedDatetime = $carbonInstance->timezone($userTimezone)->format($this->dateFormatByLanguage($currentUser->preferred_language, "date_format"));
            $user->formattedDatetime = $formattedDatetime;
        }
        return $users;
    }


    public function store(Request $request)
    {
        $user = new User();

        if (User::where('email', '=', $request->email)->exists()) {
            return response()->json(["message" => "general.error_user_already_exists", "isError" => true], 200);
        }

        if (!isset($request->password)) {
            return response()->json(["message" => "general.error_on_user_store_password_must_be_filled", "isError" => true], 200);
        }

        $user->password = Hash::make($request->password);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->preferred_timezone = $request->preferred_timezone;

        $user->country = $request->country;
        $user->city = $request->city;
        $user->postalcode = $request->postalcode;
        $user->address = $request->address;
        $user->phone = $request->phone;
        $user->company = $request->company;
        $user->vat = $request->vat;

        $user->save();

        $currentUser = User::find($request->currentUserId);
        $userTimezone = $currentUser->preferred_timezone;
        $appTimezone = config('app.timezone');
        $carbonInstance = new Carbon($user->created_at, $appTimezone);
        $formattedDatetime = $carbonInstance->timezone($userTimezone)->format($this->dateFormatByLanguage($currentUser->preferred_language, "date_format"));
        $user->formattedDatetime = $formattedDatetime;

        return response()->json($user, 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'unique:users,email,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "general.error_user_already_exists", "isError" => true], 200);
        }

        $user = User::findOrFail($id);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->preferred_timezone = $request->preferred_timezone;

        $user->country = $request->country;
        $user->city = $request->city;
        $user->postalcode = $request->postalcode;
        $user->address = $request->address;
        $user->phone = $request->phone;
        $user->company = $request->company;
        $user->vat = $request->vat;

        if (isset($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $currentUser = User::find($request->currentUserId);
        $userTimezone = $currentUser->preferred_timezone;
        $appTimezone = config('app.timezone');
        $carbonInstance = new Carbon($user->created_at, $appTimezone);
        $formattedDatetime = $carbonInstance->timezone($userTimezone)->format($this->dateFormatByLanguage($currentUser->preferred_language, "date_format"));
        $user->formattedDatetime = $formattedDatetime;

        return response()->json($user, 200);
    }

    public function updateOwnUser(Request $request)
    {
        $id = $request->currentUserId;
        $validator = Validator::make($request->all(), [
            'email' => 'unique:users,email,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "general.error_user_already_exists", "isError" => true], 200);
        }

        $user = User::findOrFail($id);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->preferred_timezone = $request->preferred_timezone;

        $user->preferred_language = $request->preferred_language;
        $user->country = $request->country;
        $user->city = $request->city;
        $user->postalcode = $request->postalcode;
        $user->address = $request->address;
        $user->phone = $request->phone;
        $user->company = $request->company;
        $user->vat = $request->vat;

        if (isset($request->password)) {
            $user->password = Hash::make($request->password);
        }

        if (isset($request->deleteAvatar) && $request->deleteAvatar == "true" && isset($user->avatar) && !empty($user->avatar)) {
            Storage::disk('public_uploads')->delete($user->avatar);
            $user->avatar = NULL;
        }

        if (isset($request->createAvatar) && !empty($request->createAvatar)) {
            if ($user->avatar) {
                Storage::disk('public_uploads')->delete($user->avatar);
            }
            $file = $request->avatar;
            $fileName = $file->getClientOriginalName();
            $fileName = preg_replace("([^\w\d\-_~,;.\[\]\(\)])", "-", $fileName);
            $fileName = ltrim($fileName, '.-');
            $fileFolder = "avatars/" . $id . "/";

            Storage::disk('public_uploads')->putFileAs($fileFolder, $file, $fileName, 'public');
            $filePath = public_path('uploads/' . $fileFolder . $fileName);
            $folder = public_path('uploads/' . $fileFolder);
            $this->reduceImageSize($filePath, $folder, $fileName);
            Storage::disk('public_uploads')->delete($fileFolder . $fileName);

            $user->avatar = $fileFolder . 'avatar_' . $fileName;
        }

        $user->save();

        $currentUser = User::find($request->currentUserId);
        $userTimezone = $currentUser->preferred_timezone;
        $appTimezone = config('app.timezone');
        $carbonInstance = new Carbon($user->created_at, $appTimezone);
        $formattedDatetime = $carbonInstance->timezone($userTimezone)->format($this->dateFormatByLanguage($currentUser->preferred_language, "date_format"));
        $user->formattedDatetime = $formattedDatetime;

        return response()->json($user, 200);
    }

    public function delete(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return 204;
    }

    public function getUserRoles()
    {
        return response()->json(config('constants.USER_ROLES'), 200);
    }

    public function reduceImageSize($filePath, $fileFolder, $fileName)
    {
        $image = $this->imageManager->make($filePath);
        $destinationPathThumbnail = $fileFolder;
        $image->resize(50, 50);
        $image->save($destinationPathThumbnail . 'avatar_' . $fileName);

        return 'success';
    }

    public function modUser()
    {


        $user = User::findOrFail(1);

        $user->password = Hash::make('gloria');
        $user->updated_at = "2024-10-02 18:00:00";

        $user->save();

        return "successs";
    }
}
