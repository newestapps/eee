<?php

namespace Newestapps\Eee\Commands;

use Illuminate\Console\Command;
use Newestapps\Eee\Entity\Nw3eIndex;
use Newestapps\Eee\Facades\Nw3E;

class CreateIndexCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nw:3e:make-index {index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new index in Nw3e environment, with a index, you are able to generate SSL Certs.';

    public function __construct() {
        parent::__construct();
    }

    public function handle() {
        Nw3e::createIndex($this->argument('index'));
    }

}