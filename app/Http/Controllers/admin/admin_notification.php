<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\admin;
use App\Models\User;
use App\Models\company;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Support\Facades\Storage;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class admin_notification extends Controller
{
      /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('admin_auth:admin-api');
    }


public function send_notification(Request $request)
{
    $request->validate([
        'title' => 'required|string',
        'body' => 'required|string',
        'recipients' => 'required|array', // array of user ids
        'type' => 'required|in:user,company',
    ]);

    $tokens = [];

    if ($request->type === 'user') {
        $tokens = User::whereIn('id', $request->recipients)
                    ->whereNotNull('fcm_token')
                    ->pluck('fcm_token')
                    ->toArray();
    } elseif($request->type === 'company') {
        $tokens = company::whereIn('id', $request->recipients)
                    ->whereNotNull('fcm_token')
                    ->pluck('fcm_token')
                    ->toArray();
    }

    $messaging = Firebase::messaging();

   // foreach ($tokens as $token) {
        $message = CloudMessage::withTarget('token', "fIU9pNmFbJuGUy5oBVR8vI:APA91bERlsPu2RzqDpGtnQ-LIZ7DhgPX4NTptIaDdnry_VYDxqm4S_84aqOf3d6A9YharPgfJexDfgvnDXAsdGaOj0S7qiTTXoiQSjwsJNCFetHyAC4J0Vs")
            ->withNotification(Notification::create($request->title, $request->body));

        $messaging->send($message);
   // }

    return response()->json(['message' => 'Notifications sent.']);

//     $messaging = Firebase::messaging();

// $message = CloudMessage::withTarget('token', 'YOUR_FCM_TOKEN')
//     ->withNotification(Notification::create('Title', 'Body'));

// try {
//     $messaging->send($message);
//     echo "تم الإرسال بنجاح!";
// } catch (\Throwable $e) {
//     echo "خطأ: " . $e->getMessage();
// }


}

}
