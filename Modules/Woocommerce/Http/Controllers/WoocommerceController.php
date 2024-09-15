<?php

namespace Modules\Woocommerce\Http\Controllers;

use Modules\Woocommerce\Http\Requests\WoocommerConfigureRequest;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Filesystem\Filesystem;
use File, Cache, ZipArchive;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Setting;

class WoocommerceController extends Controller
{
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function configure()
    {
        $data['menu'] = 'addon-manager';
        
        $wooCommerce = settings('envato');
        $data['pluginName'] = isset($wooCommerce['woocommerce_plugin_name']) ? $wooCommerce['woocommerce_plugin_name'] : '';
        $data['publicationStatus'] = isset($wooCommerce['woocommerce_publication_status']) ? $wooCommerce['woocommerce_publication_status'] : '';

        $pluginInfo = settings('woocommerce_plugin_info') ? settings('woocommerce_plugin_info') : null;
        $data['pluginInfo'] = isset($pluginInfo) ? json_decode($pluginInfo) : null;

        return view('woocommerce::configure', $data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(WoocommerConfigureRequest $request)
    {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $plugin = module_path('Woocommerce') . '/Resources/assets/paymoney-woocommerce-addon.zip';

        $dir = 'public/uploads/woocommerce';
        if (!is_dir($dir)) {
            mkdir($dir, config('Addons.file_permission'), true);
        }

        $file = new Filesystem;
        $file->cleanDirectory($dir);

        // Unzip uploaded update file and remove zip file.
        $zip = new ZipArchive;
        $res = $zip->open($plugin);

        $randomDir = Str::random(10);

        if ($res === true) {
            $res = $zip->extractTo(base_path($dir . '/' . $randomDir));
            $zip->close();
        } else {
            return redirect(config('adminPrefix') . '/module-manager/addons')->with(['AddonStatus'=> 'fail', 'AddonMessage' => __('Compressed file extracting failed.')])->withInput();
        }
  
        $WpStubFile = base_path($dir . '/' . $randomDir . '/src/main.stub');
        $WpMainFile = base_path($dir . '/' . $randomDir . '/paymoney-woocommerce.php');

        $WpMainFileContent = file_get_contents($WpStubFile);

        $searchKey = ['{plugin_name}', '{plugin_uri}', '{plugin_author}', '{plugin_author_uri}', '{plugin_base_url}', '{plugin_description}', '{plugin_brand}'];

        $replaceKey = [
            isset($request->plugin_name) ? $request->plugin_name : 'PayMoney - WooCommerce Addon',
            isset($request->plugin_uri) ? $request->plugin_uri : 'https://paymoney.techvill.org/',
            isset($request->plugin_author) ? $request->plugin_author : 'Techvillage',
            isset($request->plugin_author_uri) ? $request->plugin_author_uri : 'https://paymoney.techvill.org/',
            url('') . '/',
            isset($request->plugin_description) ? $request->plugin_description : 'Accept payments from customers via Paymoney wallets.',
            isset($request->plugin_brand) ? $request->plugin_brand : 'PayMoney'
        ];

        $WpMainFileContent = str_replace($searchKey, $replaceKey, $WpMainFileContent);
        file_put_contents($WpMainFile, $WpMainFileContent);

        // Get real path for our folder
        $rootPath = realpath($dir . '/' . $randomDir);

        // Initialize archive object
        $zipped = new ZipArchive();
        $zipped->open('public/uploads/woocommerce/'. Str::slug($request->plugin_name) .'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                // Add current file to archive
                $zipped->addFile($filePath, $relativePath);
            }
        }
        $zipped->close();

        File::deleteDirectory($dir . '/' . $randomDir);

        Setting::updateOrCreate(
            ['name' => 'woocommerce_plugin_info', 'type' => 'envato'], 
            ['value' => json_encode([
                'plugin_name' => $request->plugin_name,
                'plugin_uri' => $request->plugin_uri,
                'plugin_author' => $request->plugin_author,
                'plugin_author_uri' => $request->plugin_author_uri,
                'plugin_base_url' =>  url('') . '/',
                'plugin_description' => $request->plugin_description,
                'plugin_brand' => $request->plugin_brand,
                ])
            ]
        );
        Setting::updateOrCreate(
            ['name' => 'woocommerce_plugin_name', 'type' => 'envato'], 
            ['value' => Str::slug($request->plugin_name) .'.zip' ]
        );    
        Setting::updateOrCreate(['name' => 'woocommerce_publication_status', 'type' => 'envato'], ['value' => $request->publication_status]);
        
        Cache::forget(config('cache.prefix') . '-settings');

        return redirect(config('adminPrefix') . '/module-manager/addons')->with(['AddonStatus'=> 'success', 'AddonMessage' => __('Plugin successfully configured for merchants.')]);
    }
}
