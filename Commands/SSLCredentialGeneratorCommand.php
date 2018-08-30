<?php

namespace Newestapps\Eee\Commands;

use Illuminate\Console\Command;
use Newestapps\Eee\Entity\Nw3eIndex;
use Newestapps\Eee\Facades\Nw3E;

class SSLCredentialGeneratorCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nw:3e:make-cert {index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a SSL Cert in Nw3e storage, you must specify a index for it';

    public function __construct() {
        parent::__construct();
    }

    public function handle() {
        $index = Nw3e::getIndex($this->argument('index'));
        $cert = Nw3e::fetchSSLCredentials($index, false);
        echo $cert->publicKey();
    }

}