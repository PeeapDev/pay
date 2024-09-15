<?php

namespace Modules\TatumIo\Http\Controllers\Admin;

use App\Http\Helpers\Common;

use App\Models\{CryptoAssetApiLog,
    CryptoAssetSetting,
    CryptoProvider
};
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

use Modules\TatumIo\Class\{CryptoNetwork,
    TatumIoTransaction
};
use Modules\TatumIo\Datatables\WebhookListDataTable;
use Modules\TatumIo\Http\Requests\TatumAssetStoreRequest;

class TatumIoAssetSettingController extends Controller
{
    protected $network;
    protected $cryptoNetwork;
    protected $xpub;
    protected $mnemonic;
    protected $tatumAddress;
    protected $tatumKey;

    const NETWORK = ['BTC', 'LTC', 'DOGE', 'ETH', 'TRX', 'BTCTEST', 'LTCTEST', 'DOGETEST', 'ETHTEST', 'TRXTEST'];

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $data['menu'] = 'crypto_providers';
        return view('tatumio::admin.network.create', $data);
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(TatumAssetStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $this->validationRequestData($request);
            $this->createTatumAssetSettings($request);
            $currency = $this->storeOrUpdateCurrency($request);
            $this->createAsset($request, $currency);
            DB::commit();

            (new Common())->one_time_message('success', __('Asset added successfully.'));
            return redirect()->route('admin.crypto_providers.list', 'TatumIo');

        } catch (Exception $e) {

            DB::rollBack();
            (new Common())->one_time_message('error', $e->getMessage());
            return redirect()->route('admin.tatumio_asset.create')->withInput();
        }

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('tatumio::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($network)
    {
        $data['menu'] = 'crypto_providers';
        $network = decrypt($network);

        $data['cryptoAssetSetting'] = CryptoAssetSetting::with(['currency' => function($query) {
            $query->where('type', 'crypto_asset');
        }])
        ->where(['network' => $network, 'payment_method_id' => TatumIo])
        ->first();

        if (!empty($data['cryptoAssetSetting']) && !empty($data['cryptoAssetSetting']->currency)) {
            return view('tatumio::admin.network.edit', $data);
        } else {
            (new common)->one_time_message('error', __('Asset settings not found'));
            return redirect()->route('admin.crypto_providers.list');
        }
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $network
     * @return Renderable
     */
    public function update(Request $request, $coin)
    {

        try {
            $this->validationRequestData($request);
            $currency = $this->storeOrUpdateCurrency($request, 'update');
            $this->updateTatumAssetSettings($request, $currency);
            (new Common())->one_time_message('success', __('Crypto Asset Updated successfully.'));
            return redirect()->route('admin.crypto_providers.list', 'TatumIo');

        } catch (Exception $e) {
            (new Common())->one_time_message('error', $e->getMessage());
            return redirect()->route('admin.tatumio_asset.edit', $coin)->withInput();
        }

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    private function validationRequestData(Request $request)
    {
        if (!in_array($request->network, TatumIoAssetSettingController::NETWORK)) {
            throw new Exception(__(':x network is not supported by TatumIo.', ['x' => $request->network]));
        }
    }

    public function storeOrUpdateCurrency($request, $action = 'create')
    {
        if ($action == 'update') {
            $currency = \App\Models\Currency::where(['code' => $request->network, 'type' => 'crypto_asset'])->first();

            if (is_null($currency)) {
                throw new Exception(__('Crypto Network not available'));
            }

        }

        if($action == 'create') {
            $currency =  new \App\Models\Currency();
        }

        $currency->type = 'crypto_asset';
        $currency->name = $request->name;
        $currency->symbol = $request->symbol;
        $currency->code = $request->network;

        if ($request->hasFile('logo')) {
            $networkLogo = $request->file('logo');
            if (isset($networkLogo)) {
                $response = uploadImage($networkLogo, 'public/uploads/currency_logos/', '64*64');
                if ($response['status'] === true) {
                    $currency->logo = $response['file_name'];
                }
            }
        }

        $currency->status  = ($request->status == 'Active') ? 'Active' : 'Inactive';
        $currency->save();

        return $currency;

    }


    public function createTatumAssetSettings($request)
    {
        try {
            $this->cryptoNetwork = new CryptoNetwork($request->api_key, $request->network);

            if (!is_null($this->cryptoNetwork->getTatumAssetSetting()) ) {
                throw new Exception(__(':x Network is already available on TatumIo provider', ['x' => $request->network]));
            }

            if ($this->cryptoNetwork->networkName() !== strtolower($request->name)) {
                throw new Exception(__(':x is not appropriate Coin name for  :y Network', ['x' => $request->name, 'y' => $request->network]));
            }

            $index = 0;
            $cryptoWallet  = $this->cryptoNetwork->generateWallet();

            $this->xpub = $cryptoWallet['xpub'];
            $this->mnemonic = $cryptoWallet['mnemonic'];

            $tatumAddress = $this->cryptoNetwork->generateAddress($this->xpub , $index);
            $this->tatumAddress = $tatumAddress['address'];
            $this->tatumKey = $this->cryptoNetwork->generateAddressPrivateKey($index,  $this->mnemonic);
           

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function createAsset($request, $currency)
    {
        try {
        $tatumBalance = ( $this->cryptoNetwork->networkName()  ==  'tron' ) ? 0 : $this->cryptoNetwork->getBalanceOfAddress($this->tatumAddress);

        $tatumAssetSettings = new CryptoAssetSetting();
        $tatumAssetSettings->payment_method_id = TatumIo;
        $tatumAssetSettings->currency_id = $currency->id;
        $tatumAssetSettings->crypto_provider_id = CryptoProvider::getIdByAlias('TatumIo');
        $tatumAssetSettings->network = $request->network;

        $tatumNetworkArray = [];
        $tatumNetworkArray['api_key'] = $request->api_key;
        $tatumNetworkArray['coin'] =  $request->name;
        $tatumNetworkArray['mnemonic'] = isset($this->mnemonic) ? $this->mnemonic : '';
        $tatumNetworkArray['address'] =  $this->tatumAddress;
        $tatumNetworkArray['xpub'] = isset( $this->xpub) ?  $this->xpub : '';
        $tatumNetworkArray['key'] = isset($this->tatumKey['key']) ? $this->tatumKey['key'] : '';
        $tatumNetworkArray['balance'] = $tatumBalance;
        $tatumAssetSettings->network_credentials = json_encode($tatumNetworkArray);
        $tatumAssetSettings->status = $currency->status;
        $tatumAssetSettings->save();

        if (isset($request->create_address) && $request->create_address == 'on') {
            $this->createUsersNetworkAddress($currency->code, $currency->id);
        }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function updateTatumAssetSettings($request, $currency)
    {

        $this->cryptoNetwork = new CryptoNetwork($request->api_key, $request->network);

        $cryptoAssetSetting = $this->cryptoNetwork->getTatumAssetSetting('All');

        $credentials = json_decode($cryptoAssetSetting->network_credentials);

        $tatumBalance =  $this->cryptoNetwork->getBalanceOfAddress($credentials->address);

        $tatumArray = [];
        $tatumArray['api_key'] = $credentials->api_key;
        $tatumArray['coin'] = $credentials->coin;
        $tatumArray['mnemonic'] = $credentials->mnemonic;
        $tatumArray['address'] = $credentials->address;
        $tatumArray['xpub'] = $credentials->xpub;
        $tatumArray['key'] = $credentials->key;
        $tatumArray['balance'] = $tatumBalance;


        if (is_null($cryptoAssetSetting) ) {
            throw new Exception(__(':x Network is not available', ['x' => $request->network]));
        }

        if ($this->cryptoNetwork->networkName() !== strtolower($request->name)) {
            throw new Exception(__(':x is not appropriate Coin name for  :y Network', ['x' => $request->name, 'y' => $request->network]));
        }
        $cryptoAssetSetting->network_credentials = json_encode($tatumArray);

        $cryptoAssetSetting->status = $currency->status;
        $cryptoAssetSetting->save();


        if (isset($request->create_address) && $request->create_address == 'on') {
            $credentials = json_decode($cryptoAssetSetting->network_credentials);
            $this->xpub = $credentials->xpub;
            $this->mnemonic = $credentials->mnemonic;
            $this->createUsersNetworkAddress($currency->code, $currency->id);
        }

    }

    protected function createUsersNetworkAddress($network, $currencyId)
    {
        try {
            $users = \App\Models\User::with(['wallets' => function ($q) use ($currencyId)
            {
                $q->where(['currency_id' => $currencyId]);
            }])
            ->where(['status' => 'Active'])
            ->get(['id', 'email']);

            if (!empty($users)) {
                foreach ($users as $user) {
                    $getWalletObject = (new Common)->getUserWallet([], ['user_id' => $user->id, 'currency_id' => $currencyId], ['id']);
                    if (empty($getWalletObject) && count($user->wallets) == 0) {
                        $wallet              = new \App\Models\Wallet();
                        $wallet->user_id     = $user->id;
                        $wallet->currency_id = $currencyId;
                        $wallet->is_default  = 'No';
                        $wallet->save();
                        $this->createCryptoWalletLog($wallet->id, $user->id, $network);
                    }
                }
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }


    public function createCryptoWalletLog($walletId, $userId, $network)
    {
        try {
            $getTatumAssetApiLog = (new CryptoAssetApiLog())->getCryptoAssetapiLog(['payment_method_id' => TatumIo, 'object_id' => $walletId, 'object_type' => 'wallet_address', 'network' => $network], ['id']);
            if (empty($getTatumAssetApiLog)) {
                $tatumAddress = $this->cryptoNetwork->generateAddress($this->xpub, $userId);
                $tatumKey = $this->cryptoNetwork->generateAddressPrivateKey($userId, $this->mnemonic);
                $tatumBalance =  $this->cryptoNetwork->getBalanceOfAddress($tatumAddress['address']);
                $this->cryptoNetwork->createSubscription($tatumAddress['address']);

                $tatumNetworkArray = [];

                $tatumNetworkArray['address'] = $tatumAddress['address'];
                $tatumNetworkArray['key'] = isset($tatumKey['key']) ? $tatumKey['key'] : '';
                $tatumNetworkArray['balance'] =  $tatumBalance;
                $tatumNetworkArray['user_id'] =  $userId;
                $tatumNetworkArray['wallet_id'] =  $walletId;
                $tatumNetworkArray['network'] =  $network;

                //create new crypt api log if empty
                $tatumIoAssetApiLog = new CryptoAssetApiLog();
                $tatumIoAssetApiLog->payment_method_id = TatumIo;
                $tatumIoAssetApiLog->object_id = $walletId;
                $tatumIoAssetApiLog->object_type = 'wallet_address';
                $tatumIoAssetApiLog->network = $network;
                $tatumIoAssetApiLog->payload = json_encode($tatumNetworkArray);
                $tatumIoAssetApiLog->save();
            }


        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function assetStatusChange(Request $request)
    {
        $network = decrypt($request->network);

        $cryptoAssetSetting = CryptoAssetSetting::with(['currency' => function($query) {
            $query->where('type', 'crypto_asset');
        }])
        ->where(['network' => $network, 'payment_method_id' => TatumIo])
        ->first();

        try {
            DB::beginTransaction();
            $cryptoAssetSetting->update(['status' => $request->network_status]);
            $cryptoAssetSetting->currency->update(['status' => $request->network_status]);
            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => __(':x has been :y successfully.', ['x' => $network, 'y' => $request->network_status == 'Active' ? __('Activated') : __('Deactivated')]),
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    // Validate crypto address
    public function validateAddress(Request $request)
    {
        $network = $request->network;
        $address = $request->address;
        $tatumIo = new TatumIoTransaction($network);
        $tatumIo->tatumIoAsset();
        return $tatumIo->checkAddress($address);
    }

    /**
     * Display the webhook list for a given network.
     *
     * @param WebhookListDataTable $dataTable The data table instance.
     * @param string $network The encrypted network.
     * @throws Exception If there is an error decrypting the network or displaying the webhook list.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse The rendered webhook list view or a redirect response.
     */
    public function webhookList(WebhookListDataTable $dataTable, $network)
    {
        try {
            $data['menu'] = 'crypto_providers';
            $data['network'] = decrypt($network);
            return $dataTable->render('tatumio::admin.subscription.index', $data);
        } catch (Exception $e) {
            (new common)->one_time_message('error', $e->getMessage());
            return redirect()->route('admin.crypto_providers.list');
        }
    }

    /**
     * Remove a webhook subscription for a given network and subscription ID.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects to the webhook list page with the encrypted network.
     * @throws \Exception If there is an error deleting the subscription.
     */
    public function webhookRemove()
    {
        try {
            $network   = request()->network;
            $subscriptionId  = request()->id;
            $tatumIo = new TatumIoTransaction($network);
            $tatumIo->tatumIoAsset();
            $api_key = $tatumIo->getMerchantApiKey();
            $cryptoNetwork = new CryptoNetwork($api_key, $network);
            $cryptoNetwork->deleteSubscription($subscriptionId);
            (new Common())->one_time_message('success',  __('Subscription has been Deleted'));
            return redirect()->route('admin.tatumio_asset.webhooklist', encrypt($network));    
        } catch (Exception $e) {
            (new Common())->one_time_message('error', $e->getMessage());
            return redirect()->route('admin.tatumio_asset.webhooklist',  encrypt($network) );
        }
        
    }

    /**
     * Create a webhook subscription creation page for a given network.
     *
     * @return \Illuminate\View\View The view for creating a webhook subscription.
     */
    public function webhookCreate()
    {
        $data['menu'] = 'crypto_providers';
        $data['network'] = $network   = request()->network;

        $data['currency'] = \App\Models\Currency::where(['code' => $network, 'type' => 'crypto_asset'])->first([
            'id', 'type', 'status'
        ]);
        $currencyId = $data['currency']->id;

        $data['users'] = \App\Models\User::whereHas('wallets.cryptoAssetApiLogs', function ($q) use ($currencyId, $network) {
            $q->where('wallets.currency_id', $currencyId);
            $q->where([
                'crypto_asset_api_logs.payment_method_id' => TatumIo,
                'crypto_asset_api_logs.network' => $network
            ]);
        })
            ->whereStatus('Active')
            ->get();

        return view('tatumio::admin.subscription.create', $data);
    }

    /**
     * Store a new webhook subscription for a given network and user address.
     *
     * @param Request $request The HTTP request object containing the network and user address.
     * @throws Exception If the feature is not available in the localhost environment.
     * @throws Exception If there is an error creating the subscription.
     * @return \Illuminate\Http\RedirectResponse Redirects to the webhook list page with the encrypted network.
     */
    public function webhookStore(Request $request)
    {
        try {
            $network   = $request->network;
            $address = $request->userAddress;

            if (isLocalhost()) {
                throw new Exception(__('This feature is not available in localhost environment'));
            }

            $tatumIo = new TatumIoTransaction($network);
            $tatumIo->tatumIoAsset();
            $api_key = $tatumIo->getMerchantApiKey();
            $cryptoNetwork = new CryptoNetwork($api_key, $network);
            $response = $cryptoNetwork->createSubscription($address);

            if (isset($response->statusCode) && $response->statusCode == 403 ) {
                throw new Exception($response->message);
            }

            if (isset($response->statusCode) && $response->statusCode == 400) {
                throw new Exception($response->data[0]);
            }

            (new Common())->one_time_message('success',  __('Subscription has been Added'));
            return redirect()->route('admin.tatumio_asset.webhooklist', encrypt($network));
        } catch (Exception $e) {
            (new Common())->one_time_message('error', $e->getMessage());
            return redirect()->route('admin.tatumio_asset.webhooklist',  encrypt($network) );
        }
    }
}
