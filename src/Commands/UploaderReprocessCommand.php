<?php namespace browner12\uploader\Commands;

use browner12\uploader\UploaderInterface;
use Illuminate\Console\Command;

class UploaderReprocessCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'uploader:reprocess {types : Directories to process (comma separated).} {--overwrite : Reprocess existing images.}';

    /**
     * @var string
     */
    protected $description = 'Create optimized and thumbnail images from originals.';

    /**
     * @var \browner12\uploader\UploaderInterface
     */
    private $uploader;

    /**
     * constructor
     *
     * @param \browner12\uploader\UploaderInterface $uploader
     */
    public function __construct(UploaderInterface $uploader)
    {
        //parent
        parent::__construct();

        //assign
        $this->uploader = $uploader;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //change cwd to public path
        chdir(public_path());

        //get types
        $types = explode(',', $this->argument('types'));

        //overwrite
        $overwrite = $this->option('overwrite');

        //initialize results
        $results = [];

        //loop through types
        foreach ($types as $type) {

            //reprocess type
            $count = $this->uploader->reprocess(trim($type), $overwrite);

            //display message
            $results[] = [$type, $count['optimized'], $count['thumbnails']];
        }

        //found results
        if (count($results)) {
            $this->table(['Type', 'Optimized', 'Thumbnails'], $results);
        }

        //no results
        else {
            $this->info('Nothing processed.');
        }
    }
}
