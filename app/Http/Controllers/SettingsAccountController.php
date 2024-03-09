<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\UserConnections;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Stripe;

//use Illuminate\Support\Facades\Log;

class SettingsAccountController extends Controller
{
    protected $connection = 'vpAppTemplate';

    public $timestamps = true;

    public function index()
    {
        return view('settings.index');
    }

    public function show()
    {
        $settings = DB::connection('vpAppTemplate')->table('settings')->pluck('value', 'key')->toArray();

        try {
            $stripe = Stripe::getStripeClient();
        } catch (\Exception $e) {
            \Log::error('createStripeSession: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão com a Stripe'
            ]);
        }

        $getSubscriptionData = getSubscriptionData();
        $customerId = isset($getSubscriptionData['customer_id']) ? $getSubscriptionData['customer_id'] : '';
        $subscriptionId = isset($getSubscriptionData['subscription_id']) ? $getSubscriptionData['subscription_id'] : '';

        $subscriptionItemId = '';

        //$users = getUsers();
        $users = UserConnections::getUsersDataConnectedOnMyAccount();

        $user = auth()->user();

        return view('settings.account', compact(
                'settings',
                'user',
                'users',
                'stripe',
                'customerId',
                'subscriptionId',
                'subscriptionItemId'
            )
        );
    }

    public function updateAccount(Request $request)
    {
        //\Log::info($request->all());

        // Custom error messages
        $messages = [
            'name.required' => 'O nome da Instituição (empresa/organização) é obrigatório.',
            'name.max' => 'O nome da Instituição (empresa/organização) não pode ter mais de 191 caracteres.',
            //'user_name.required' => 'Seu nome é obrigatório.',
            //'user_name.max' => 'Seu nome não pode ter mais de 191 caracteres.',
            'phone.required' => 'O número de telefone é obrigatório.',
            'phone.max' => 'O número de telefone não pode ter mais de 16 caracteres.',
        ];

        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:191',
            //'user_name' => 'required|string|max:191',
            'phone' => 'required|string|max:16',
        ], $messages);

        $this->updateOrInsertSetting('name', $request->name);

        //$this->updateOrInsertSetting('user_name', $request->user_name);

        // Remove non-numeric characters from phone number
        $cleanedPhone = onlyNumber($request->phone);
        $this->updateOrInsertSetting('phone', $cleanedPhone);

        return redirect()->back()->with('success', 'Dados da Conta atualizados com êxito!');
    }

    public function updateUser(Request $request)
    {
        //\Log::info($request->all());

        // Custom error messages
        $messages = [
            'user_name.required' => 'Seu nome é obrigatório.',
            'user_name.max' => 'Seu nome não pode ter mais de 100 caracteres.',
            'user_name.min' => 'Seu nome deve ter no mínimo 3 caracteres.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.max' => 'A senha deve ter no máximo 20 caracteres.',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string|min:3|max:100',
            'password' => 'sometimes|nullable|string|min:8|max:20', // Password is entirely optional
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        $user = auth()->user();

        $userName = $request->user_name;
        $password = $request->password ?? null;

        // Update user name
        $user->name = $userName;

        // Check if password was provided and update it
        if (!is_null($password)) {
            $user->password = Hash::make($password);
        }

        // Save the changes
        $user->save();

        return redirect()->back()->with('success', 'Dados de Usuário atualizados com êxito!');
    }

    /**
     * Update or insert a setting.
     */
    public function updateOrInsertSetting(string $key, $value)
    {
        DB::connection('vpAppTemplate')->table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value]
        );
    }


}
