<?php

namespace Newestapps\Eee\Facades;

use Illuminate\Support\Facades\Facade;
use Newestapps\Eee\Entity\Nw3eIndex;
use Newestapps\Eee\Entity\SSLCredential;

/**
 * @method static Nw3eIndex getIndex($index)
 * @method static Nw3eIndex createIndex($indexKey)
 * @method static array generateSSLCert()
 * @method static SSLCredential fetchSSLCredentials(Nw3eIndex $index, $useExistingCert = true)
 * @method static mixed encryptResponse(Nw3eIndex $index, $response);
 * @method static mixed encrypt(SSLCredential $cert, $plainTextData)
 * @method static mixed decrypt(SSLCredential $cert, $encryptedData)
 */
class Nw3e extends Facade {

    protected static function getFacadeAccessor() {
        return 'nw-3e';
    }

}