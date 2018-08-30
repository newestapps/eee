<?php

namespace Newestapps\Eee\Services;

use Illuminate\Http\JsonResponse;
use Newestapps\Eee\Entity\Nw3eIndex;
use Newestapps\Eee\Entity\SSLCredential;
use Newestapps\Eee\Exceptions\IndexDuplicationException;
use Newestapps\Eee\Exceptions\IndexNotFoundException;
use Newestapps\Eee\Facades\Nw3E;

class Nw3EService {

    private $bits = 0;
    private $type = 0;

    /**
     * Nw3EService constructor.
     * @param int $bits
     * @param int $type
     */
    public function __construct($bits, $type) {
        $this->bits = $bits;
        $this->type = $type;
    }

    /**
     * @param $index
     * @return Nw3eIndex
     * @throws IndexNotFoundException
     */
    public function getIndex($index) {
        $i = Nw3eIndex::whereUuid($index)->first();
        if (empty($i))
            throw new IndexNotFoundException();

        return $i;
    }

    /**
     * @param $indexKey
     * @return Nw3eIndex
     * @throws IndexDuplicationException
     */
    public function createIndex($indexKey) {
        $i = Nw3eIndex::withTrashed()->whereUuid($indexKey)->first();
        if (empty($i)) {
            $ind = new Nw3eIndex();
            $ind->uuid = $indexKey;
            $ind->saveOrFail();

            return $ind;
        } else {
            throw new IndexDuplicationException($indexKey);
        }
    }

    /**
     * @return array
     */
    public function generateSSLCert() {
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => $this->bits,
            "private_key_type" => $this->type,
        );

        // Create the private and public key
        $res = openssl_pkey_new($config);
        $privKey = null;

        // Extract the private key from $res to $privKey
        openssl_pkey_export($res, $privKey);

        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        return [
            '_pubkey' => $pubKey,
            '_privkey' => $privKey,
            'config' => $config
        ];
    }

    /**
     * @param Nw3eIndex $index
     * @param bool $useExistingCert if FALSE, force the creating of a new cert
     * @return $this|SSLCredential
     */
    public function fetchSSLCredentials(Nw3eIndex $index, $useExistingCert = true) {
        /** @var SSLCredential $cred */
        if ($useExistingCert) {
            $cred = SSLCredential::where('nw3e_index', $index->id)->orderBy('created_at', 'DESC')->first();
            if (empty($cred)) {
                $cred = $this->newCertForIndex($index);
            }
        } else {
            $cred = $this->newCertForIndex($index);
        }

        return $cred;
    }

    /**
     * @param Nw3eIndex $index
     * @return SSLCredential
     */
    private function newCertForIndex(Nw3eIndex $index) {
        $cert = $this->generateSSLCert();
        $PUBKEY = $cert['_pubkey'];
        $PRIVKEY = $cert['_privkey'];

        $cred = new SSLCredential();
        $cred->nw3e_index = $index->id;
        $cred->_pubkey = $PUBKEY;
        $cred->_privkey = $PRIVKEY;
        $cred->keybits = $this->bits;
        $cred->keytype = $this->type;
        $cred->saveOrFail();

        return $cred;
    }

    /**
     * @param SSLCredential $cert
     * @param $plainTextData
     * @return mixed
     */
    public function encrypt(SSLCredential $cert, $plainTextData) {
        openssl_private_encrypt($plainTextData, $encrypted, $cert->privateKey());
        return $encrypted;
    }

    /**
     * @param SSLCredential $cert
     * @param $encryptedData
     * @return mixed
     */
    public function decrypt(SSLCredential $cert, $encryptedData) {
        openssl_public_decrypt(base64_decode($encryptedData), $decrypted, $cert->publicKey());
        return $decrypted;
    }

    /**
     * @param Nw3eIndex $index
     * @param $response
     * @return JsonResponse|mixed
     */
    public function encryptResponse(Nw3eIndex $index, $response) {
        $cert = $this->fetchSSLCredentials($index, true);

        if ($response instanceof JsonResponse) {
            $o = $this->__jsonResponseEncryption($cert, $response);
        } else {
            $o = $this->encrypt($cert, $response);
        }

        return $o;
    }

    /**
     * @param SSLCredential $cert
     * @param JsonResponse $response
     * @return JsonResponse
     */
    private function __jsonResponseEncryption(SSLCredential $cert, $response) {
        $data = $response->getContent();
        $secureData = [
            'data' => base64_encode($this->encrypt($cert, $data)),
            'encryption_date' => $cert->created_at,
            'crypt' => NW3E_VERSION
        ];

        $response->setData($secureData);
        $response->withHeaders([
            'X-NW3E-VERSION' => NW3E_VERSION,
            'X-NW3E-KEY-DATE' => $cert->created_at->format('c')
        ]);

        $response->setCharset('UTF-8');

        return $response;
    }

}