<?php

namespace Newestapps\Eee\Entity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int keytype
 * @property int keybits
 * @property integer nw3e_index
 * @property string _pubkey
 * @property string _privkey
 * @property mixed created_at
 *
 * @method SSLCredential static prepared();
 */
class SSLCredential extends Model {
    use SoftDeletes;

    protected $table = 'nw3e_ssl_credentials';

    protected $visible = [];
    protected $hidden = [];

    public function setPubkeyAttribute($pubkey) {
        $this->attributes['_pubkey'] = encrypt($pubkey);
    }

    public function setPrivkeyAttribute($privkey) {
        $this->attributes['_privkey'] = encrypt($privkey);
    }

    public function getPubkeyAttribute() {
        return '**PUBLIC KEY NOT PREPARED**';
    }

    public function getPrivkeyAttribute() {
        return '**PRIVATE KEY NOT PREPARED**';
    }

    public function privateKey() {
        return decrypt($this->attributes['_privkey']);
    }

    public function publicKey() {
        return decrypt($this->attributes['_pubkey']);
    }

    public function scopeFromIndex($query, $indexName) {
        $index = $indexName;
        if ($index instanceof Nw3eIndex) {
            $index = $indexName->uuid;
        }

        $query->where('nw3e_index', $index)->orderBy('created_at', 'DESC')
            ->first();

        return $query;
    }

}