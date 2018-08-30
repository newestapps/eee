<?php

namespace Newestapps\Eee\Entity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer id
 * @property string uuid
 */
class Nw3eIndex extends Model {
    use SoftDeletes;

    protected $table = 'nw3e_indexes';

}