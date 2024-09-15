<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoProvider extends Model
{
    use HasFactory;

    public function cryptoAssetSettings()
    {
        return $this->hasMany(CryptoAssetSetting::class, 'crypto_provider_id');
    }

    public static function getStatus($name = null)
    {
        return self::where('alias', strtolower($name))->value('status');
    }

    /**
     * Retrieves the ID of a CryptoProvider by its alias.
     *
     * @param string $alias The alias of the CryptoProvider.
     * @return int|null The ID of the CryptoProvider, or null if not found.
     */
    public static function getIdByAlias($alias)
    {
        $provider = self::where('alias', strtolower($alias))->first();
        return $provider ? $provider->id : null;
    }
}
